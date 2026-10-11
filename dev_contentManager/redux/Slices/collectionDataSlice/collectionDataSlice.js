import { createAsyncThunk, createSlice } from '@reduxjs/toolkit';
import { __ } from '@wordpress/i18n';
import { showNotification } from '../notificationSlice/notificationSlice';
import { makeRequest } from '@ECMUtils/helper';
import { getUpvoteUsers } from './upvote';
import { createPostItem, deletePostItem, duplicatePostItem, fetchPostItems, fetchSinglePostItems, getPostItemCount, getUserPosts, saveUserPosts, trashPostItem, updatePostItem } from './postItems';
import { getReactionsUsers } from './reactions';

export const fetchDefaultCustomField = createAsyncThunk(
  'contentManager/fetchDefaultCustomField',
  ({ action, params }, thunkAPI) => {
    return makeRequest(action, {
      field_type: params?.field_type,
      post_type: params?.post_type,
    })
      .then((res) => res.data)
      .catch((error) => {
        return thunkAPI.rejectWithValue(
          error?.response?.data?.message ?? error.message
        );
      });
  }
);

export const deleteCustomPostType = createAsyncThunk(
  'contentManager/deleteCustomPostType',
  ({ params }, thunkAPI) => {
    return makeRequest('delete_custom_post_type', {
      post_id: params,
    })
      .then((res) => res.data)
      .catch((error) => {
        return thunkAPI.rejectWithValue(
          error?.response?.data?.message ?? error.message
        );
      });
  }
);

export const createCollection = createAsyncThunk(
  'contentManager/createCollection',
  (values, thunkAPI) => {
    return makeRequest('create_custom_post_type', {
      ...values,
    })
      .then((res) => {
        if (res.status === 200) {
          thunkAPI.dispatch(
            showNotification({
              message: __(
                'Successfully created Post!',
                'easy-content-manager'
              ),
              isShow: true,
              type: 'success',
            })
          );
          return res.data.data;
        }

        if (res.status === 409) {
          thunkAPI.dispatch(
            showNotification({
              message: __(
                'Post type already exists',
                'easy-content-manager'
              ),
              isShow: true,
              type: 'error',
            })
          );
          return thunkAPI.rejectWithValue(
            __('Post type already exists', 'easy-content-manager')
          );
        }

        thunkAPI.dispatch(
          showNotification({
            message: __(res?.data?.message, 'easy-content-manager'),
            isShow: true,
            type: 'error',
          })
        );
        return thunkAPI.rejectWithValue(res.data.data);
      })
      .catch((error) => {
        thunkAPI.dispatch(
          showNotification({
            message:
              error?.response?.data?.message ?? error.message,
            isShow: true,
            type: 'error',
          })
        );
        return thunkAPI.rejectWithValue(
          error?.response?.data?.message ?? error.message
        );
      });
  }
);

export const fetchCollections = createAsyncThunk(
  'contentManager/fetchCollections',
  ({ search = '', status = '' }, thunkAPI) => {
    const params = {};
    if (search) params.search = search;
    if (status && status !== 'all') params.status = status;

    return makeRequest('get_all_custom_post_types', params)
      .then((res) => res.data.data)
      .catch((error) => {
        thunkAPI.dispatch(
          showNotification({
            message:
              error?.response?.data?.message ?? error.message,
            isShow: true,
            type: 'error',
          })
        );
        return thunkAPI.rejectWithValue(
          error?.response?.data?.message ?? error.message
        );
      });
  }
);

export const fetchExcludedPostTypes = createAsyncThunk(
  'contentManager/fetchExcludedPostTypes',
  ({ post_type_slug = '' }, thunkAPI) => {
    return makeRequest('get_excluded_post_type_details', {
      post_type_slug: post_type_slug ?? '',
    })
      .then((res) => res.data.data)
      .catch((error) => {
        thunkAPI.dispatch(
          showNotification({
            message:
              error?.response?.data?.message ?? error.message,
            isShow: true,
            type: 'error',
          })
        );
        return thunkAPI.rejectWithValue(
          error?.response?.data?.message ?? error.message
        );
      });
  }
);

