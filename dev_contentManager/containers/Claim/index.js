import React, { useEffect, useState } from 'react';
import { __ } from '@wordpress/i18n';
import { useSelector, useDispatch } from 'react-redux';
import { useNavigate } from 'react-router-dom';
import { capitalizeFirstLetter, replaceTableRowItemWhitSpace, route_path, sliceString, useQuery } from '@ECMUtils/helper';
import Search from '@ECMComponents/Search';
import OptionMenu from '@ECMComponents/OptionMenu';
import TopBar from '@ECMComponents/TopBar';
import ECMLabel from '@ECMComponents/Labels/ECMLabel';
import { Flex, Span } from '@chakra-ui/react';
import ListTable from '@ECMComponents/ListTable';
import SnackbarAction from '@ECMComponents/BulkAction/SnackbarAction';
import { deleteClaim, getAllClaims, updateClaimItemPage, updateClaimItemPerPage, updateClaimStatus } from '@ECMRedux/Slices/claimSlice/claimSlice';
import ClaimDetailsModal from './ClaimDetails';
import { getClaimStatus, statusArray } from './helper';
import SubTopBar from '@ECMComponents/SubTopBar';

export default function Claim({ appName }) {
    const { data: allClaims, ...data } = useSelector((state) => state.claims || {});
    const [selectedItems, setSelectedItems] = useState([]);
    const [snackbarOption, setSnackbarOption] = useState({});
    const [resetSelectedItems, setResetSelectedItems] = useState(false);
    const [dataFetchingStatus, setDataFetchingStatus] = useState(false);
    const [openDetailsModal, setOpenDetailsModal] = useState(false);
    const [currentClaim, setCurrentClaim] = useState(null);
    const [confirm, setConfirm] = useState(false);

    const dispatch = useDispatch();
    const navigate = useNavigate();
    const location = useQuery();

    const currentPage = Number(location.get('currentPage')) || 1;
    const perPage = Number(location.get('perPage')) || 10;
    const itemStatus = location.get('status');
    const searchText = location.get('search');
    const [status, setStatus] = useState(itemStatus ? itemStatus : 'all');

    const handleNavigate = (claimStatus, itemPerPage, page, claimSearch) => {
        let url = `${route_path}admin.php?page=${appName}-claims`;

        const queryParams = [];

        if (
            claimStatus &&
            claimStatus !== 'null' &&
            claimStatus !== 'all'
        )
            queryParams.push(`status=${encodeURIComponent(claimStatus)}`);

        if (
            itemPerPage &&
            itemPerPage !== 'null' &&
            itemPerPage !== 'all'
        )
            queryParams.push(`perPage=${encodeURIComponent(itemPerPage)}`);

        if (
            page &&
            page !== 'null' &&
            page !== 'all'
        )
            queryParams.push(`currentPage=${encodeURIComponent(page)}`);

        if (
            claimSearch &&
            claimSearch !== 'null' &&
            claimSearch !== 'all'
        )
            queryParams.push(`search=${encodeURIComponent(claimSearch)}`);

        if (queryParams.length > 0) url += `&${queryParams.join('&')}`;

        navigate(url);
    };

    useEffect(() => {
        if (!allClaims || allClaims.length <= 0) {
            setDataFetchingStatus(true);
            dispatch(getAllClaims({ status })).then(() => {
                setDataFetchingStatus(false);
            });
        }
    }, []);

    useEffect(() => {
        handleNavigate(itemStatus, perPage, currentPage, searchText);
    }, [itemStatus, perPage, currentPage, searchText]);

    const handleTableDataFetch = (page = 1, itemPerPage = 10) => {
        dispatch(getAllClaims({
            status,
            page,
            per_page: itemPerPage,
            search: searchText,
        }));
    };

    const handlePageChange = (page, itemPerPage = 10) => {
        dispatch(
            updateClaimItemPage(page)
        );
        handleTableDataFetch(page, itemPerPage);
        handleNavigate(itemStatus, itemPerPage, page);
    };

    const handleItemsPage = (itemsPerPage, page) => {
        dispatch(
            updateClaimItemPerPage(itemsPerPage)
        );
        handleTableDataFetch(1, itemsPerPage);
        handleNavigate(itemStatus, itemsPerPage);
    };

    // Bulk actions
    const snackbarOptions = [
        {
            value: 'delete',
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
        }
    ];

    const snackbarActionHandler = async (option) => {
        if (option === 'delete') {
            const promises = selectedItems.map((item) => {
                return dispatch(deleteClaim(item.id));
            });

            await Promise.all(promises).then(() => {
                handleReset();
            });
        }
    };

    const handleReset = () => {
        setSelectedItems([]);
        setConfirm(false);
        setSnackbarOption({});
    };

    React.useMemo(() => {
        if (confirm) {
            (async () => await snackbarActionHandler(snackbarOption.value))();
        } else {
            handleReset();
        }
    }, [confirm]);

    const deleteHandler = (row) => {
        if (window.confirm(__('Are you sure you want to permanently delete this item?', 'easy-content-manager'))) {
            dispatch(deleteClaim(row.id));
        }
    };

    const handleStatusChange = (row, status) => {
        if (status === 'approved') {
            dispatch(updateClaimStatus({ id: row.id, status: 'approved' }))
        } else if (status === 'pending') {
            dispatch(updateClaimStatus({ id: row.id, status: 'pending' }))
        } else if (status === 'rejected') {
            dispatch(updateClaimStatus({ id: row.id, status: 'rejected' }))
        }
    };

    const columns = [
        {
            name: __('Claimer', 'easy-content-manager'),
            cell: (row) => {
                return (
                    <div className="ecm-title-wrap">
                        <span className="ecm-title-wrap__title">
                            {sliceString(capitalizeFirstLetter(row?.name))}
                        </span>
                    </div>
                );
            },
        },
        {
            name: __('Phone', 'easy-content-manager'),
            cell: (row) => {
                return (
                    <div className="content-manager-table-title-wrap">
                        {replaceTableRowItemWhitSpace(row?.phone)}
                    </div>
                );
            },
        },
        {
            name: __('Date', 'easy-content-manager'),
            cell: (row) => {
                return (
                    <div className="content-manager-table-title-wrap">
                        <Flex direction="column" gap={1} >
                            <span>{moment(row?.created_at).format('MMM DD, YYYY')}</span>
                            <span>{moment(row?.created_at).format('hh:mm A')}</span>
                        </Flex>
                    </div>
                );
            },
        },
        {
            name: __('Status', 'easy-content-manager'),
            cell: (row) => {
                const { claimStatus, bgColor, textColor } = getClaimStatus(row?.status);
                return (
                    <div className="content-manager-table-title-wrap">
                        <Span
                            color={textColor}
                            padding="8px 16px"
                            borderRadius="full"
                            bg={bgColor}
                            fontWeight={600}
                        >
                            {claimStatus}
                        </Span>
                    </div>
                );
            },
        },
        {
            name: __('Proof', 'easy-content-manager'),
            cell: (row) => {
                return (
                    <div className="content-manager-table-title-wrap" title={row?.proof_content}>
                        {sliceString(row?.proof_content, 50)}
                    </div>
                );
            },
        },
        {
            name: __('Post ID', 'easy-content-manager'),
            cell: (row) => {
                return (
                    <div className="content-manager-table-title-wrap">
                        #{row?.post_id}
                    </div>
                );
            },
        },
    ];

    const subHeaderComponentMemo = React.useMemo(() => {
        const searchHandler = (value) => {
            setDataFetchingStatus(true);
            dispatch(getAllClaims({ status: itemStatus, search: value, page: 1, per_page: perPage })).then(() => {
                handleNavigate(itemStatus, perPage, 1, value);
                setDataFetchingStatus(false);
            });
        };

        const dataFetchHandler = (itemStatus) => {
            setDataFetchingStatus(true);
            dispatch(getAllClaims({ status: itemStatus, page: 1, per_page: perPage })).then(() => {
                handleNavigate(itemStatus, perPage, 1);
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
                        placeholder={__('Search Claims', 'easy-content-manager')}
                        onSearchHandler={searchHandler}
                        defaultValue={searchText ? searchText : ''}
                    />
                </div>
            </>
        );
    }, [status, searchText, perPage]);

    const hoverAction = {
        cell: (row) => {
            const options = [
                {
                    type: 'button',
                    label: __('Details', 'easy-content-manager'),
                    onClick: () => {
                        setCurrentClaim(row);
                        setOpenDetailsModal(true);
                    },
                },
                ...((row.status === 'pending' || row.status === 'rejected') ? [
                    {
                        type: 'button',
                        label: <Span color="#00AD6B">{__('Approve', 'easy-content-manager')}</Span>,
                        onClick: () => handleStatusChange(row, 'approved'),
                    }
                ] : []),
                ...(row.status === 'approved' ? [
                    {
                        type: 'button',
                        label: <Span color="#FDB022">{__('Pending', 'easy-content-manager')}</Span>,
                        onClick: () => handleStatusChange(row, 'pending'),
                    }
                ] : []),
                ...(row.status !== 'rejected' ? [
                    {
                        type: 'button',
                        prefix: 'trash',
                        suffix: 'trash',
                        label: __('Reject', 'easy-content-manager'),
                        onClick: () => handleStatusChange(row, 'rejected'),
                    }
                ] : []),
                {
                    type: 'button',
                    prefix: 'trash',
                    suffix: 'trash',
                    label: __('Delete', 'easy-content-manager'),
                    onClick: () => deleteHandler(row),
                }
            ].filter(Boolean);

            return (
                <OptionMenu
                    alwaysShowOptions={true}
                    options={options}
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
                            color="var(--ecm-primary)"
                            type="subtitle"
                            fontWeight="medium"
                            label={__('Claims', 'easy-content-manager')}
                        />
                    </>
                )}
            />

            <div className="content-manager-page-content">
                <SubTopBar
                    heading={__('Claims', 'easy-content-manager')}
                />
                <ListTable
                    key={currentPage}
                    columns={columns}
                    data={allClaims || []} // Ensure array
                    showSubHeader={true}
                    noDataText={__('No claims found.', 'easy-content-manager')}
                    subHeaderComponent={subHeaderComponentMemo}
                    isRowSelectable={true}
                    showColumnFilter={false}
                    getSelectRowValue={(selectedRow) => {
                        setSelectedItems(selectedRow);
                        setResetSelectedItems(false);
                    }}
                    showPagination={data.totalItems >= 10}
                    onChangePage={handlePageChange}
                    onChangeItemsPerPage={handleItemsPage}
                    totalItems={data?.totalItems}
                    totalRows={allClaims?.length}
                    dataFetchingStatus={dataFetchingStatus}
                    resetSelected={resetSelectedItems}
                    currentPageNumber={data?.currentPage || currentPage}
                    rowsPerPage={data?.rowsPerPage || perPage}
                    suffix="claim-table"
                    hoverAction={hoverAction}
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

                {openDetailsModal && (
                    <ClaimDetailsModal
                        isOpen={openDetailsModal}
                        onClose={() => {
                            setOpenDetailsModal(false);
                            setCurrentClaim(null);
                        }}
                        claim={currentClaim}
                    />
                )}
            </div>
        </>
    );
};
