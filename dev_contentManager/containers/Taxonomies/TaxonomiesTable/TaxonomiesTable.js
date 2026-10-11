import React, { useEffect, useState } from 'react';
import {
	sliceString,
	route_path,
	capitalizeFirstLetter,
	replaceTableRowItemWhitSpace,
	exportHandler,
} from '@ECMUtils/helper';
import ListTable from '@ECMComponents/ListTable';
import { __ } from '@wordpress/i18n';
import { useDispatch, useSelector } from 'react-redux';
import OptionMenu from '@ECMComponents/OptionMenu';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import Search from '@ECMComponents/Search';
import TopBar from '@ECMComponents/TopBar';
import { Button, Flex, Span } from '@chakra-ui/react';
import { primaryBtn } from '../../../../assets/scss/chakra/recipe';
import {
	deleteCustomTaxonomy,
	duplicateCustomTaxonomy,
	getAllCustomTaxonomies,
	updateCurrentPage,
	updateItemPerPage,
} from '@ECMRedux/Slices/taxonomySlice/taxonomySlice';
import SnackbarAction from '@ECMComponents/BulkAction/SnackbarAction';
import ECMLabel from '@ECMComponents/Labels/ECMLabel';
import { deleteTaxonomyLocalize } from '@ECMRedux/Slices/localizeSlice/localizeSlice';
import SubTopBar from '@ECMComponents/SubTopBar';

const statusArray = [
	{
		label: __('All', 'easy-content-manager'),
		value: 'any',
	},
	{
		label: __('Publish', 'easy-content-manager'),
		value: 'publish',
	},
	{
		label: __('Draft', 'easy-content-manager'),
		value: 'draft',
	},
];

function useQuery() {
	const { search } = useLocation();

	return React.useMemo(() => new URLSearchParams(search), [search]);
}

