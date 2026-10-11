import React, { useEffect, useState } from 'react';
import { Box, Flex } from '@chakra-ui/react';
import ECMLabel from '@ECMComponents/Labels/ECMLabel';
import {
	createPostTypeTaxonomies,
	fetchIncludedFromSearch,
	getAllPostTypeTaxonomies,
} from '@ECMContainers/Texanomy/helper';
import { getTagSuggestions, prepareSavedTags } from '@ECMUtils/helper';
import { FormTokenField } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import './styles.scss';

const Tags = ({
	label = __('Tags', 'easy-content-manager'),
	name,
	values,
	saveValueHandler,
	missingTags,
	margin,
	tagsData,
	setTagsData,
}) => {
	const [suggestions, setSuggestions] = useState([]);

	useEffect(() => {
		getAllPostTypeTaxonomies('tags', { page: 1, per_page: 100 }).then(
			(res) => {
				setTagsData([...res.data, ...missingTags]);
			}
		);
	}, []);

	useEffect(() => {
		if (tagsData?.length) {
			setSuggestions(getTagSuggestions(tagsData));
		}
	}, [tagsData]);

	const onChangeSavedValue = async (allTags) => {
		const existingTags = tagsData.filter((item) =>
			allTags.includes(item?.name)
		);
		const existingIds = existingTags.map((item) => item.id);

		const newTags = allTags.filter(
			(tag) => !existingTags.some((item) => item.name === tag)
		);
		let newTagIds = [];

		if (newTags.length) {
			const fetchResults = await Promise.all(
				newTags.map((tag) =>
					fetchIncludedFromSearch('tags', tag).then(
						(res) => res?.id || null
					)
				)
			);

			const missingTags = [];
			fetchResults.forEach((id, index) => {
				if (id) {
					newTagIds.push(id);
				} else {
					missingTags.push(newTags[index]);
				}
			});

			if (missingTags.length > 0) {
				const createdResults = await Promise.all(
					missingTags.map((tag) =>
						createPostTypeTaxonomies('tags', { name: tag }).then(
							(res) => {
								setTagsData((prev) => [...prev, res]);
								return res?.id || res?.term_id;
							}
						)
					)
				);
				newTagIds = [...newTagIds, ...createdResults];
			}
		}

		const updatedTags = [...existingIds, ...newTagIds];

		const uniqueTags = [...new Set(updatedTags)];

		saveValueHandler(name, uniqueTags);
	};

	return (
		<Flex
			direction={'column'}
			gap={'8px'}
			margin={margin}
			className="ecm-widget ecm-widget--tags"
		>
			<ECMLabel label={label} />
			<Box className="ecm-widget__body">
				<FormTokenField
					className="ecm-form-control"
					value={prepareSavedTags(tagsData, values)}
					suggestions={suggestions}
					onChange={(allTags) => {
						onChangeSavedValue(allTags);
					}}
				/>
			</Box>
		</Flex>
	);
};

export default Tags;
