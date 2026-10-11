import { createAsyncThunk, createSlice } from '@reduxjs/toolkit';
import { API, makeRequest, namespace } from '@ECMUtils/helper';
import { showNotification } from '../notificationSlice/notificationSlice';
import { __ } from '@wordpress/i18n';

export const getAllTaxonomiesTag = createAsyncThunk(
	'contentManager/getAllTaxonomiesTag',
	(_, thunkAPI) => {
		try {
			return makeRequest('get_post_types_taxonomy').then((res) => {
				return res.data.data;
			});
		} catch (error) {
			thunkAPI.dispatch(
				showNotification({
					message: error?.response?.data?.message ?? error.message,
					isShow: true,
					type: 'error',
				})
			);
			return thunkAPI.rejectWithValue(error.message);
		}
	}
);

export const getAllCustomTaxonomies = createAsyncThunk(
	'contentManager/getAllCustomTaxonomies',
	({ search = '', page = 1, per_page = 10, status = 'any', order_by = 'post_title', order = 'asc' }, thunkAPI) => {
		const params = {
			page,
			per_page,
			order_by,
			order
		};
		if (search) params.search = search;
		if (status) params.status = status;

		try {
			return makeRequest('get_all_custom_taxonomies', params).then(
				(res) => {
					return {
						data: res.data.data.data,
						totalItems: res.data.data.pagination.total,
						page,
						per_page,
					};
				}
			);
		} catch (error) {
			thunkAPI.dispatch(
				showNotification({
					message: error?.response?.data?.message ?? error.message,
					isShow: true,
					type: 'error',
				})
			);
			return thunkAPI.rejectWithValue(error.message);
		}
	}
);

export const getTaxonomyPriority = createAsyncThunk(
	'contentManager/getTaxonomyPriority',
	({ search = '', per_page = 10, taxonomy }, thunkAPI) => {
		const params = {
			per_page,
			taxonomy
		};
		if (search) params.search = search;

		try {
			return API.get(`${namespace}terms-priority`, { params })
			.then(
				(res) => {
					return res.data;
				}
			);
		} catch (error) {
			thunkAPI.dispatch(
				showNotification({
					message: error?.response?.data?.message ?? error.message,
					isShow: true,
					type: 'error',
				})
			);
			return thunkAPI.rejectWithValue(error.message);
		}
	}
);

export const updateTaxonomyPriority = createAsyncThunk(
	'contentManager/updateTaxonomyPriority',
	({ taxonomy, priorities}, thunkAPI) => {
		const params = {
			priorities,
			taxonomy
		};

		try {
			return API.post(`${namespace}terms-priority`, params)
			.then(
				(res) => {
					return res.data.updated;
				}
			);
		} catch (error) {
			thunkAPI.dispatch(
				showNotification({
					message: error?.response?.data?.message ?? error.message,
					isShow: true,
					type: 'error',
				})
			);
			return thunkAPI.rejectWithValue(error.message);
		}
	}
);

