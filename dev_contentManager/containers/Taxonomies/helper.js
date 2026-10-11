export const getTaxonomiesInitialValues = (id = null, posts = false) => {
	if (id && posts) {
		const postData = posts.find(
			(item) => parseInt(item.ID) === parseInt(id)
		);

		if (postData) {
			const { singular_name, name } = postData.post_content.labels;

			const {
				taxonomy_slug,
				labels,
				description,
				custom_fields,
				show_ui,
				show_in_menu,
				show_in_admin_bar,
				menu_position,
				active,
				public: is_public,
				admin_menu_parent,
				exclude_from_search,
				appearance_menus_support,
				taxonomies,
				rename_capabilities,
				can_export,
				delete_with_user,
				ui_type,
				default_term,
				sort,
				object_type,
				is_default_term,
				show_admin_column,
				hierarchical,
			} = postData.post_content;

			const configuration = {
				active,
				taxonomy_slug,
				labels,
				description,
				show_ui,
				show_in_menu,
				show_in_admin_bar,
				menu_position,
				admin_menu_parent,
				exclude_from_search,
				appearance_menus_support,
				taxonomies,
				can_export,
				rename_capabilities,
				delete_with_user,
				ui_type,
				default_term,
				is_default_term,
				sort,
				show_admin_column,
			};

			return {
				post_id: id,
				singular_name,
				plural_name: name,
				taxonomy_slug,
				custom_fields,
				is_public,
				object_type,
				advanced_configuration: true,
				hierarchical,
				configuration,
			};
		}
	}
	return {
		singular_name: '',
		plural_name: '',
		custom_fields: [],
		taxonomy_slug: '',
		object_type: [],
		is_public: true,
		hierarchical: true,
		advanced_configuration: true,
		configuration: {
			taxonomies: [],
			appearance_menus_support: true,
			active: true,
			show_ui: true,
			show_in_menu: true,
			show_in_admin_bar: true,
			can_export: true,
			rename_capabilities: false,
			can_export: true,
			delete_with_user: false,
			default_term: {},
			is_default_term: false,
			sort: false,
			show_admin_column: false,
			tag_cloud: false,
			quick_edit: false,
		},
	};
};
