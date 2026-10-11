import React, { useEffect, useState } from 'react';
import {
  sliceString,
  route_path,
  capitalizeFirstLetter,
  replaceTableRowItemWhitSpace,
  exportHandler,
  useQuery,
  makeRequest,
} from '@ECMUtils/helper';
import ListTable from '@ECMComponents/ListTable';
import { __, sprintf } from '@wordpress/i18n';
import { useDispatch, useSelector } from 'react-redux';
import OptionMenu from '@ECMComponents/OptionMenu';
import { Link, useNavigate } from 'react-router-dom';
import Search from '@ECMComponents/Search';
import TopBar from '@ECMComponents/TopBar';
import {
  deleteCustomPostType,
  duplicateCustomPostType,
  fetchCollections,
  updateCustomPostTypeStatus,
} from '@ECMRedux/Slices/collectionDataSlice/collectionDataSlice';
import {
  deleteMenu,
  fetchUpdatedMenu,
} from '@ECMRedux/Slices/menuSlice/menuSlice';
import { Button, Text } from '@chakra-ui/react';
import SnackbarAction from '@ECMComponents/BulkAction/SnackbarAction';
import {
  createPostTypeLocalize,
  deletePostTypeLocalize,
} from '@ECMRedux/Slices/localizeSlice/localizeSlice';
import { primaryBtn } from '../../../assets/scss/chakra/recipe';
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