export const createCustomTaxonomy = createAsyncThunk(
	'contentManager/createCustomTaxonomy',
	(formData, thunkAPI) => {
		return makeRequest('create_custom_taxonomy', formData)
			.then((res) => {
				if (res?.data?.success) {
					thunkAPI.dispatch(
						showNotification({
							message: __(
								'Successfully created Taxonomy!',
								'easy-content-manager'
							),
							isShow: true,
							type: 'success',
						})
					);
					return res.data.data;
				}
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

export const updateCustomTaxonomy = createAsyncThunk(
	'contentManager/updateCustomTaxonomy',
	(formData, thunkAPI) => {
		return makeRequest('update_custom_taxonomy', formData)
			.then((res) => {
				if (res?.data?.success) {
					thunkAPI.dispatch(
						showNotification({
							message: __(
								'Successfully updated Taxonomy!',
								'easy-content-manager'
							),
							isShow: true,
							type: 'success',
						})
					);
					return res.data.data;
				}
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

export const duplicateCustomTaxonomy = createAsyncThunk(
	'contentManager/duplicateCustomTaxonomy',
	(formData, thunkAPI) => {
		return makeRequest('duplicate_custom_taxonomy', {
			taxonomy_id: formData.taxonomy_id,
		})
			.then((res) => {
				thunkAPI.dispatch(
					showNotification({
						message: __(
							'Successfully duplicated Taxonomy!',
							'easy-content-manager'
						),
						isShow: true,
						type: 'success',
					})
				);
				return {
					data: res.data.data,
					duplicateData: formData,
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

export const deleteCustomTaxonomy = createAsyncThunk(
	'contentManager/deleteCustomTaxonomy',
	(post_id, thunkAPI) => {
		return makeRequest('delete_custom_taxonomy', { post_id })
			.then((res) => {
				if (res?.data?.success) {
					thunkAPI.dispatch(
						showNotification({
							message: __(
								'Successfully deleted Taxonomy!',
								'easy-content-manager'
							),
							isShow: true,
							type: 'success',
						})
					);
					return post_id;
				}
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

export const getSingleCustomTaxonomy = createAsyncThunk(
	'contentManager/getSingleCustomTaxonomy',
	(post_id, thunkAPI) => {
		return makeRequest('get_single_custom_taxonomy', { post_id })
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

const taxonomySlice = createSlice({
	name: 'taxonomies',
	initialState: {
		data: [],
		totalItems: 0,
		itemPerPage: 10,
		currentPage: 1,
		loading: false,
	},
	reducers: {
		updateCurrentPage: (state, action) => {
			state.currentPage = action.payload;
		},
		updateItemPerPage: (state, action) => {
			state.itemPerPage = action.payload;
		},
	},
	extraReducers: (builder) => {
		builder
			// get all taxonomies
			.addCase(getAllCustomTaxonomies.fulfilled, (state, action) => {
				state.loading = false;
				state.data = action.payload.data;
				state.totalItems = action.payload.totalItems;
				state.itemPerPage = action.payload.per_page;
				state.currentPage = action.payload.page;
			})

			// get single taxonomy
			.addCase(getSingleCustomTaxonomy.fulfilled, (state, action) => {
				state.loading = false;
				state.data = [action.payload];
			})

			// create custom taxonomy
			.addCase(createCustomTaxonomy.fulfilled, (state, action) => {
				state.loading = false;
				state.data = [action.payload, ...state.data];
			})

			// update custom taxonomy
			.addCase(updateCustomTaxonomy.fulfilled, (state, action) => {
				state.loading = false;
				state.data = state.data.map((taxonomy) => {
					if (Number(taxonomy.ID) === Number(action.payload.ID)) {
						return action.payload;
					}
					return taxonomy;
				});
			})

			// delete custom taxonomy
			.addCase(deleteCustomTaxonomy.fulfilled, (state, action) => {
				state.loading = false;
				state.data = state.data.filter(
					(taxonomy) => Number(taxonomy.ID) !== Number(action.payload)
				);
			})

			// duplicate custom taxonomy
			.addCase(duplicateCustomTaxonomy.pending, (state, action) => {
				state.loading = false;
				const { duplicateData } = action.meta.arg;

				state.data = [duplicateData, ...state.data];
			})

			.addCase(duplicateCustomTaxonomy.fulfilled, (state, action) => {
				state.loading = false;
				const { duplicateData } = action.meta.arg;
				const { data } = action.payload;

				state.data = state.data.map((taxonomy) => {
					if (taxonomy.tempId === duplicateData.tempId) {
						return data;
					}
					return taxonomy;
				});
			})

			.addCase(duplicateCustomTaxonomy.rejected, (state) => {
				state.loading = false;
				state.data = state.data.filter((taxonomy) => !taxonomy.tempId);
			});
	},
});

export const { updateCurrentPage, updateItemPerPage } = taxonomySlice.actions;

export default taxonomySlice.reducer;
