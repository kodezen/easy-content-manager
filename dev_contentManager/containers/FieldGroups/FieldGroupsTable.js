import React, { useEffect, useState } from 'react';
import {
	sliceString,
	route_path,
	capitalizeFirstLetter,
	replaceTableRowItemWhitSpace,
} from '@ECMUtils/helper';
import ListTable from '@ECMComponents/ListTable';
import { __ } from '@wordpress/i18n';
import { useDispatch, useSelector } from 'react-redux';
import { Button } from '@chakra-ui/react';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import Search from '@ECMComponents/Search';
import TopBar from '@ECMComponents/TopBar';
import { handleUpdateFieldGroupLocalize } from './helper';
import { useFormikContext } from 'formik';
import {
	getAllFieldGroups,
	deleteFieldGroup,
	updateCurrentPage,
	updateItemPerPage,
	updateFieldGroups,
	duplicateFieldGroup,
} from '@ECMRedux/Slices/fieldGroupsSlice/fieldGroupsSlice';
import OptionMenu from '@ECMComponents/OptionMenu';
import SnackbarAction from '@ECMComponents/BulkAction/SnackbarAction';
import {
	createFieldGroupLocalize,
	deleteFieldGroupLocalize,
} from '@ECMRedux/Slices/localizeSlice/localizeSlice';
import { primaryBtn } from '../../../assets/scss/chakra/recipe';
import { handleAddCustomField } from '@ECMComponents/CustomFields/helper';
import ECMLabel from '@ECMComponents/Labels/ECMLabel';
import SubTopBar from '@ECMComponents/SubTopBar';

const statusArray = [
	{
		label: __('All', 'easy-content-manager'),
		value: 'all',
	},
	{
		label: __('Active', 'easy-content-manager'),
		value: 'publish',
	},
	{
		label: __('Inactive', 'easy-content-manager'),
		value: 'draft',
	},
	{
		label: __('Trash', 'easy-content-manager'),
		value: 'trash',
	},
];

function useQuery() {
	const { search } = useLocation();
	return React.useMemo(() => new URLSearchParams(search), [search]);
}

