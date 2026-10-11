import { createAsyncThunk, createSlice } from '@reduxjs/toolkit';
import { makeRequest, API, menu } from '@ECMUtils/helper';
import { __ } from '@wordpress/i18n';
import { showNotification } from '../notificationSlice/notificationSlice';

export const createFieldGroup = createAsyncThunk(
	'contentManager/createFieldGroup',
	(formData, thunkAPI) => {
		return makeRequest('create_field_group', {
			group_name: formData.group_name,
			location: formData.location,
			custom_fields: formData.field_groups,
			slug: formData.slug,
		})
			.then((res) => {
				if (res?.data.success) {
					thunkAPI.dispatch(
						showNotification({
							message: __(
								'Successfully created field group!',
								'easy-content-manager'
							),
							isShow: true,
							type: 'success',
						})
					);
					return {
						custom_fields: res.data.data.post_content.custom_fields,
						id: res.data.data.ID,
						location: res.data.data.post_content.location,
						title: res.data.data.post_title,
						slug: res.data.data.post_content.post_slug,
						total_fields:
							res.data.data.post_content.custom_fields.length,
					};
				}
				thunkAPI.dispatch(
					showNotification({
						message: __(
							'Failed to create field group!',
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

export const deleteFieldGroup = createAsyncThunk(
	'contentManager/deleteFieldGroup',
	(id, thunkAPI) => {
		return makeRequest('delete_field_group', {
			group_id: id,
			force: true,
		})
			.then((res) => {
				if (res.status === 200) {
					thunkAPI.dispatch(
						showNotification({
							message: __(
								'Successfully deleted field group!',
								'easy-content-manager'
							),
							isShow: true,
							type: 'success',
						})
					);
					return res.data.data.id;
				}
				thunkAPI.dispatch(
					showNotification({
						message: __(
							'Failed to trash item!',
							'easy-content-manager'
						),
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
				return thunkAPI.rejectWithValue(error.message);
			});
	}
);

export const updateFieldGroups = createAsyncThunk(
	'contentManager/updateFieldGroups',
	(formData, thunkAPI) => {
		return makeRequest('update_field_group', {
			group_id: formData.id,
			group_name: formData.group_name,
			slug: formData.slug,
			location: formData.location,
			custom_fields: formData.field_groups,
			status: formData.status,
		})
			.then((res) => {
				if (res.status === 200) {
					thunkAPI.dispatch(
						showNotification({
							message: __(
								'Successfully updated field group!',
								'easy-content-manager'
							),
							isShow: true,
							type: 'success',
						})
					);
					return {
						custom_fields: res.data.data.post_content.custom_fields,
						id: res.data.data.ID,
						location: res.data.data.post_content.location,
						title: res.data.data.post_title,
						slug: res.data.data.post_content.post_slug,
						status: res.data.data.post_status,
					};
				}
				thunkAPI.dispatch(
					showNotification({
						message: __(
							'Failed to update item!',
							'easy-content-manager'
						),
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
				return thunkAPI.rejectWithValue(error.message);
			});
	}
);

export const getExistingPostType = createAsyncThunk(
	'contentManager/getExistingPostType',
	(_, thunkAPI) => {
		return makeRequest('get_existing_post_type', {
			built_in: false,
		})
			.then((res) => {
				return res.data.data;
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

export const getAllUserRoles = createAsyncThunk(
	'contentManager/getAllUserRoles',
	(_, thunkAPI) => {
		return makeRequest('get_all_user_roles')
			.then((res) => {
				return res.data.data;
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

export const getAllFieldGroups = createAsyncThunk(
	'contentManager/getAllFieldGroups',
	({ page = 1, per_page = 10, search = '', status = '' }, thunkAPI) => {
		const params = { page, per_page };
		if (search) params.search = search;
		if (status && status !== 'all') params.status = status;

		return makeRequest('get_all_field_groups', params)
			.then((res) => {
				return {
					data: res.data.data,
					totalItems: parseInt(res.headers['x-wp-total']),
					page,
					per_page,
				};
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

export const getFieldGroups = createAsyncThunk(
	'contentManager/getFieldGroups',
	(formData, thunkAPI) => {
		return makeRequest('get_field_groups', {
			group_id: formData.ID ?? 0,
			group_name: formData.group_name ?? '',
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
				return thunkAPI.rejectWithValue(error.message);
			});
	}
);

export const duplicateFieldGroup = createAsyncThunk(
	'contentManager/duplicateFieldGroup',
	(formData, thunkAPI) => {
		return makeRequest('duplicate_field_group', {
			group_id: formData.ID ?? 0,
		})
			.then((res) => {
				return {
					custom_fields: res.data.data.post_content.custom_fields,
					id: res.data.data.ID,
					location: res.data.data.post_content.location,
					title: res.data.data.post_title,
					post_slug: res.data.data.post_content.post_slug,
					status: res.data.data.post_status,
					total_fields:
						res.data.data.post_content.custom_fields.length,
				};
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

export const getFieldGroupsCount = createAsyncThunk(
	'contentManager/getFieldGroupsCount',
	(arg, thunkAPI) => {
		return makeRequest('get_all_field_groups_count')
			.then((res) => {
				return res.data.data;
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

const fieldGroupsSlice = createSlice({
	name: 'field_groups',
	initialState: {
		data: [],
		locations: [],
		loading: false,
	},
	reducers: {
		updateCurrentPage: (state, action) => {
			state.page = action.payload;
		},
		updateItemPerPage: (state, action) => {
			state.itemsPerPgae = action.payload;
		},
	},
	extraReducers: (builder) => {
		builder
			.addCase(createFieldGroup.pending, (state) => {
				state.loading = true;
			})
			.addCase(createFieldGroup.fulfilled, (state, action) => {
				state.data = [action.payload, ...state.data];
				state.loading = false;
			})
			.addCase(createFieldGroup.rejected, (state) => {
				state.loading = false;
			})
			.addCase(getAllFieldGroups.pending, (state) => {
				state.loading = true;
			})
			.addCase(getAllFieldGroups.fulfilled, (state, action) => {
				state.data = action.payload.data;
				state.totalItems = action.payload.totalItems;
				state.page = action.payload.page;
				state.itemsPerPgae = action.payload.per_page;
				state.loading = false;
			})
			.addCase(getAllFieldGroups.rejected, (state) => {
				state.loading = false;
			})
			.addCase(updateFieldGroups.pending, (state) => {
				state.loading = true;
			})
			.addCase(updateFieldGroups.fulfilled, (state, action) => {
				state.loading = false;
				state.data = state.data.map((item) => {
					if (item.id === action.payload.id) {
						return { ...item, ...action.payload };
					}
					return item;
				});
			})
			.addCase(updateFieldGroups.rejected, (state) => {
				state.loading = false;
			})
			.addCase(deleteFieldGroup.pending, (state) => {
				state.loading = true;
			})
			.addCase(deleteFieldGroup.fulfilled, (state, action) => {
				state.loading = false;
				state.data = state.data.filter(
					(item) => item.id !== action.payload
				);
			})
			.addCase(deleteFieldGroup.rejected, (state) => {
				state.loading = false;
			})
			.addCase(getFieldGroups.fulfilled, (state, action) => {
				state.loading = false;
				state.data = state.data.map((item) => {
					if (item.id === action.payload.id) {
						return { ...item, ...action.payload };
					}
					return item;
				});
			})
			.addCase(getFieldGroupsCount.fulfilled, (state, action) => {
				state.loading = false;
				state.totalItems = action.payload;
			})

			/**
			 * Handles field group duplication with optimistic UI updates:
			 *
			 * PENDING (Initial duplication request):
			 * - Activates loading state to indicate pending operation
			 * - Immediately inserts the new field group (with temporary ID) at the beginning of the list
			 * - Provides instant visual feedback while waiting for server response
			 *
			 * FULFILLED (Successful duplication):
			 * - Replaces the temporary field group with the persisted version from server
			 * - Maintains data consistency by matching items via tempId
			 * - Deactivates loading state upon completion
			 *
			 * REJECTED (Failed duplication):
			 * - Cleans up the temporarily added field group
			 * - Ensures state consistency by removing the unsuccessful duplicate
			 * - Resets loading state
			 *
			 * Implementation Notes:
			 * - Uses optimistic updates for better perceived performance
			 * - Relies on tempId for temporary-to-permanent record reconciliation
			 * - Maintains immutable state updates throughout the process
			 */
			.addCase(duplicateFieldGroup.pending, (state, action) => {
				state.loading = true;
				const { duplicateFieldGroupData } = action.meta.arg;
				state.data = [duplicateFieldGroupData, ...state.data];
			})
			.addCase(duplicateFieldGroup.fulfilled, (state, action) => {
				state.loading = false;
				const { duplicateFieldGroupData } = action.meta.arg;

				state.data = state.data.map((item) => {
					if (item.tempId === duplicateFieldGroupData.tempId) {
						return action.payload;
					}
					return item;
				});
			})
			.addCase(duplicateFieldGroup.rejected, (state, action) => {
				state.loading = false;
				state.data = state.data.filter(
					(item) =>
						item.tempId !==
						action.meta.arg.duplicateFieldGroupData.tempId
				);
			});
	},
});

export const { updateCurrentPage, updateItemPerPage } =
	fieldGroupsSlice.actions;

export default fieldGroupsSlice.reducer;