const TaxonomiesTable = () => {
	const taxonomies = useSelector((state) => state.taxonomies);
	const [selectedItems, setSelectedItems] = useState([]);
	const [totalItems, setTotalItems] = useState([]);
	const [confirm, setConfirm] = useState(false);
	const [snackbarOption, setSnackbarOption] = useState({});
	const [resetSelectedItems, setResetSelectedItems] = useState(false);
	const [dataFetchingStatus, setDataFetchingStatus] = useState(false);

	const dispatch = useDispatch();
	const navigate = useNavigate();
	const location = useQuery();

	const currentPage = parseInt(location.get('currentPage')) || 1;
	const perPage = parseInt(location.get('perPage')) || 10;
	const itemStatus = location.get('status');
	const searchText = location.get('search');

	const handleNavigate = (
		tableStatus = '',
		itemsPerPage = '',
		page = 1,
		text = ''
	) => {
		let url = `${route_path}admin.php?page=easy-content-manager-taxonomy`;

		if (page !== '' && page !== null && page !== 'null') {
			const hasQuestionMark =
				url[url.length - 1] === '?' && url.split(url.length - 1);
			url = `${url}${!hasQuestionMark ? '&' : ''}currentPage=${page}`;
		}
		if (
			itemsPerPage !== '' &&
			itemsPerPage !== null &&
			itemsPerPage !== 'null'
		) {
			const hasQuestionMark =
				url[url.length - 1] === '?' && url.split(url.length - 1);
			url = `${url}${!hasQuestionMark ? '&' : ''}perPage=${itemsPerPage}`;
		}
		if (
			tableStatus !== '' &&
			tableStatus !== null &&
			tableStatus !== 'null' &&
			tableStatus !== 'all'
		) {
			const hasQuestionMark =
				url[url.length - 1] === '?' && url.split(url.length - 1);
			url = `${url}${!hasQuestionMark ? '&' : ''}status=${tableStatus}`;
		}
		if (text !== '' && text !== null && text !== 'null') {
			const hasQuestionMark =
				url[url.length - 1] === '?' && url.split(url.length - 1);
			url = `${url}${!hasQuestionMark ? '&' : ''}search=${text}`;
		}

		navigate(url);
	};

	const [status, setStatus] = useState(itemStatus ? itemStatus : 'any');

	useEffect(() => {
		handleNavigate(itemStatus, perPage, currentPage, searchText);
	}, [itemStatus, perPage, currentPage, searchText]);

	useEffect(() => {
		if (
			!taxonomies.data ||
			!taxonomies?.data?.length ||
			taxonomies?.data?.length >= 1
		) {
			setDataFetchingStatus(true);
			dispatch(
				getAllCustomTaxonomies({
					status,
					page: currentPage,
					per_page: perPage,
				})
			).finally(() => {
				setDataFetchingStatus(false);
			});
		}
	}, []);

	const handleTableDataFetch = (page = 1, itemPerPage = 10) => {
		setDataFetchingStatus(true);
		dispatch(
			getAllCustomTaxonomies({
				status,
				page,
				per_page: itemPerPage,
			})
		).then(() => {
			setDataFetchingStatus(false);
		});
	};

	const handlePageChange = (page, itemPerPage = 10) => {
		dispatch(updateCurrentPage(page));
		handleTableDataFetch(page, itemPerPage);
		handleNavigate(itemStatus, itemPerPage, page);
	};

	const handleItemsPage = (itemsPerPage, page) => {
		dispatch(updateItemPerPage(itemsPerPage));
		handleTableDataFetch(page, itemsPerPage);
		handleNavigate(itemStatus, itemsPerPage);
	};

	const trashAction = [
		{
			suffix: 'delete',
			label: __('Delete Permanently', 'easy-content-manager'),
			onClick: () =>
				setSnackbarOption({
					value: 'delete',
					message: __(
						'Are you sure, you want to delete these items?',
						'easy-content-manager'
					),
				}),
		},
	];

	const snackbarOptions = [...trashAction];

	const snackbarActionHandler = (option) => {
		selectedItems.forEach((item) => {
			dispatch(deleteCustomTaxonomy(item?.ID)).then(() => {
				dispatch(
					deleteTaxonomyLocalize(item?.post_content?.taxonomy_slug)
				);
			});
		});
		handleReset();
		setDataFetchingStatus(true);
		dispatch(
			getAllCustomTaxonomies({ status, page: 1, per_page: perPage })
		).finally(() => {
			setDataFetchingStatus(false);
		});
	};

	const handleReset = () => {
		setSelectedItems([]);
		setSnackbarOption({});
		setConfirm(false);
	};

	React.useMemo(() => {
		if (confirm) {
			snackbarActionHandler(snackbarOption.value);
		} else {
			handleReset();
		}
	}, [confirm]);

	const duplicateHandler = (item) => {
		const duplicateData = {
			tempId: `${new Date().toISOString()}`,
			post_title: `${item?.post_title} - (copy)`,
			post_content: {
				...item?.post_content,
				taxonomy_slug: `${item?.post_content?.taxonomy_slug}-copy`,
			},
		};

		dispatch(
			duplicateCustomTaxonomy({ taxonomy_id: item?.ID, duplicateData })
		);
	};

	const handleToggleSort = (e, orderby) => {
		const btn = e.currentTarget;
		const currentOrder = btn.getAttribute('data-order') || 'desc';
		const nextOrder = currentOrder === 'desc' ? 'asc' : 'desc';

		btn.setAttribute('data-order', nextOrder);

		dispatch(
			getAllCustomTaxonomies({
				status,
				page: 1,
				per_page: perPage,
				orderby,
				order: nextOrder,
			})
		);
	}

	const columns = [
		{
			name: (
				<Flex alignItems="center" gap={2}>
					<Span>{__('Taxonomies Title', 'easy-content-manager')}</Span>
					<Span
						_before={{
							color: "var(--ecm-text-muted)",
							fontSize: "0.75rem"
						}}
						cursor="pointer"
						className='easy-content-manager-icon easy-content-manager-icon--arrow-up-down'
						onClick={(e) => handleToggleSort(e, 'title')}
					/>
				</Flex>
			),
			isWidth: 'ecm-min-w--240',
			cell: (row) => {
				return (
					<Link
						to={`${route_path}admin.php?page=easy-content-manager-taxonomy&action=edit&id=${row?.ID}`}
						state={row}
						className="ecm-title-wrap"
					>
						<span
							dangerouslySetInnerHTML={{
								__html: sliceString(
									capitalizeFirstLetter(row?.post_title)
								),
							}}
							className="ecm-title-wrap__title"
						/>
					</Link>
				);
			},
		},
		{
			name: __('Description', 'easy-content-manager'),
			cell: (row) => {
				return (
					<div className="content-manager-table-title-wrap">
						<span
							dangerouslySetInnerHTML={{
								__html: sliceString(
									replaceTableRowItemWhitSpace(
										row?.post_content?.description
									)
								),
							}}
						/>
					</div>
				);
			},
		},
		{
			name: __('Post Types', 'easy-content-manager'),
			cell: (row) => {
				const postTypes = row?.post_content?.object_type
					?.map((post) => post?.title)
					.join(', ');
				return (
					<div className="content-manager-table-title-wrap">
						<span>{postTypes}</span>
					</div>
				);
			},
		},
		{
			name: __('Terms', 'easy-content-manager'),
			cell: (row) => {
				const terms = row?.post_content?.capabilities
					? Object.entries(row?.post_content?.capabilities)
						?.map(([_, value]) => value)
						.join(', ')
					: '';
				return (
					<div className="content-manager-table-title-wrap">
						<span>{terms}</span>
					</div>
				);
			},
		},
	];

	const subHeaderComponentMemo = React.useMemo(() => {
		const searchHandler = (value) => {
			setDataFetchingStatus(true);
			dispatch(
				getAllCustomTaxonomies({
					status,
					page: 1,
					per_page: perPage,
					search: value,
				})
			).then(() => {
				handleNavigate(status, perPage, 1, value);
				setDataFetchingStatus(false);
			});
		};
		const dataFetchHandler = (itemStatus) => {
			dispatch(getAllCustomTaxonomies({ status: itemStatus }))
				.then(() => {
					handleNavigate(itemStatus, perPage, 1);
					setDataFetchingStatus(false);
				})
				.catch((error) => {
					console.error('Data fetch failed:', error);
					setDataFetchingStatus(false);
				});
		};

		return (
			<>
				<div className="ecm-table__sub-header-left">
					{statusArray.map((item, index) => (
						<span
							role="presentation"
							className={`tab ${item?.value === status ? 'is-active' : ''
								} `}
							key={index}
							onClick={() => {
								setStatus(item.value);
								dataFetchHandler(item.value);
							}}
						>
							{item.label}
						</span>
					))}
				</div>
				<div className="ecm-table-sub-header-actions-right">
					<Search
						placeholder={__('Search Items', 'easy-content-manager')}
						onSearchHandler={searchHandler}
						defaultValue={searchText ? searchText : ''}
					/>
				</div>
			</>
		);
	}, [searchText, status]);

	const hoverAction = {
		cell: (row) => {
			const actions = [
				{
					type: 'button',
					label: __('Edit', 'easy-content-manager'),
					onClick: () => {
						const url = `${route_path}admin.php?page=easy-content-manager-taxonomy&action=edit&id=${row?.ID}`;
						navigate(url);
					},
				},
				{
					type: 'button',
					label: __('Duplicate', 'easy-content-manager'),
					onClick: () => {
						duplicateHandler(row);
					},
				},
				{
					type: 'button',
					label: __('Export', 'easy-content-manager'),
					onClick: () => {
						exportHandler(row);
					},
				},
				{
					type: 'button',
					prefix: 'trash',
					suffix: 'trash',
					label: __('Delete', 'easy-content-manager'),
					onClick: () => {
						dispatch(deleteCustomTaxonomy(row?.ID)).then(() => {
							dispatch(
								deleteTaxonomyLocalize(
									row?.post_content?.taxonomy_slug
								)
							);
						});
					},
				},
			];

			return <OptionMenu alwaysShowOptions={true} options={actions} />;
		},
	};

	return (
		<>
			<TopBar
				leftContent={() => (
					<>
						<span className="ecm-topbar-logo ecm-primary easy-content-manager-icon easy-content-manager-icon--ecm-primary" />

						<span className="easy-content-manager-icon easy-content-manager-icon--angle-right" />

						<ECMLabel
							as="h2"
							color="var(--ecm-primary)"
							type="subtitle"
							fontWeight="medium"
							label={__('Taxonomies', 'easy-content-manager')}
						/>
					</>
				)}
				rightContent={() => {
					return (
						<Button
							{...primaryBtn}
							onClick={() => {
								const url = `${route_path}admin.php?page=easy-content-manager-taxonomy&action=add`;
								navigate(url);
							}}
						>
							{__('Create Taxonomy', 'easy-content-manager')}
						</Button>
					);
				}}
			/>
			<div className="content-manager-page-content">
			<SubTopBar
				heading={__('Taxonomies', 'easy-content-manager')}
			/>
				<ListTable
					key={`content-manager-taxonomy_${currentPage}`}
					columns={columns}
					data={taxonomies?.data ?? []}
					showSubHeader={true}
					noDataText={__(
						'Please, create items to see the available list here.',
						'easy-content-manager'
					)}
					subHeaderComponent={subHeaderComponentMemo}
					isRowSelectable={true}
					showColumnFilter={false}
					getSelectRowValue={(selectedRow) => {
						setSelectedItems(selectedRow);
						setResetSelectedItems(false);
					}}
					showPagination={taxonomies?.totalItems >= 10}
					onChangePage={handlePageChange}
					onChangeItemsPerPage={handleItemsPage}
					totalItems={taxonomies?.totalItems}
					totalRows={taxonomies?.data?.length}
					dataFetchingStatus={dataFetchingStatus}
					resetSelected={resetSelectedItems}
					hoverAction={hoverAction}
					currentPageNumber={taxonomies?.currentPage || currentPage}
					rowsPerPage={
						taxonomies?.itemPerPage
							? taxonomies?.itemPerPage
							: perPage
					}
					suffix="taxonomies-table"
				/>

				{selectedItems.length > 0 && (
					<SnackbarAction
						actionButtons={snackbarOptions}
						itemsLength={selectedItems?.length}
						confirmHandler={setConfirm}
						isActionSelected={snackbarOption}
						resetHandler={handleReset}
					/>
				)}
			</div>
		</>
	);
};

export default TaxonomiesTable;