export const getPostType = createAsyncThunk(
  'contentManager/getPostType',
  ({ post_id, post_title }, thunkAPI) => {
    return makeRequest('get_single_custom_post_type', {
      post_id: post_id ? parseInt(post_id) : 0,
      post_title: post_title || '',
    })
      .then((res) => res.data.data)
      .catch((error) => {
        thunkAPI.dispatch(
          showNotification({
            message:
              error?.response?.data?.message ?? error.message,
            isShow: true,
            type: 'error',
          })
        );
        return thunkAPI.rejectWithValue(
          error?.response?.data?.message ?? error.message
        );
      });
  }
);

export const fetchPostFieldsBySingularName = createAsyncThunk(
  'contentManager/fetchPostFieldBySingularName',
  ({ params }, thunkAPI) => {
    return makeRequest('get_post_fields_by_singular_name', {
      singular_name: params,
    })
      .then((res) => res.data.data)
      .catch((error) => {
        thunkAPI.dispatch(
          showNotification({
            message:
              error?.response?.data?.message ?? error.message,
            isShow: true,
            type: 'error',
          })
        );
        return thunkAPI.rejectWithValue(
          error?.response?.data?.message ?? error.message
        );
      });
  }
);

export const updateCustomPostTypeStatus = createAsyncThunk(
  'contentManager/updateCustomPostTypeStatus',
  (formData, thunkAPI) => {
    return makeRequest('update_custom_post_type_status', formData)
      .then((res) => {
        if (res.status === 200) {
          thunkAPI.dispatch(
            showNotification({
              message: __(
                'Successfully updated Post Status!',
                'easy-content-manager'
              ),
              isShow: true,
              type: 'success',
            })
          );
          return res.data.data;
        }
        thunkAPI.dispatch(
          showNotification({
            message: __(
              'Failed to update Post Status!',
              'easy-content-manager'
            ),
            isShow: true,
            type: 'error',
          })
        );
        return thunkAPI.rejectWithValue(res.data);
      })
      .catch((error) => {
        thunkAPI.dispatch(
          showNotification({
            message:
              error?.response?.data?.message ?? error.message,
            isShow: true,
            type: 'error',
          })
        );
        return thunkAPI.rejectWithValue(error.message);
      });
  }
);

export const updateCollection = createAsyncThunk(
  'contentManager/updateCollection',
  (values, thunkAPI) => {
    return makeRequest('update_custom_post_type', values)
      .then((res) => {
        if (res.status === 200) {
          thunkAPI.dispatch(
            showNotification({
              message: __(
                'Successfully updated Post!',
                'easy-content-manager'
              ),
              isShow: true,
              type: 'success',
            })
          );
          return res.data.data;
        }
        thunkAPI.dispatch(
          showNotification({
            message: __(
              'Failed to update Post!',
              'easy-content-manager'
            ),
            isShow: true,
            type: 'error',
          })
        );
        return thunkAPI.rejectWithValue(res.data);
      })
      .catch((error) => {
        thunkAPI.dispatch(
          showNotification({
            message:
              error?.response?.data?.message ?? error.message,
            isShow: true,
            type: 'error',
          })
        );
        return thunkAPI.rejectWithValue(error.message);
      });
  }
);

export const duplicateCustomPostType = createAsyncThunk(
  'contentManager/duplicateCustomPostType',
  ({ params, duplicatePostTypeData }, thunkAPI) => {
    return makeRequest('duplicate_custom_post_type', {
      post_id: params,
    })
      .then((res) => {
        if (res.status === 200) {
          thunkAPI.dispatch(
            showNotification({
              message: __(
                'Successfully duplicated Post!',
                'easy-content-manager'
              ),
              isShow: true,
              type: 'success',
            })
          );
          return {
            data: res.data.data,
            duplicatePostTypeData,
          };
        }
        thunkAPI.dispatch(
          showNotification({
            message: __(
              'Failed to duplicate Post!',
              'easy-content-manager'
            ),
            isShow: true,
            type: 'error',
          })
        );
        return thunkAPI.rejectWithValue(res.data);
      })
      .catch((error) => {
        thunkAPI.dispatch(
          showNotification({
            message:
              error?.response?.data?.message ?? error.message,
            isShow: true,
            type: 'error',
          })
        );
        return thunkAPI.rejectWithValue(error.message);
      });
  }
);

