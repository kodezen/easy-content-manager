import React, { useEffect, useState } from 'react';
import { __ } from '@wordpress/i18n';
import Preloader from '@ECMComponents/Loader/Preloader';
import Categories from '@ECMComponents/Term/Categories';
import { API, rest_url } from '@ECMUtils/helper';
import TaxonomyField from '@ECMComponents/Term/TaxonomyField';
import { fetchIncludedTaxonomy } from '@ECMContainers/Texanomy/helper';
import { ChakraProvider } from '@chakra-ui/react';
import { theme } from '../../assets/scss/chakra/theme';

const ReactTaxonomy = ({ values, setFieldValue, post_type }) => {
	const [CPTTaxonomies, setCPTTaxonomies] = useState([]); //CPTTaxonomies means (Custom Post Type Taxonomies)
	const [missingTags, setMissingTags] = useState([]);
	const [tagsData, setTagsData] = useState([...missingTags]);
	const [missingMap, setMissingMap] = useState({});
	const [customTermsMap, setCustomTermsMap] = useState({});
	const [loading, isLoading] = useState(false);

	const defaultTaxonomies = [
		'academy_courses_category',
		'academy_courses_tag',
		'storeengine_product_category',
		'storeengine_product_tag',
	];

	useEffect(() => {
		(async () => {
			await API.get(`${rest_url}wp/v2/types/${post_type}`)
				.then(({ data }) =>
					data.taxonomies.filter(
						(item) => !defaultTaxonomies.includes(item)
					)
				)
				.then((taxonomies) =>
					Promise.all(
						taxonomies.map(
							async (item) =>
								await API.get(
									`${rest_url}wp/v2/taxonomies/${item}`
								).then((res) => res.data)
						)
					)
				)
				.then((response) => setCPTTaxonomies(response));
		})();
	}, [post_type]);

	//CPTCTCategoryUI means (Custom Post Type Custom Taxonomy Category UI)
	const CPTCTCategoryUI =
		CPTTaxonomies.length > 0
			? CPTTaxonomies.filter((item) => item.hierarchical)
			: [];

	//CPTCTTagUI means (Custom Post Type Custom Taxonomy Tag UI)
	const CPTCTTagUI =
		CPTTaxonomies.length > 0
			? CPTTaxonomies.filter((item) => !item.hierarchical)
			: [];

	useEffect(() => {
		const initMissing = {};
		const initTerms = {};
		CPTCTTagUI.forEach((taxonomy) => {
			initMissing[taxonomy] = [];
			initTerms[taxonomy] = []; // init termsData empty
		});
		setMissingMap(initMissing);
		setCustomTermsMap(initTerms);
	}, [post_type]);

	const tagValues = values?.tags;

	useEffect(() => {
		if (tagValues?.length && tagsData?.length) {
			setMissingTags(
				tagValues.filter(
					(tagItem) =>
						!tagsData.some(
							(item) => parseInt(item?.id) === parseInt(tagItem)
						)
				) || []
			);
		}
	}, [tagsData]);

	useEffect(() => {
		const fetchMissingTags = async (term) => {
			if (missingTags.length) {
				isLoading(true);
				fetchIncludedTaxonomy(term, missingTags).then((res) => {
					//merge missing tags
				});

				isLoading(false);
				setMissingTags([]);
			}
		};

		fetchMissingTags();
	}, [missingTags?.length]);

	const setTermsDataFor = (taxonomy) => (newData) => {
		setCustomTermsMap((prev) => ({
			...prev,
			[taxonomy]: newData,
		}));
	};

	return (
		<ChakraProvider value={theme}>
			{CPTTaxonomies.length > 0 && (
				<>
					<div className="easy-content-manager-custom-filed-render">
						{CPTCTCategoryUI.length > 0 &&
							CPTCTCategoryUI.map((taxonomy) => {
								return loading ? (
									<Preloader />
								) : (
									<Categories
										label={__(
											taxonomy.name,
											'easy-content-manager'
										)}
										name={taxonomy.slug}
										value={values[taxonomy.slug] ?? []}
										saveValueHandler={setFieldValue}
										margin="12px 0"
									/>
								);
							})}

						{CPTCTTagUI.length > 0 &&
							CPTCTTagUI.map((taxonomy) => {
								return loading ? (
									<Preloader />
								) : (
									<TaxonomyField
										key={taxonomy.slug}
										name={taxonomy.slug}
										type={taxonomy.slug}
										label={__(
											taxonomy.name,
											'easy-content-manager'
										)}
										values={values[taxonomy.slug]}
										termsData={
											customTermsMap[taxonomy.slug] ?? []
										}
										setTermsData={setTermsDataFor(
											taxonomy.slug
										)}
										saveValueHandler={setFieldValue}
										margin="16px 0"
										missingTerms={
											missingMap[taxonomy.slug] ?? []
										}
									/>
								);
							})}
					</div>
				</>
			)}
		</ChakraProvider>
	);
};

export default ReactTaxonomy;