export default function CustomPostTypes({ appName }) {
  const { loading, ...data } = useSelector((state) => state.post_types);
  const [selectedItems, setSelectedItems] = useState([]);
  const [snackbarOption, setSnackbarOption] = useState({});
  const [resetSelectedItems, setResetSelectedItems] = useState(false);
  const [dataFetchingStatus, setDataFetchingStatus] = useState(false);
  const [confirm, setConfirm] = useState(false);

  const dispatch = useDispatch();
  const navigate = useNavigate();
  const location = useQuery();

  const currentPage = parseInt(location.get('currentPage')) || 1;
  const perPage = parseInt(location.get('perPage')) || 10;
  const itemStatus = location.get('status');
  const searchText = location.get('search');
  const [status, setStatus] = useState(itemStatus ? itemStatus : 'all');

  const handleNavigate = (postTypeStatus, postTypeSearch) => {
    let url = `${route_path}admin.php?page=${appName}-post-types`;

    const queryParams = [];

    if (
      postTypeStatus &&
      postTypeStatus !== 'null' &&
      postTypeStatus !== 'all'
    )
      queryParams.push(`status=${encodeURIComponent(postTypeStatus)}`);

    if (
      postTypeSearch &&
      postTypeSearch !== 'null' &&
      postTypeSearch !== 'all'
    )
      queryParams.push(`search=${encodeURIComponent(postTypeSearch)}`);

    if (queryParams.length > 0) url += `&${queryParams.join('&')}`;

    navigate(url);
  };

  useEffect(() => {
    if (!data?.totalPosts || data?.totalPosts.length <= 1) {
      setDataFetchingStatus(true);
      dispatch(fetchCollections({ status: itemStatus })).then(() => {
        setDataFetchingStatus(false);
      });
    }
  }, []);

  useEffect(() => {
    if (itemStatus) handleNavigate(itemStatus, searchText);
  }, [itemStatus, searchText]);

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

  const snackbarActionHandler = async (option) => {
    if (status !== 'trash' && option === 'trash') {
      const promises = selectedItems.map((item) => {
        return dispatch(
          updateCustomPostTypeStatus({
            post_id: item.ID,
            post_status: 'trash',
          })
        );
      });

      Promise.all(promises).then(() => {
        handleReset();
        dispatch(fetchUpdatedMenu());
      });
    } else if (option === 'restore') {
      selectedItems.forEach((item) => {
        dispatch(
          updateCustomPostTypeStatus({
            post_id: item.ID,
            post_status: 'draft',
          })
        );
      });
      handleReset();
    } else {
      const promises = selectedItems.map((item) => {
        return dispatch(deleteCustomPostType({ params: item.ID })).then(
          () => {
            dispatch(
              deletePostTypeLocalize(
                item?.post_content?.post_slug
              )
            );
          }
        );
      });

      Promise.all(promises).then(() => {
        handleReset();
        dispatch(fetchUpdatedMenu());
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
    if (
      row.post_status === 'trash' &&
      // eslint-disable-next-line
      window.confirm(__('Are you sure you want to permanently delete this item?', 'easy-content-manager'))) {
      dispatch(
        deleteCustomPostType({
          params: row?.ID,
        })
      ).then(() => {
        dispatch(deletePostTypeLocalize(row?.post_content?.post_slug));
        dispatch(
          deleteMenu({
            slug: row?.post_content?.post_slug,
          })
        );
      });
    }
  };

  const duplicateHandler = (item) => {
    const duplicatePostTypeData = {
      ...item,
      tempId: `${new Date().toISOString()}`,
      post_status: 'draft',
      post_title: `${item?.post_title} - (copy)`,
      post_content: {
        ...item?.post_content,
        post_slug: `${item?.post_content?.post_slug}-copy`,
      },
    };
    dispatch(
      duplicateCustomPostType({
        params: item?.id ? item?.id : item?.ID,
        duplicatePostTypeData,
      })
    );
  };

  const handleStatusChange = (row, status) => {
    if (status === 'deactivate' || status === 'restore') {
      dispatch(
        updateCustomPostTypeStatus({
          post_id: row.ID,
          post_status: 'draft',
        })
      ).then(() => {
        dispatch(deletePostTypeLocalize(row?.post_content?.post_slug));
        dispatch(fetchUpdatedMenu());
        dispatch(fetchCollections({}));
      });
    } else if (status === 'trash') {
      dispatch(
        updateCustomPostTypeStatus({
          post_id: row.ID,
          post_status: status,
        })
      ).then(() => {
        dispatch(deletePostTypeLocalize(row?.post_content?.post_slug));
        dispatch(fetchUpdatedMenu());
        dispatch(fetchCollections({}));
      });
    } else if (status === 'activate') {
      dispatch(
        updateCustomPostTypeStatus({
          post_id: row.ID,
          post_status: 'publish',
        })
      ).then(() => {
        const updatedLocalizeData = {
          custom_fields: row?.post_content?.custom_fields,
          rest_base: row?.post_content?.rest_base,
          rest_namespace: row?.post_content?.rest_namespace,
          taxonomy: row?.post_content?.taxonomies,
          title: row?.post_title,
          supports: row?.post_content?.supports,
          postTypeSlug:
            row?.post_slug ?? row?.post_content?.post_slug,
        };
        dispatch(createPostTypeLocalize(updatedLocalizeData));
        dispatch(fetchUpdatedMenu());
        dispatch(fetchCollections({}));
      });
    }
  };

  const columns = [
    {
      name: __('Post Title', 'easy-content-manager'),
      isWidth: 'ecm-min-w--240',
      cell: (row) => {
        return (
          <>
            {row?.post_status !== 'draft' ? (
              <Link
                to={
                  row?.post_status !== 'draft' &&
                  `${route_path}admin.php?page=${appName}-${row?.post_content?.post_slug}`
                }
                state={row}
                className="ecm-title-wrap"
              >
                <span
                  dangerouslySetInnerHTML={{
                    __html: sliceString(
                      capitalizeFirstLetter(
                        row?.post_title
                      )
                    ),
                  }}
                  className="ecm-title-wrap__title"
                />
              </Link>
            ) : (
              <Text
                margin={0}
                cursor="not-allowed"
                state={row}
                className="ecm-title-wrap"
              >
                <span
                  dangerouslySetInnerHTML={{
                    __html: sliceString(
                      capitalizeFirstLetter(
                        row?.post_title
                      )
                    ),
                  }}
                  className="ecm-title-wrap__title"
                />
                <span className="ecm-title-wrap__inactive">
                  &#x2014;
                  <span className="easy-content-manager-icon easy-content-manager-icon--eye-slash"></span>
                  <span>
                    {__('Inactive', 'easy-content-manager')}
                  </span>
                </span>
              </Text>
            )}
          </>
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
      name: __('Taxonomies', 'easy-content-manager'),
      cell: (row) => {
        const taxonomies = row?.post_content?.taxonomies
          .map((item) => capitalizeFirstLetter(item))
          .join(', ');

        return (
          <span dangerouslySetInnerHTML={{ __html: taxonomies }} />
        );
      },
    },
    {
      name: __('Field Groups	', 'easy-content-manager'),
      cell: (row) => {
        return (
          <span>
            {replaceTableRowItemWhitSpace(row?.total_fields)}
          </span>
        );
      },
    },
    {
      name: __('Posts', 'easy-content-manager'),
      cell: (row) => {
        return (
          <div className="content-manager-table-title-wrap">
            <span>{replaceTableRowItemWhitSpace(row?.posts)}</span>
          </div>
        );
      },
    },
  ];

  const subHeaderComponentMemo = React.useMemo(() => {
    const searchHandler = (value) => {
      setDataFetchingStatus(true);
      dispatch(fetchCollections({ search: value })).then(() => {
        handleNavigate(itemStatus, value);
        setDataFetchingStatus(false);
      });
    };

    const dataFetchHandler = (itemStatus) => {
      setDataFetchingStatus(true);
      dispatch(fetchCollections({ status: itemStatus })).then(() => {
        handleNavigate(itemStatus);
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
                  handleNavigate(item?.value);
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
  }, [status, searchText, perPage]);

  const hoverAction = {
    cell: (row) => {
      const publishActions =
        row.post_status !== 'trash'
          ? [
            {
              type: 'button',
              label: __('Edit', 'easy-content-manager'),
              onClick: () => {
                const url = `${route_path}admin.php?page=${appName}-post-types&path=edit-post&action=edit&id=${row?.ID}`;
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
                row?.post_status === 'draft'
                  ? __('Activate', 'easy-content-manager')
                  : __(
                    'Deactivate',
                    'easy-content-manager'
                  ),
              onClick: () => {
                const conditionalStatus =
                  (row?.post_status === 'publish' &&
                    'deactivate') ||
                  (row?.post_status === 'draft' &&
                    'activate');
                handleStatusChange(row, conditionalStatus);
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
              label: __('Trash', 'easy-content-manager'),
              onClick: () => handleStatusChange(row, 'trash'),
            },
          ]
          : [];

      const trashActions =
        row.post_status === 'trash'
          ? [
            {
              type: 'button',
              label: __('Restore', 'easy-content-manager'),
              onClick: () =>
                handleStatusChange(row, 'restore'),
            },
            {
              type: 'button',
              prefix: 'trash',
              suffix: 'trash',
              label: __(
                'Delete Permanently',
                'easy-content-manager'
              ),
              onClick: () => deleteHandler(row),
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
              color="var(--ecm-primary)"
              type="subtitle"
              fontWeight="medium"
              label={__('Post Types', 'easy-content-manager')}
            />
          </>
        )}
        rightContent={() => {
          return (
            <Button
              {...primaryBtn}
              onClick={() => {
                const url = `${route_path}admin.php?page=${appName}-post-types&path=add-post&action=add`;
                navigate(url);
              }}
            >
              <span className="easy-content-manager-icon easy-content-manager-icon--plus has-ecm-blue-bg" />
              {__('Add Post Type', 'easy-content-manager')}
            </Button>
          );
        }}
      />

      <div className="content-manager-page-content">

        <SubTopBar
          heading={__('Post Types', 'easy-content-manager')}
        />

        <ListTable
          columns={columns}
          data={data?.totalPosts ? data?.totalPosts : []}
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
          showPagination={false}
          dataFetchingStatus={dataFetchingStatus}
          resetSelected={resetSelectedItems}
          suffix="dynamic-post-table"
          hoverAction={hoverAction}
          Button={
            <Button
              bg="var(--ecm-primary)"
              onClick={() => {
                const url = `${route_path}admin.php?page=${appName}-post-types&path=add-post&action=add`;
                navigate(url);
              }}
            >
              <span className="easy-content-manager-icon easy-content-manager-icon--plus has-ecm-blue-bg" />
              {__('Add Post Type', 'easy-content-manager')}
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
