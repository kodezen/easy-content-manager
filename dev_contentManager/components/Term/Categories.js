import React, { useEffect, useState } from 'react';
import { Box, Checkbox, Flex } from '@chakra-ui/react';
import Preloader from '@ECMComponents/Loader/Preloader';
import Search from '@ECMComponents/Search';
import {
	fetchIncludedFromSearch,
	getAllPostTypeTaxonomies,
} from '@ECMContainers/Texanomy/helper';
import { current_user_can, prepare_term_data } from '@ECMUtils/helper';
import NewCategory from './NewCategory';
import ECMLabel from '@ECMComponents/Labels/ECMLabel';
import { sprintf } from '@wordpress/i18n';

import './styles.scss';

const TermItem = ({ termItem, value, onChangeCategory }) => {
	const nestedTerms = (termItem.children || []).map((childItem) => {
		return (
			<TermItem
				termItem={childItem}
				key={childItem.id}
				value={value}
				onChangeCategory={onChangeCategory}
				type="child"
			/>
		);
	});

	return (
		<Flex gap={2} direction="column">
			<Checkbox.Root
				onCheckedChange={(e) => {
					onChangeCategory(e.checked, termItem);
				}}
				checked={value?.includes(termItem.id)}
				colorPalette="blue"
				size="sm"
			>
				<Checkbox.HiddenInput />
				<Checkbox.Control />
				<Checkbox.Label color="var(--ecm-font-color)">
					{sprintf('%s', termItem.name)}
				</Checkbox.Label>
			</Checkbox.Root>
			<Box ml={4}>{nestedTerms}</Box>
		</Flex>
	);
};

const Categories = (props) => {
	const { value, saveValueHandler, name, label = '' } = props;
	const [categories, setCategories] = useState([]);
	const [loading, setLoading] = useState(false);

	function fetchAllCategory() {
		getAllPostTypeTaxonomies(name, { page: 1, per_page: 100 }).then(
			(res) => {
				setCategories(res.data);
			}
		);
	}

	useEffect(() => {
		fetchAllCategory();
	}, []);

	const onSearchHandler = async (searchKey = '') => {
		if (!searchKey) {
			fetchAllCategory();
			return;
		}

		setLoading(true);
		fetchIncludedFromSearch(name, searchKey).then((res) => {
			if (res) {
				setCategories([res]);
			}
			setLoading(false);
		});
	};

	const preparedCatData =
		!prepare_term_data(categories)?.length && categories?.length > 0
			? categories
			: prepare_term_data(categories);

	const onChangeCategory = (status, item) => {
		if (status) {
			saveValueHandler(name, [...value, item.id]);
		} else {
			saveValueHandler(
				name,
				value.filter((catID) => Number(catID) !== Number(item.id))
			);
		}
	};

	return (
		<>
			<ECMLabel label={label} margin="0 0 12px 0" />

			<Search
				onSearchHandler={onSearchHandler}
				custom="ecm-width-full content-manager-mt-2"
			/>

			<Box margin="12px 0 0 0">
				{loading ? (
					<Preloader />
				) : (
					<>
						{categories && categories.length > 0 && (
							<div className="ecm-term-list">
								{preparedCatData.length > 0 &&
									preparedCatData.map((item, index) => (
										<TermItem
											termItem={item}
											key={index}
											value={value}
											onChangeCategory={onChangeCategory}
										/>
									))}
							</div>
						)}
					</>
				)}
			</Box>
			{(current_user_can?.manage_categories ||
				current_user_can?.manage_options) && (
				<NewCategory
					data={prepare_term_data(categories)}
					categoryName={name}
					setData={setCategories}
					fetchAllCategory={fetchAllCategory}
				/>
			)}
		</>
	);
};

export default Categories;