const collectionsDataSlice = createSlice({
  name: 'post_types',
  initialState: {
    totalPosts: [],
    inharitedPostType: false,
    upvotedUsers: {},
    reactionsUsers: {},
  },
  reducers: {
    updateCurrentPostTypePage: (state, action) => {
      state.currentPage = action.payload;
    },
    updatePostTypeItemPerPage: (state, action) => {
      state.itemPerPage = action.payload;
    },
    updateCurrentPostItemPage: (state, action) => {
      state[action.payload.keyName].currentPage =
        action.payload.currentPage;
    },
    updatePostItemPerPage: (state, action) => {
      state[action.payload.keyName].itemPerPage =
        action.payload.itemPerPage;
    },
    applyInharitedPostType: (state, action) => {
      state.inharitedPostType = action.payload;
    },
    updateUpvoteItemPage: (state, action) => {
      state.upvotedUsers.currentPage = action.payload;
    },
    updateUpvoteItemPerPage: (state, action) => {
      state.upvotedUsers.itemPerPage = action.payload;
    },
    updateReactionItemPage: (state, action) => {
      state.reactionsUsers.currentPage = action.payload;
    },
    updateReactionItemPerPage: (state, action) => {
      state.reactionsUsers.itemPerPage = action.payload;
    },
  },
  extraReducers: (builder) => {
    builder
      // Fetch all post_types
      .addCase(fetchCollections.fulfilled, (state, action) => {
        state.isLoading = false;
        state.totalPosts = action?.payload;
      })
      // Fetch post_types
      .addCase(getPostType.fulfilled, (state, action) => {
        state.isLoading = false;
        state.totalPosts = [action?.payload];
      })
      // Create post_types
      .addCase(createCollection.fulfilled, (state, action) => {
        state.isLoading = false;
        state.totalPosts = [action?.payload, ...state.totalPosts];
      })
      /**
       * Handles custom post type duplication with optimistic updates:
       *
       * PENDING:
       * - Sets loading state
       * - Immediately adds the new post type (with temporary ID) to state
       *   (optimistic update for responsive UI)
       *
       * FULFILLED:
       * - Replaces temporary post type with server-validated version
       * - Maintains same position in the list
       * - Clears loading state
       *
       * REJECTED:
       * - Removes the temporarily added post type on failure
       * - Clears loading state
       * - Provides automatic rollback of optimistic update
       *
       * Note: Uses tempId to track the temporary duplicate before server response
       */
      .addCase(duplicateCustomPostType.pending, (state, action) => {
        state.isLoading = true;
        const { duplicatePostTypeData } = action.meta.arg;
        state.totalPosts = [duplicatePostTypeData, ...state.totalPosts];
      })
      .addCase(duplicateCustomPostType.fulfilled, (state, action) => {
        state.isLoading = false;
        const { duplicatePostTypeData } = action.meta.arg;

        state.totalPosts = state.totalPosts.map((item) => {
          if (item.tempId === duplicatePostTypeData.tempId) {
            return { ...action.payload.data };
          }
          return item;
        });
      })
      .addCase(duplicateCustomPostType.rejected, (state, action) => {
        state.isLoading = false;
        const { duplicatePostTypeData } = action.meta.arg;
        state.totalPosts = state.totalPosts.filter(
          (item) => item.tempId !== duplicatePostTypeData.tempId
        );
      })
      // Update post_types
      .addCase(updateCollection.fulfilled, (state, action) => {
        state.isLoading = false;
        state.totalPosts = state.totalPosts.map((item) => {
          if (parseInt(item.ID) === parseInt(action.payload.ID)) {
            return { ...item, ...action.payload };
          }
          return item;
        });
      })
      // Update post_type Status
      .addCase(updateCustomPostTypeStatus.fulfilled, (state, action) => {
        state.isLoading = false;
        state.totalPosts = state.totalPosts
          .map((item) => {
            if (parseInt(item.ID) === parseInt(action.payload.ID)) {
              return { ...item, ...action.payload };
            }
            return item;
          })
          .filter((item) => item.post_status !== 'trash');
      })
      // Delete custom post type
      .addCase(deleteCustomPostType.fulfilled, (state, action) => {
        state.isLoading = false;
        const newPosts = state.totalPosts.filter(
          (item) => item?.ID !== action?.payload?.data?.id
        );
        state.totalPosts = newPosts;
      })
      // Fetch post items
      .addCase(fetchPostItems.fulfilled, (state, action) => {
        const { keyName, data, totalItems, itemPerPage } =
          action?.payload;

        state.isLoading = false;
        if (!state[keyName]) {
          state[keyName] = {
            data: [],
            totalItems: 0,
            itemPerPage: 0,
          };
        }

        state[keyName].data = data;
        state[keyName].totalItems = totalItems;
        state[keyName].itemPerPage = itemPerPage;
      })
      // Fetch post items
      .addCase(getUserPosts.fulfilled, (state, action) => {
        const { keyName, data, totalItems, itemPerPage, currentPage } =
          action?.payload;

        state.isLoading = false;
        if (!state[keyName]) {
          state[keyName] = {
            data: [],
            totalItems: 0,
            itemPerPage: 0,
          };
        }

        state[keyName].data = data;
        state[keyName].totalItems = totalItems;
        state[keyName].itemPerPage = itemPerPage;
      })
      //fetch single post item
      .addCase(fetchSinglePostItems.fulfilled, (state, actions) => {
        state.isLoading = false;
        const { keyName, data } = actions?.payload;

        if (!state[keyName]) {
          state[keyName] = {
            data: [],
            totalItems: 0,
            itemPerPage: 0,
            itemCount: {},
          };
        }
        state[keyName].data = [data];
      })
      //fetch post item count
      .addCase(getPostItemCount.fulfilled, (state, actions) => {
        state.isLoading = false;
        const { keyName, data } = actions?.payload;

        state[keyName].itemCount = data;
      })
      // Create post items
      .addCase(createPostItem.fulfilled, (state, action) => {
        state[action.payload.keyName].data = [
          action?.payload.data,
          ...state[action.payload.keyName].data,
        ];
        state.isLoading = false;
      })
      /**
       * Handles post item duplication state changes in three phases:
       *
       * 1. PENDING (optimistic update):
       *    - Immediately shows loading state
       *    - Adds the new duplicate item with temporary ID at the start of the list
       *    - This makes the UI feel fast while waiting for server response
       *
       */
      .addCase(duplicatePostItem.pending, (state, action) => {
        state.isLoading = true;
        const { duplicatePostItemData, keyName } = action.meta.arg;
        state[keyName].data = [
          duplicatePostItemData,
          ...state[keyName].data,
        ];
      })
      // .addCase(duplicatePostItem.fulfilled, (state, action) => {
      // 	const { keyName, duplicatePostItemData } = action.meta.arg;
      // 	state[keyName].data = state[keyName].data.map((item) => {
      // 		if (item.tempId === duplicatePostItemData.tempId) {
      // 			return action.payload.data;
      // 		}
      // 		return item;
      // 	});
      // 	state.isLoading = false;
      // })
      // .addCase(duplicatePostItem.rejected, (state, action) => {
      // 	state.isLoading = false;
      // 	const { keyName, duplicatePostItemData } = action.meta.arg;
      // 	state[keyName].data = state[keyName].data.filter(
      // 		(item) => item.tempId !== duplicatePostItemData.tempId
      // 	);
      // })
      // Update post items
      .addCase(updatePostItem.fulfilled, (state, action) => {
        state.isLoading = false;

        state[action.payload.keyName].data = state[
          action.payload.keyName
        ].data.map((item) => {
          if (
            parseInt(item.id) === parseInt(action.payload.data.id)
          ) {
            return {
              ...item,
              ...action.payload.data,
            };
          }
          return item;
        });
      })
      // Save post items
      .addCase(saveUserPosts.fulfilled, (state, action) => {
        state.isLoading = false;


        const stateIds = state[action.payload.keyName].data.map( (item) => item.id );
        if (
          !stateIds.includes(action.payload.data.id)
        ) {
          state[action.payload.keyName].data = [
            action.payload.data,
            ...state[action.payload.keyName].data,
          ];
        } else {
          state[action.payload.keyName].data = state[
            action.payload.keyName
          ].data.map((item) => {
            if (
              parseInt(item.id) === parseInt(action.payload.data.id)
            ) {
              return {
                ...item,
                ...action.payload.data,
              };
            }
            return item;
          });
        }
      })
      // Delete post items
      .addCase(deletePostItem.fulfilled, (state, action) => {
        state.isLoading = false;
        state[action.payload.keyName].data = state[
          action.payload.keyName
        ].data.filter(
          (item) =>
            parseInt(item.id) !== parseInt(action.payload.data.id)
        );
      })
      // Trash post items
      .addCase(trashPostItem.fulfilled, (state, action) => {
        state.isLoading = false;
        state[action.payload.keyName].data = state[
          action.payload.keyName
        ].data
          .map((item) => {
            if (
              parseInt(item.id) ===
              parseInt(action.payload.data.id)
            ) {
              return {
                ...item,
                ...action.payload.data,
              };
            }
            return item;
          })
          .filter((item) => item.status !== 'trash');
      })
      .addCase(getUpvoteUsers.fulfilled, (state, action) => {
        const postId = Number(action.meta.arg.post_id);

        if (!state.upvotedUsers[postId]) state.upvotedUsers[postId] = {};
        if (!state.upvotedUsers[postId]?.data) state.upvotedUsers[postId].data = [];
        if (!state.upvotedUsers[postId]?.anonymousUpvotes) state.upvotedUsers[postId].anonymousUpvotes = 0;
        if (!state.upvotedUsers[postId]?.currentPage) state.upvotedUsers[postId].currentPage = 1;
        if (!state.upvotedUsers[postId]?.itemPerPage) state.upvotedUsers[postId].itemPerPage = 10;
        if (!state.upvotedUsers[postId]?.totalItems) state.upvotedUsers[postId].totalItems = 0;

        state.upvotedUsers[postId].data = action.payload.data;
        state.upvotedUsers[postId].anonymousUpvotes = action.payload.anonymousUpvotes;
        state.upvotedUsers[postId].currentPage = action.payload.currentPage;
        state.upvotedUsers[postId].itemPerPage = action.payload.itemPerPage;
        state.upvotedUsers[postId].totalItems = action.payload.totalItems;
      })
      .addCase(getReactionsUsers.fulfilled, (state, action) => {
        const postId = Number(action.meta.arg.post_id);

        if (!state.reactionsUsers[postId]) state.reactionsUsers[postId] = {};
        if (!state.reactionsUsers[postId]?.data) state.reactionsUsers[postId].data = [];
        if (!state.reactionsUsers[postId]?.currentPage) state.reactionsUsers[postId].currentPage = 1;
        if (!state.reactionsUsers[postId]?.itemPerPage) state.reactionsUsers[postId].itemPerPage = 10;
        if (!state.reactionsUsers[postId]?.totalItems) state.reactionsUsers[postId].totalItems = 0;

        state.reactionsUsers[postId].data = action.payload.data;
        state.reactionsUsers[postId].currentPage = action.payload.currentPage;
        state.reactionsUsers[postId].itemPerPage = action.payload.itemPerPage;
        state.reactionsUsers[postId].totalItems = action.payload.totalItems;
      })
  },
});

export const {
  updateCurrentPostTypePage,
  updatePostTypeItemPerPage,
  updateCurrentPostItemPage,
  updatePostItemPerPage,
  applyInharitedPostType,
  updateUpvoteItemPage,
  updateUpvoteItemPerPage,
  updateReactionItemPage,
  updateReactionItemPerPage,
} = collectionsDataSlice.actions;
export default collectionsDataSlice.reducer;
