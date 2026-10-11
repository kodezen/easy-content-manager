import { createSlice } from '@reduxjs/toolkit';

const {
	post_types = {},
	field_groups = [],
	taxonomies = {},
	color_theme_mode = '',
} = window.EasyContentManagerGlobal;

const localizeSlice = createSlice({
	name: 'localized_data',
	initialState: {
		post_types,
		field_groups,
		taxonomies,
		color_theme_mode,
		all_fields: {},
	},
	reducers: {
		updateFieldGroupLocalize: (state, action) => {
			state.field_groups = state.field_groups.map((item) => {
				if (
					item.post_slug ===
					(action.payload.old_slug || action.payload.slug)
				) {
					return {
						custom_fields: action.payload.custom_fields,
						location: action.payload.location,
						post_slug: action.payload.slug,
					};
				}
				return item;
			});
		},

		createFieldGroupLocalize: (state, action) => {
			state.field_groups = [...state.field_groups, action.payload];
		},

		deleteFieldGroupLocalize: (state, action) => {
			state.field_groups = state.field_groups.filter(
				(item) => item.post_slug !== action.payload
			);
		},

		updatePostTypeLocalize: (state, action) => {
			const {
				postTypeSlug,
				custom_fields,
				rest_base,
				rest_namespace,
				taxonomy,
				title,
				supports,
			} = action.payload;
			state.post_types[postTypeSlug] = {
				custom_fields,
				rest_base,
				rest_namespace,
				taxonomy,
				title,
				supports,
				slug: postTypeSlug,
			};
		},

		createPostTypeLocalize: (state, action) => {
			const {
				postTypeSlug,
				custom_fields,
				rest_base,
				rest_namespace,
				taxonomy,
				title,
				supports,
			} = action.payload;

			const insertData = {
				custom_fields,
				rest_base,
				rest_namespace,
				taxonomy,
				title,
				supports,
				slug: postTypeSlug,
			};

			if (Array.isArray(state.post_types)) {
				state.post_types = {};
			}

			state.post_types[postTypeSlug] = insertData;
		},

		deletePostTypeLocalize: (state, action) => {
			const postTypeSlug = action.payload;
			state.post_types = Object.fromEntries(
				Object.entries(state.post_types).filter(
					([key]) => key !== postTypeSlug
				)
			);
		},

		updateTaxonomyLocalize: (state, action) => {
			const {
				taxonomySlug,
				custom_fields,
				title,
				rest_base,
				rest_namespace,
			} = action.payload;

			if (!state?.taxonomies || Array.isArray(state.taxonomies)) {
				state.taxonomies = {};
			}
			const insertData = {
				custom_fields,
				title,
				slug: taxonomySlug,
				rest_base,
				rest_namespace,
			};

			state.taxonomies[taxonomySlug] = insertData;
		},

		deleteTaxonomyLocalize: (state, action) => {
			const taxonomySlug = action.payload;
			state.taxonomies = Object.fromEntries(
				Object.entries(state.taxonomies).filter(
					([key]) => key !== taxonomySlug
				)
			);
		},

		updateColorLocalize: (state, action) => {
			state.color_theme_mode = action.payload;
		},

		updateAllFieldsLocalize: (state, action) => {
			state.all_fields = action.payload;
		},
	},
});

export const {
	updateFieldGroupLocalize,
	createFieldGroupLocalize,
	deleteFieldGroupLocalize,
	updatePostTypeLocalize,
	createPostTypeLocalize,
	deletePostTypeLocalize,
	updateColorLocalize,
	updateAllFieldsLocalize,
	updateTaxonomyLocalize,
	deleteTaxonomyLocalize,
} = localizeSlice.actions;

export default localizeSlice.reducer;
