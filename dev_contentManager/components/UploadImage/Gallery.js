import React, { useState, useEffect } from 'react';
import PropTypes from 'prop-types';
import { __ } from '@wordpress/i18n';
import { API, runUploader } from '@ECMUtils/helper';
import { SortableContainer, SortableElement } from 'react-sortable-hoc';
import { arrayMoveImmutable as arrayMove } from 'array-move';
import { Button } from '@chakra-ui/react';

import './styles/gallery.scss';

const propTypes = {
	name: PropTypes.string,
	value: PropTypes.array,
	saveValueHandler: PropTypes.func,
	featureImage: PropTypes.bool, // Added featureImage prop
};

const SortableItem = SortableElement(
	({ attachment, removeImage, isFeature }) => (
		<div
			onClick={(e) => {
				e.stopPropagation();
			}}
			className={`content-manager-gallery__image ${isFeature
					? 'content-manager-gallery__feature-image'
					: 'content-manager-gallery__all-image'
				}`}
		>
			<img src={attachment.url} alt="" />
			<Button
				onClick={(e) => {
					e.stopPropagation();
					removeImage(attachment.id);
				}}
				suffix="delete"
				size="xs"
				zIndex={9999}
				bg="red.400 !important"
				color="red.800 !important"
			>
				<span className="easy-content-manager-icon easy-content-manager-icon--close" />
			</Button>
			<span className="easy-content-manager-icon easy-content-manager-icon--drag" />
		</div>
	)
);

const SortableList = SortableContainer(
	({ items, removeImage, uploadImageHandler, featureImage }) => (
		<div className="content-manager-gallery__list">
			{items.map((attachment, index) => (
				<SortableItem
					key={`item-${index}`}
					index={index}
					attachment={attachment}
					removeImage={removeImage}
					isFeature={featureImage && index === 0}
				/>
			))}
		</div>
	)
);

const Gallery = (props) => {
	const {
		value = [],
		name = '',
		saveValueHandler = () => { },
		featureImage = false,
	} = props;
	const [attachments, setAttachments] = useState([]);

	useEffect(() => {
		if (!Array.isArray(value) || !value.length) return;

		const fetchAttachments = async () => {
			const fetchedAttachments = await Promise.all(
				value.map((id) => API.get(`wp/v2/media/${id}`))
			);
			setAttachments(
				fetchedAttachments.map((res) => ({
					id: res.data.id,
					url: res.data.source_url,
				}))
			);
		};

		fetchAttachments();
	}, [value]);

	const updateGallery = (galleryItems) => {
		const attachmentIds = galleryItems.map((image) => image.id);
		let featured_media = 0;

		if (attachmentIds.length) {
			// featured_media = attachmentIds.shift();
			featured_media = attachmentIds;
		}
		// saveValueHandler('featured_media', featured_media);
		saveValueHandler(name, attachmentIds);
	};

	const uploadImageHandler = (e) => {
		e.preventDefault();
		runUploader({}, true).then((res) => {
			let newImages;

			if (attachments.length > 0) {
				newImages = res
					.filter(
						(image) =>
							!attachments.some(
								(attachment) =>
									parseInt(attachment?.id) ===
									parseInt(image?.id)
							)
					)
					.map((image) => ({
						id: image.id,
						url: image.url,
					}));
			} else {
				newImages = res.map((image) => ({
					id: image.id,
					url: image.url,
				}));
			}

			const updatedAttachments = [...attachments, ...newImages];
			setAttachments(updatedAttachments);
			updateGallery([...updatedAttachments]);
		});
	};

	const removeImage = (id) => {
		const updatedAttachments = attachments.filter(
			(image) => image.id !== id
		);
		setAttachments(updatedAttachments);
		updateGallery([...updatedAttachments]);
	};

	const onSortEnd = ({ oldIndex, newIndex }) => {
		const sortedAttachments = arrayMove(attachments, oldIndex, newIndex);
		setAttachments(sortedAttachments);
		updateGallery([...sortedAttachments]);
	};

	return (
		<div className="content-manager-gallery">
			<SortableList
				items={attachments}
				onSortEnd={onSortEnd}
				removeImage={removeImage}
				uploadImageHandler={uploadImageHandler}
				axis="xy"
				useDragHandle={false}
				featureImage={featureImage}
			/>
			<div className="content-manager-gallery__action">
				<Button bg="var(--ecm-primary)" onClick={uploadImageHandler}>
					{__('Upload', 'easy-content-manager')}
				</Button>
			</div>
		</div>
	);
};

Gallery.propTypes = propTypes;
export default Gallery;
