import { createAsyncThunk, createSlice } from '@reduxjs/toolkit';
import { menu, makeRequest, API, IsJsonString } from '@ECMUtils/helper';

export const fetchMenuOptions = createAsyncThunk(
	'contentManager/fetchMenu',
	async () => {
		const res = await API.get('content-manager/v1/post_types');
		return res.data;
	}
);

export const fetchUpdatedMenu = createAsyncThunk(
	'contentManager/fetchUpdatedMenu',
	async () => {
		const res = await makeRequest('get_updated_menu');
		return res.data.data;
	}
);

const menuSlice = createSlice({
	name: 'menus',
	initialState: {
		data: IsJsonString(menu) && JSON.parse(menu) || {},
		loading: false,
	},
	reducers: {
		deleteMenu: (state, action) => {
			const postSlug = action.payload.slug;
			const deletedMenu = Object.entries(state.data).reduce(
				(acc, [key, value]) => {
					if (key !== `easy-content-manager-${postSlug}`) {
						acc[key] = { ...value };
					}
					return acc;
				},
				{}
			);
			state.data = deletedMenu;
		},
		duplicateMenu: (state, action) => {
			const { title, slug } = action.payload;

			const menuData = {
				[`easy-content-manager-${slug}`]: {
					capability: 'manage_options',
					parent_slug: 'easy-content-manager',
					slug,
					title,
				},
			};

			const updatedState = { ...state.data, ...menuData };

			const reorderedState = {};

			const keys = Object.keys(updatedState);
			reorderedState[keys[0]] = updatedState[keys[0]];

			reorderedState[Object.keys(menuData)[0]] =
				menuData[Object.keys(menuData)[0]];

			keys.slice(1).forEach((key) => {
				if (key !== Object.keys(menuData)[0]) {
					reorderedState[key] = updatedState[key];
				}
			});

			state.data = reorderedState;
		},
	},
	extraReducers: (builder) => {
		builder
			.addCase(fetchMenuOptions.pending, (state) => {
				state.loading = true;
			})
			.addCase(fetchMenuOptions.fulfilled, (state, action) => {
				state.loading = false;
				state.data = action.payload;
			})
			.addCase(fetchMenuOptions.rejected, (state) => {
				state.loading = false;
			})
			//fetch updated menu
			.addCase(fetchUpdatedMenu.pending, (state) => {
				state.loading = true;
			})
			.addCase(fetchUpdatedMenu.fulfilled, (state, action) => {
				state.loading = false;
				state.data = action.payload;
			})
			.addCase(fetchUpdatedMenu.rejected, (state) => {
				state.loading = false;
			});
	},
});

export const { deleteMenu, duplicateMenu } = menuSlice.actions;

export default menuSlice.reducer;
