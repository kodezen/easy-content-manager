import { createAsyncThunk, createSlice } from '@reduxjs/toolkit';
import {
	API,
	handleSliceError,
	makeRequest,
	namespace,
} from '@ECMUtils/helper';
import { showNotification } from '../notificationSlice/notificationSlice';
import { __ } from '@wordpress/i18n';

export const importDefaultPosts = createAsyncThunk(
	'contentManager/importDefaultPosts',
	(formData, thunkAPI) => {
		try {
			return makeRequest('save_default_post_types', {
				post_types: JSON.stringify(formData),
				built_in: false,
			}).then((res) => {
				return res.data.data;
			});
		} catch (error) {
			handleSliceError(thunkAPI, error);
		}
	}
);

export const getSettings = createAsyncThunk(
	'contentManager/getSettings',
	async (_, thunkAPI) => {
		return await makeRequest('get_settings')
			.then((res) => {
				return res.data.data;
			})
			.catch((error) => {
				handleSliceError(thunkAPI, error);
			});
	}
);

export const createSettings = createAsyncThunk(
	'contentManager/createSettings',
	async (formData, thunkAPI) => {
		return await makeRequest('create_settings', formData)
			.then((res) => {
				thunkAPI.dispatch(
					showNotification({
						message: __(
							'Settings created successfully',
							'easy-content-manager'
						),
						isShow: true,
						type: 'success',
					})
				);
				return res.data.data;
			})
			.catch((error) => {
				handleSliceError(thunkAPI, error);
			});
	}
);

export const updateSettings = createAsyncThunk(
	'contentManager/updateSettings',
	async (formData, thunkAPI) => {
		return await makeRequest('update_settings', formData)
			.then((res) => {
				thunkAPI.dispatch(
					showNotification({
						message: __(
							'Settings updated successfully',
							'easy-content-manager'
						),
						isShow: true,
						type: 'success',
					})
				);
				return res.data.data;
			})
			.catch((error) => {
				handleSliceError(thunkAPI, error);
			});
	}
);

export const deleteSettings = createAsyncThunk(
	'contentManager/deleteSettings',
	async (formData, thunkAPI) => {
		return await makeRequest('delete_settings', formData)
			.then((res) => {
				thunkAPI.dispatch(
					showNotification({
						message: __(
							'Settings deleted successfully',
							'easy-content-manager'
						),
						isShow: true,
						type: 'success',
					})
				);
				return formData;
			})
			.catch((error) => {
				handleSliceError(thunkAPI, error);
			});
	}
);

export const saveSettings = createAsyncThunk(
	'contentManager/saveSettings',
	async (formData, thunkAPI) => {
		return await makeRequest('save_settings_data', formData)
			.then((res) => {
				thunkAPI.dispatch(
					showNotification({
						message: __(
							'Settings saved successfully',
							'easy-content-manager'
						),
						isShow: true,
						type: 'success',
					})
				);
				return {
					data: res.data.data,
					id: formData?.id,
					route: formData?.route,
				};
			})
			.catch((error) => {
				handleSliceError(thunkAPI, error);
			});
	}
);

export const getSavedSettings = createAsyncThunk(
	'contentManager/getSavedSettings',
	async (formData, thunkAPI) => {
		return await makeRequest('get_saved_settings', {
			option_name: formData.option_name,
			route: formData.route,
		})
			.then((res) => {
				return {
					data: res.data.data,
					id: formData?.id,
					route: formData?.route,
				};
			})
			.catch((error) => {
				handleSliceError(thunkAPI, error);
			});
	}
);

export const getBaseSettings = createAsyncThunk(
	'contentManager/getBaseSettings',
	async (_, thunkAPI) => {
		return await API.get(`${namespace}settings`)
			.then((res) => {
				return res.data;
			})
			.catch((error) => {
				handleSliceError(thunkAPI, error);
			});
	}
);

export const updateBaseSettings = createAsyncThunk(
	'contentManager/updateBaseSettings',
	async (formData, thunkAPI) => {
		const { isShowNotification, ...rest } = formData;
		return await makeRequest('update_base_settings', rest)
			.then((res) => {
				if (!isShowNotification) return rest;
				thunkAPI.dispatch(
					showNotification({
						message: __(
							'Settings updated successfully',
							'easy-content-manager'
						),
						isShow: true,
						type: 'success',
					})
				);
				return rest;
			})
			.catch((error) => {
				handleSliceError(thunkAPI, error);
			});
	}
);

const settingsSlice = createSlice({
	name: 'settings',
	initialState: {
		settings: {},
		baseSettings: {},
		generatedSettings: {},
		loading: false,
	},
	reducers: {},
	extraReducers: (builder) => {
		builder
			.addCase(importDefaultPosts.pending, (state) => {
				state.loading = true;
			})
			.addCase(importDefaultPosts.fulfilled, (state, action) => {
				state.loading = false;
				state.data = action.payload;
			})
			.addCase(importDefaultPosts.rejected, (state) => {
				state.loading = false;
			})

			.addCase(getSettings.fulfilled, (state, { payload }) => {
				state.settings = payload;
			})

			.addCase(createSettings.fulfilled, (state, { payload }) => {
				state.settings.items = [...state.settings.items, payload];
			})

			.addCase(updateSettings.fulfilled, (state, { payload }) => {
				state.settings.items = state.settings.items.map((item) => {
					if (Number(item.id) === Number(payload.id)) {
						return payload;
					}
					return item;
				});
			})

			.addCase(deleteSettings.fulfilled, (state, { payload }) => {
				state.settings.items = state.settings.items.filter(
					(item) => Number(item.id) !== Number(payload.id)
				);
			})

			.addCase(saveSettings.fulfilled, (state, { payload }) => {
				if (!payload?.id) return;

				if (!state.generatedSettings[payload.id]) {
					state.generatedSettings[payload.id] = [
						{ route: payload.route, meta: payload.data },
					];
					return;
				}

				const existingItem = state.generatedSettings[payload.id].find(
					(item) => item?.route === payload?.route
				);

				if (!existingItem) {
					state.generatedSettings[payload.id] = [
						...state.generatedSettings[payload.id],
						{ route: payload.route, meta: payload.data },
					];
					return;
				}

				state.generatedSettings[payload.id] = state.generatedSettings[
					payload.id
				].map((item) => {
					if (item?.route === payload?.route) {
						return { 
							...item, 
							meta: { 
								...item.meta, 
								...payload.data 
							} 
						};
					}
					return item;
				});
			})

			.addCase(getSavedSettings.fulfilled, (state, { payload }) => {
				if (!payload?.id) return;

				if (!state.generatedSettings[payload.id]) {
					state.generatedSettings[payload.id] = [
						{ route: payload.route, meta: payload.data },
					];
					return;
				}

				const existingItem = state.generatedSettings[payload.id].find(
					(item) => item?.route === payload?.route
				);

				if (!existingItem) {
					state.generatedSettings[payload.id] = [
						...state.generatedSettings[payload.id],
						{ route: payload.route, meta: payload.data },
					];
					return;
				}

				state.generatedSettings[payload.id] = state.generatedSettings[
					payload.id
				].map((item) => {
					if (item?.route === payload?.route) {
						return { 
							...item, 
							meta: { 
								...item.meta, 
								...payload.data 
							} 
						};
					}
					return item;
				});
			})

			.addCase(getBaseSettings.fulfilled, (state, { payload }) => {
				state.baseSettings = payload;
			})

			.addCase(updateBaseSettings.fulfilled, (state, { payload }) => {
				state.baseSettings = payload;
			});
	},
});

export default settingsSlice.reducer;
