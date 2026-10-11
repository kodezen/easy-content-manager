import React, { useEffect, useState } from 'react';
import {
	API,
	isArray,
	plugin_root_url,
	runUploader,
	sliceString,
} from '@ECMUtils/helper';
import { Button, Flex, Image, Text } from '@chakra-ui/react';
import PropTypes from 'prop-types';
import { __ } from '@wordpress/i18n';
import { clearBtn, primaryBtn } from '../../../assets/scss/chakra/recipe';
import ECMLabel from '@ECMComponents/Labels/ECMLabel';

import './styles/styles.scss';

const propTypes = {
	name: PropTypes.string,
	value: PropTypes.arrayOf(PropTypes.number),
	saveValueHandler: PropTypes.func,
	supportedFile: PropTypes.string,
	type: PropTypes.string,
};

export default function UploadImage(props) {
	const {
		value = [],
		name = '',
		saveValueHandler = () => { },
		type = 'image',
	} = props;
	const [attachment, setAttachment] = useState([]);

	const uploadedAttachment =
		plugin_root_url + 'assets/images/imagePlaceHolder.svg';

	function getCleanValue(val){
		if (typeof val === 'string' && val.trim() !== '') {
			const trimmed = val.trim();

			if (trimmed.startsWith('[') && trimmed.endsWith(']')) {
				try {
					const parsed = JSON.parse(trimmed);
					if (Array.isArray(parsed)) return parsed.filter(Boolean);
				} catch (e) {
					// Not valid JSON, fall through to numeric parsing.
				}
			}

			return [parseInt(Number(val))];
		}
		else if (typeof val === 'number') return [val];
		else if (Array.isArray(val)) return val.filter(Boolean);

		return [];
	}
	const filteredVal = getCleanValue(value);

	useEffect(() => {
		if (!filteredVal || !Array.isArray(filteredVal) || !filteredVal.length) return;

		const handleAttachment = async () => {
			if (Array.isArray(filteredVal) && filteredVal.length > 0) {
				await Promise.all(
					filteredVal.map((id) => API.get(`wp/v2/media/${id}`))
				).then((responses) => {
					const images = responses.map((res) => ({
						id: res.data.id,
						url:
							res.data.media_type === type
								? res.data.source_url
								: uploadedAttachment,
						name: sliceString(res.data.title.rendered, 15),
						size: formatFileSize(
							res.data.media_details.filesize || 0
						),
					}));
					setAttachment(images);
				});
			} else if (!Array.isArray(filteredVal) && Number(filteredVal) > 0) {
				await API.get(`wp/v2/media/${filteredVal}`).then((res) => {
					const images = [
						{
							id: res.data.id,
							url:
								res.data.media_type === type
									? res.data.source_url
									: uploadedAttachment,
							name: sliceString(res.data.title.rendered, 15),
							size: formatFileSize(
								res.data.media_details.filesize || 0
							),
						},
					];
					setAttachment(images);
				});
			} else {
				setAttachment([]);
			}
		};
		handleAttachment();
	}, [filteredVal.join(',')]);

	const uploadImageHandler = (e) => {
		e.preventDefault();
		runUploader({}, false).then((res) => {
			// const allowedExtensions =
			// 	supportedFile === 'any' ? null : supportedFile;

			// if (allowedExtensions) {
			// 	const invalidFiles = res.filter((file) => {
			// 		const fileExtension = file.filename
			// 			.split('.')[1]
			// 			.toLowerCase();
			// 		return !allowedExtensions.includes(fileExtension);
			// 	});

			// 	if (invalidFiles.length > 0) {

			// 		return;
			// 	}
			// }
			const newImage = {
				id: res.id,
				url: res.type === 'image' ? res.url : uploadedAttachment,
				name: res.name,
				size: formatFileSize(res.filesizeInBytes || 0),
			};
			saveValueHandler(name, res.id);
			setAttachment([newImage]);
		});
	};

	const removeImageHandler = (id) => {
		const updatedAttachments = attachment.filter(
			(image) => image.id !== id
		);
		const updatedIds = updatedAttachments.map((image) => image.id);
		saveValueHandler(name, updatedIds);
		setAttachment(updatedAttachments);
	};

	const formatFileSize = (size) => {
		if (size >= 1024 * 1024) {
			return (size / (1024 * 1024)).toFixed(2) + ' MB';
		} else if (size >= 1024) {
			return (size / 1024).toFixed(0) + ' KB';
		}
		return size + ' bytes';
	};

	return (
		<Flex
			justifyContent="space-between"
			alignItems={'flex-end'}
			width={'100%'}
		>
			<Flex alignItems={'flex-end'} gap={6}>
				{attachment.length === 0 ? (
					<div className="content-manager-upload-image">
						<Image
							className="content-manager-upload-image__placeholder"
							src={uploadedAttachment}
							alt="upload"
						/>
					</div>
				) : (
					<>
						{attachment.map((att, index) => (
							<div
								key={index}
								className="content-manager-upload-image-wrap"
								style={{
									backgroundImage: `url(${att.url})`,
									backgroundRepeat: 'no-repeat',
									backgroundSize: 'contain',
									backgroundPosition: 'center center',
								}}
							/>
						))}
					</>
				)}

				<Flex direction="column" gap={4} width="100%">
					{attachment.length > 0 ? (
						<Flex direction={'column'} gap={2} width="100%">
							<ECMLabel
								type="subtitle"
								fontWeight="medium"
								label={__(
									`${attachment[0].name}`,
									'easy-content-manager'
								)}
							/>
							<ECMLabel
								type="subtitle"
								label={__(
									`${attachment[0].size}`,
									'easy-content-manager'
								)}
							/>
						</Flex>
					) : (
						<ECMLabel
							type="subtitle"
							fontWeight="medium"
							label={__(
								'Choose your Image',
								'easy-content-manager'
							)}
						/>
					)}

					<Button
						{...primaryBtn}
						onClick={uploadImageHandler}
						width="fit-content"
					>
						{__('Upload Image', 'easy-content-manager')}
					</Button>
				</Flex>
			</Flex>

			{attachment.length > 0 && (
				<Button
					{...clearBtn}
					fontSize="16px"
					size="xs"
					padding={0}
					width="fit-content"
					onClick={() => removeImageHandler(attachment[0].id)}
				>
					<span className="easy-content-manager-icon easy-content-manager-icon--trash" />
				</Button>
			)}
		</Flex>
	);
}

UploadImage.propTypes = propTypes;
