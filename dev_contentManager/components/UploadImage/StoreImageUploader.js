import React, { useState, useEffect } from 'react';
import { __ } from '@wordpress/i18n';
import Button from '@ECMComponents/Button';
import { API, plugin_root_url, runUploader } from '@ECMUtils/helper';
import PropTypes from 'prop-types';

import './styles/styles.scss';

const propTypes = {
	name: PropTypes.string,
	value: PropTypes.number,
	saveValueHandler: PropTypes.func,
	imageSize: PropTypes.string,
	supportedFile: PropTypes.string,
};

const StoreImageUploader = (props) => {
	const {
		value = 0,
		name = '',
		saveValueHandler = () => {},
		imageSize = __('700x430 pixels', 'easy-content-manager'),
		supportedFile = __('.jpg, .jpeg, .gif, .png', 'easy-content-manager'),
	} = props;

	const [attachment, setAttachment] = useState({});

	useEffect(() => {
		if (value) {
			API.get(`wp/v2/media/${value}`).then((res) => {
				setAttachment({ url: res.data.source_url });
			});
		} else {
			setAttachment({});
		}
	}, [value]);

	const uploadImageHandler = (e) => {
		e.preventDefault();
		runUploader().then((res) => {
			saveValueHandler(name, res.id);
			setAttachment(res);
		});
	};

	const removeFeaturedImage = () => {
		saveValueHandler(name, 0);
		setAttachment({});
	};

	return (
		<div className={`content-manager-upload-image`}>
			<div className="content-manager-upload-image__image-section">
				{Object.keys(attachment).length === 0 ? (
					<div className="content-manager-upload-image__empty-placeholder">
						<div className="content-manager-upload-image-linear-wrap">
							<img
								src={
									plugin_root_url +
									'assets/images/defaultImg.svg'
								}
								alt="upload"
							/>
						</div>
						<div className="content-manager-upload-image__empty-placeholder__items">
							<span className="content-manager-title">
								{__('Upload Image', 'easy-content-manager')}
							</span>
							<span className="content-manager-sub-title">
								{__(
									'.jpg, .jpeg, .gif, .png',
									'easy-content-manager'
								)}{' '}
								({imageSize})
							</span>
						</div>
					</div>
				) : (
					<div className="content-manager-upload-image__image">
						{Object.keys(attachment).length > 0 && (
							<img
								key={attachment.id}
								src={attachment.url}
								alt=""
							/>
						)}
					</div>
				)}
			</div>
			<div className="content-manager-upload-image__actions">
				<div className="content-manager-flex">
					<Button
						onClick={(e) => uploadImageHandler(e)}
						label={
							Object.keys(attachment).length > 0
								? __('Change', 'easy-content-manager')
								: __('Upload', 'easy-content-manager')
						}
						preset="sky-blue"
						suffix={`${
							Object.keys(attachment).length > 0 ? '' : 'upload'
						}`}
					/>

					{Object.keys(attachment).length > 0 && (
						<Button
							type="button"
							label={__('Remove', 'easy-content-manager')}
							icon={
								<span className="easy-content-manager-icon easy-content-manager-icon--trash"></span>
							}
							suffix="delete"
							onClick={removeFeaturedImage}
							preset="transparent"
							border="gray"
						/>
					)}
				</div>
			</div>

			{supportedFile && <p className="media-format">{supportedFile}</p>}
		</div>
	);
};

StoreImageUploader.propTypes = propTypes;
export default StoreImageUploader;