const FieldGroupsTable = ({ setOpen, setCurrentTabName, ID, setLoading }) => {
	const { values, setFieldValue } = useFormikContext();
	const { loading, data, totalItems, page, itemPerPage } = useSelector(
		(state) => state.field_groups
	);
	const [selectedItems, setSelectedItems] = useState([]);
	const [snackbarOption, setSnackbarOption] = useState({});
	const [confirm, setConfirm] = useState(false);
	const [resetSelectedItems, setResetSelectedItems] = useState(false);
	const [dataFetchingStatus, setDataFetchingStatus] = useState(
		data.length === 0 ? true : false
	);

	const dispatch = useDispatch();
	const navigate = useNavigate();
	const location = useQuery();

	const itemStatus = location.get('status');
	const perPage = location.get('perPage') ?? 10;
	const searchText = location.get('search');
	const postType = location.get('post_type');
	const [status, setStatus] = useState(itemStatus ? itemStatus : 'all');

	const handleNavigate = (tableStatus, itemPerPage, text = '') => {
		let url = `${route_path}admin.php?page=easy-content-manager-field-groups`;
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
		if (
			itemPerPage !== '' &&
			itemPerPage !== null &&
			itemPerPage !== 'null'
		) {
			const hasQuestionMark =
				url[url.length - 1] === '?' && url.split(url.length - 1);
			url = `${url}${!hasQuestionMark ? '&' : ''}perPage=${itemPerPage}`;
		}
		if (text !== '' && text !== null && text !== 'null') {
			const hasQuestionMark =
				url[url.length - 1] === '?' && url.split(url.length - 1);
			url = `${url}${!hasQuestionMark ? '&' : ''}search=${text}`;
		}

		navigate(url);
	};

	useEffect(() => {
		if (!data.length || !data.length > 0 || !ID) {
			setDataFetchingStatus(true);
			dispatch(
				getAllFieldGroups({ status, page: 1, per_page: perPage })
			).then(({ payload: data }) => {
				if (data) {
					setDataFetchingStatus(false);
				}
			});
		}
	}, [ID]);

	useEffect(() => {
		handleNavigate(itemStatus, perPage, searchText);
	}, [itemStatus, perPage, searchText]);

	const activeAction =
		status !== 'trash'
			? [
				{
					suffix: 'trash',
					label: __('Trash', 'easy-content-manager'),
					onClick: () =>
						setSnackbarOption({
							value: 'trash',
							message: __(
								'Are you sure, you want to move these item to trash!',
								'easy-content-manager'
							),
						}),
				},
			]
			: [];

	const trashAction =
		status === 'trash'
			? [
				{
					suffix: 'restore',
					label: __('Restore', 'easy-content-manager'),
					onClick: () =>
						setSnackbarOption({
							value: 'restore',
							message: __(
								'Are you sure, you want to restore these items?',
								'easy-content-manager'
							),
						}),
				},
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
			]
			: [];

	const snackbarOptions = [...activeAction, ...trashAction];

	const commonUpdatePayload = (item) => {
		return {
			id: item.id,
			group_name: item.title,
			slug: item.post_slug,
			location: item.location,
			field_groups: item.custom_fields,
		};
	};

	const snackbarActionHandler = (option) => {
		if (status !== 'trash' && option === 'trash') {
			selectedItems.forEach((item) => {
				dispatch(
					updateFieldGroups({
						...commonUpdatePayload(item),
						status: 'trash',
					})
				);
			});
			handleReset();
		} else if (option === 'restore') {
			selectedItems.forEach((item) => {
				dispatch(
					updateFieldGroups({
						...commonUpdatePayload(item),
						status: 'trash',
					})
				);
			});
			handleReset();
		} else {
			selectedItems.forEach((item) => {
				dispatch(deleteFieldGroup(item.id));
				handleReset();
			});
		}
	};

	const handleReset = () => {
		setSelectedItems([]);
		setConfirm(false);
		setResetSelectedItems(true);
	};

	React.useMemo(() => {
		if (confirm) {
			snackbarActionHandler(snackbarOption.value);
		} else {
			handleReset();
		}
	}, [confirm]);

	const handleTableDataFetch = (page = 1, itemPerPage = 10) => {
		setDataFetchingStatus(true);
		dispatch(getAllFieldGroups({ page, per_page: itemPerPage })).then(
			() => {
				setDataFetchingStatus(false);
			}
		);
	};

	const handlePageChange = (page, itemPerPage = 10) => {
		dispatch(updateCurrentPage(page));
		handleTableDataFetch(page, itemPerPage);
	};

	const handleItemsPage = (itemsPerPage = 10, page) => {
		dispatch(updateItemPerPage(itemsPerPage));
		handleTableDataFetch(page, itemsPerPage);
		handleNavigate(itemStatus, itemsPerPage);
	};

	const duplicateHandler = (item) => {
		const duplicateFieldGroupData = {
			...item,
			title: item.title + ' (Copy)',
			tempId: `${new Date().toISOString()}`,
			status: 'draft',
		};
		dispatch(
			duplicateFieldGroup({
				ID: item?.id ? item?.id : item?.ID,
				duplicateFieldGroupData,
			})
		).then(({ payload: data }) => {
			const updatedLocalizeData = handleUpdateFieldGroupLocalize(data);
			dispatch(
				createFieldGroupLocalize({
					...updatedLocalizeData,
					post_slug: data.post_slug,
				})
			);
		});
	};

	const deleteHandler = (item) => {
		dispatch(deleteFieldGroup(item.id)).then(() => {
			dispatch(deleteFieldGroupLocalize(item.post_slug));
		});
	};

	const handleFieldGroupStatus = (row, fieldGroupStatus) => {
		if (fieldGroupStatus === 'trash') {
			dispatch(
				updateFieldGroups({
					...commonUpdatePayload(row),
					status: 'trash',
				})
			);
		} else if (
			fieldGroupStatus === 'restore' ||
			fieldGroupStatus === 'deactivate'
		) {
			dispatch(
				updateFieldGroups({
					...commonUpdatePayload(row),
					status: 'draft',
				})
			);
		} else if (fieldGroupStatus === 'activate') {
			dispatch(
				updateFieldGroups({
					...commonUpdatePayload(row),
					status: 'publish',
				})
			);
		}
	};

	const columns = [
		{
			name: __('Post Title', 'easy-content-manager'),
			isWidth: 'ecm-min-w--240',
			cell: (row) => {
				return (
					<Link
						to={`${route_path}admin.php?page=easy-content-manager-field-groups&action=edit&id=${row?.id}`}
						className="ecm-title-wrap"
					>
						<span
							dangerouslySetInnerHTML={{
								__html: sliceString(
									capitalizeFirstLetter(row?.title)
								),
							}}
						/>
						{row?.status === 'draft' && (
							<span className="ecm-title-wrap__inactive">
								&#x2014;
								<span className="easy-content-manager-icon easy-content-manager-icon--eye-slash"></span>
								<span>
									{__('Inactive', 'easy-content-manager')}
								</span>
							</span>
						)}
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
			name: __('Key', 'easy-content-manager'),
			cell: (row) => {
				return (
					<div className="content-manager-table-title-wrap">
						<span
							dangerouslySetInnerHTML={{
								__html: sliceString(
									replaceTableRowItemWhitSpace(row?.group_key)
								),
							}}
						/>
					</div>
				);
			},
		},
		{
			name: __('Location', 'easy-content-manager'),
			cell: (row) => {
				return (
					<div className="content-manager-table-title-wrap">
						<span
							dangerouslySetInnerHTML={{
								__html: sliceString(
									replaceTableRowItemWhitSpace(row?.post)
								),
							}}
						/>
					</div>
				);
			},
		},
		{
			name: __('Fields', 'easy-content-manager'),
			cell: (row) => {
				return (
					<div className="content-manager-table-title-wrap">
						<span
							dangerouslySetInnerHTML={{
								__html: row?.total_fields,
							}}
						/>
					</div>
				);
			},
		},
	];

	const subHeaderComponentMemo = React.useMemo(() => {
		const searchHandler = (value) => {
			setDataFetchingStatus(true);
			dispatch(
				getAllFieldGroups({
					search: value,
					status,
					page: 1,
					per_page: perPage,
				})
			).then(() => {
				handleNavigate(status, perPage, value);
				setDataFetchingStatus(false);
			});
		};
		const dataFetchHandler = (itemStatus) => {
			setDataFetchingStatus(true);
			dispatch(
				getAllFieldGroups({
					status: itemStatus,
					page: 1,
					per_page: perPage,
				})
			).then(() => {
				handleNavigate(itemStatus, perPage);
				setDataFetchingStatus(false);
			});
		};

		return (
			<>
				<div className="ecm-table__sub-header-left">
					{statusArray.map((item, index) => {
						return (
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
						);
					})}
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
	}, [status, searchText, perPage, statusArray]);

	const hoverAction = {
		cell: (row) => {
			const publishActions =
				row.status !== 'trash'
					? [
						{
							type: 'button',
							label: __('Edit', 'easy-content-manager'),
							onClick: () => {
								const url = `${route_path}admin.php?page=easy-content-manager-field-groups&action=edit&id=${row?.id}`;
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
							label:
								(row.status === 'publish' &&
									__(
										'Deactivate',
										'easy-content-manager'
									)) ||
								(row.status === 'draft' &&
									__('Activate', 'easy-content-manager')),
							onClick: () => {
								handleFieldGroupStatus(
									row,
									(row.status === 'publish' &&
										'deactivate') ||
									(row.status === 'draft' &&
										'activate')
								);
							},
						},
						{
							type: 'button',
							prefix: 'trash',
							label: __('trash', 'easy-content-manager'),
							suffix: 'trash',
							onClick: () => {
								handleFieldGroupStatus(row, 'trash');
							},
						},
					]
					: [];

			const trashActions =
				row.status === 'trash'
					? [
						{
							type: 'button',
							label: __('Restore', 'easy-content-manager'),
							onClick: () =>
								handleFieldGroupStatus(row, 'restore'),
						},
						{
							type: 'button',
							prefix: 'trash',
							label: __(
								'Delete Permanently',
								'easy-content-manager'
							),
							suffix: 'trash',
							onClick: () => {
								deleteHandler(row);
							},
						},
					]
					: [];

			return (
				<OptionMenu
					alwaysShowOptions={true}
					options={[...publishActions, ...trashActions]}
				/>
			);
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
							type="subtitle"
							fontWeight="medium"
							color="var(--ecm-primary)"
							label={__('Field Groups', 'easy-content-manager')}
						/>
					</>
				)}
				rightContent={() => {
					return (
						<>
							<Button
								{...primaryBtn}
								onClick={() => {
									const url = `${route_path}admin.php?page=easy-content-manager-field-groups&action=add`;
									navigate(url);
								}}
							>
								<span className="easy-content-manager-icon easy-content-manager-icon--plus has-ecm-blue-bg" />
								{__('Add Field Group', 'easy-content-manager')}
							</Button>
						</>
					);
				}}
			/>

			<div className="content-manager-page-content">
				<SubTopBar
					heading={__('Field Groups', 'easy-content-manager')}
				/>
				<ListTable
					columns={columns}
					data={data ? data : []}
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
					showPagination={totalItems >= 10}
					onChangePage={handlePageChange}
					onChangeItemsPerPage={handleItemsPage}
					totalItems={totalItems}
					totalRows={data?.length}
					dataFetchingStatus={dataFetchingStatus}
					resetSelected={resetSelectedItems}
					currentPageNumber={page}
					hoverAction={hoverAction}
					rowsPerPage={itemPerPage ? itemPerPage : perPage}
					suffix="field-groups-table"
					Button={
						<Button
							bg="var(--ecm-primary)"
							onClick={() => {
								const url = `${route_path}admin.php?page=easy-content-manager-field-groups&action=add`;
								navigate(url);
								handleAddCustomField(
									values,
									setFieldValue,
									setCurrentTabName,
									setOpen,
									setLoading,
									['field_groups']
								);
							}}
						>
							<span className="easy-content-manager-icon easy-content-manager-icon--plus has-ecm-blue-bg" />
							{__('Add Field Group', 'easy-content-manager')}
						</Button>
					}
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

export default FieldGroupsTable;
