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

const TaxonomyField = ({
	label,
	name,
	values,
	saveValueHandler,
	missingTerms,
	margin,
	termsData,
	setTermsData,
	type,
}) => {
	const [suggestions, setSuggestions] = useState([]);

	useEffect(() => {
		getAllPostTypeTaxonomies(type, { page: 1, per_page: 100 }).then(
			(res) => {
				setTermsData([...res.data, ...missingTerms]);
			}
		);
	}, []);

	useEffect(() => {
		if (termsData?.length) {
			setSuggestions(getTagSuggestions(termsData));
		}
	}, [termsData]);

	const onChangeSavedValue = async (allTerms) => {
		const existingTerms = termsData.filter((item) =>
			allTerms.includes(item?.name)
		);
		const existingIds = existingTerms.map((item) => item.id);

		const newTerms = allTerms.filter(
			(term) => !existingTerms.some((item) => item.name === term)
		);
		let newTermIds = [];

		if (newTerms.length) {
			const fetchResults = await Promise.all(
				newTerms.map((term) =>
					fetchIncludedFromSearch(type, term).then(
						(res) => res?.id || null
					)
				)
			);

			const missingNow = [];
			fetchResults.forEach((id, index) => {
				if (id) {
					newTermIds.push(id);
				} else {
					missingNow.push(newTerms[index]);
				}
			});

			if (missingNow.length > 0) {
				const createdResults = await Promise.all(
					missingNow.map((term) =>
						createPostTypeTaxonomies(type, { name: term }).then(
							(res) => {
								setTermsData([...termsData, res]);
								return res?.id || res?.term_id;
							}
						)
					)
				);
				newTermIds = [...newTermIds, ...createdResults];
			}
		}

		const updatedTerms = [...existingIds, ...newTermIds];
		const uniqueTerms = [...new Set(updatedTerms)];

		saveValueHandler(name, uniqueTerms);
	};

	const value =
		Array.isArray(termsData) && prepareSavedTags(termsData, values);

	return (
		<Flex
			direction="column"
			gap="8px"
			margin={margin}
			className="ecm-widget ecm-widget--taxonomy"
		>
			<ECMLabel label={label} />
			<Box className="ecm-widget__body">
				<FormTokenField
					key={name}
					className="ecm-form-control"
					value={value}
					suggestions={suggestions}
					onChange={(all) => onChangeSavedValue(all)}
				/>
			</Box>
		</Flex>
	);
};

export default TaxonomyField;
