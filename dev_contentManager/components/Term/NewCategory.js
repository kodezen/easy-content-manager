import React, { useCallback, useEffect, useMemo, useState } from 'react';
import { __ } from '@wordpress/i18n';
import { useDispatch } from 'react-redux';
import Select from 'react-select';
import debounce from 'just-debounce-it';
import { showNotification } from '@ECMRedux/Slices/notificationSlice/notificationSlice';
import {
	createPostTypeTaxonomies,
	fetchIncludedFromSearch,
} from '@ECMContainers/Texanomy/helper';
import { prepare_term_data } from '@ECMUtils/helper';
import { Button, Flex, Input } from '@chakra-ui/react';
import {
	outlineBtn,
	primaryBtn,
	primaryClearBtn,
} from '../../../assets/scss/chakra/recipe';
import ECMLabel from '@ECMComponents/Labels/ECMLabel';

export function formatTermOptions(data, depth = 0) {
	return data.reduce((acc, item) => {
		const indent = '—'.repeat(depth);
		acc.push({
			value: item.id,
			label: item.name,
			indentLabel: `${indent} ${item.name}`.trim(),
		});

		if (Array.isArray(item.children) && item.children.length > 0) {
			acc.push(...formatTermOptions(item.children, depth + 1));
		}

		return acc;
	}, []);
}

const NewCategory = ({ data, categoryName, fetchAllCategory }) => {
	const fetchedOptions = useMemo(() => formatTermOptions(data), [data]);

	const [options, setOptions] = useState(fetchedOptions);

	useEffect(() => {
		setOptions(fetchedOptions);
	}, [fetchedOptions]);

	const [isOpen, setIsOpen] = useState(false);
	const [name, setName] = useState('');
	const [parent, setParent] = useState('');
	const [isLoading, setIsLoading] = useState(false);
	const [loading, setLoading] = useState(false);

	const dispatch = useDispatch();

	const onClickHandler = () => {
		if (name.trim() !== '') {
			setIsLoading(true);
			setIsOpen(true);

			const params = {
				name,
			};
			if (parent !== '') {
				params.parent = parent;
			}

			createPostTypeTaxonomies(categoryName, params).then((res) => {
				fetchAllCategory();
				dispatch(
					showNotification({
						message: __('Category Added', 'easy-content-manager'),
						isShow: true,
						type: 'success',
					})
				);
				setIsLoading(false);
			});

			setTimeout(() => {
				setName('');
				setParent('');
				setIsOpen(false);
			}, 500);
		}
	};

	// new category
	const onSearchHandler = async (searchKey = '') => {
		setLoading(true);

		return await fetchIncludedFromSearch(name, searchKey).then((res) => {
			const preparedData = prepare_term_data(res);
			setOptions(formatTermOptions(!searchKey ? preparedData : res.data));
			setLoading(false);
		});
	};

	const debouncedAPICall = useCallback(
		debounce((keyword) => {
			onSearchHandler(keyword);
		}, 1000),
		[onSearchHandler]
	);

	return (
		<>
			{!isOpen ? (
				<Button {...primaryClearBtn} onClick={() => setIsOpen(!isOpen)}>
					<span className="easy-content-manager-icon easy-content-manager-icon--simple-add" />
					{__('Add New Category', 'easy-content-manager')}
				</Button>
			) : (
				<Flex direction="column" gap="16px" margin="12px 0 0 0">
					<Flex direction="column" gap="8px" width="100%">
						<ECMLabel
							label={__(
								'New Category Name',
								'easy-content-manager'
							)}
							type="subtitle"
							fontWeight="medium"
							color="var(--ecm-font-color)"
						/>

						<Input
							width="100%"
							type="text"
							name="name"
							className="content-manager-input"
							value={name}
							onChange={(e) => {
								setName(e.target.value);
							}}
						/>
					</Flex>

					<Flex direction="column" gap="8px" width="100%">
						<ECMLabel
							label={__(
								'Parent Category',
								'easy-content-manager'
							)}
							type="subtitle"
							fontWeight="medium"
							color="var(--ecm-primary)"
						/>

						<Select
							id="newCatParentId"
							name="parent"
							className="content-manager-select ecm-mb-2"
							classNamePrefix="content-manager-select"
							placeholder={__(
								'— Parent Category —',
								'easy-content-manager'
							)}
							options={options}
							getOptionLabel={(e) => e.indentLabel || e.label}
							onInputChange={debouncedAPICall}
							isLoading={loading}
							value={options.find(
								(item) =>
									parseInt(item.value) === parseInt(parent)
							)}
							onChange={(option) => {
								if (option) {
									setParent(option.value);
								} else {
									setParent('');
								}
							}}
							isClearable
						/>
					</Flex>

					<Flex alignItems="center" gap="8px">
						<Button
							onClick={onClickHandler}
							loading={isLoading}
							size="sm"
							{...primaryBtn}
						>
							{__('Create', 'easy-content-manager')}
						</Button>
						<Button
							size="sm"
							onClick={() => setIsOpen(!isOpen)}
							{...outlineBtn}
						>
							{__('Cancel', 'easy-content-manager')}
						</Button>
					</Flex>
				</Flex>
			)}
		</>
	);
};

export default NewCategory;
