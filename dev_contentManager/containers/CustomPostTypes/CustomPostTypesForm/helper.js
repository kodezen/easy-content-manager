import { __ } from '@wordpress/i18n';
import {
	capitalizeFirstLetter,
	generateSlug,
	makeRequest,
	route_path,
} from '@ECMUtils/helper';
import { showNotification } from '@ECMRedux/Slices/notificationSlice/notificationSlice';
import { createFieldGroup } from '@ECMRedux/Slices/fieldGroupsSlice/fieldGroupsSlice';
import { components } from 'react-select';
import { Flex } from '@chakra-ui/react';

export const getPostInitialValues = (id = null, posts = false) => {
	if (id && posts) {
		const postData = posts.find(
			(item) => parseInt(item.ID) === parseInt(id)
		);

		if (postData) {
			const { singular_name, plural_name } = postData.post_content.labels;

			const {
				post_slug,
				hierarchical,
				custom_fields,
				labels,
				description,
				supports,
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
			} = postData.post_content;

			const advanced_config_data = {
				active,
				post_slug,
				labels,
				description,
				supports,
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
			};

			return {
				post_id: id,
				singular_name,
				plural_name,
				post_slug,
				is_public,
				hierarchical,
				custom_fields,
				advanced_configuration: true,
				advanced_config_data,
				duplicate_field_names: false,
			};
		}
	}
	return {
		singular_name: '',
		plural_name: '',
		post_slug: '',
		is_public: true,
		hierarchical: false,
		custom_fields: [],
		advanced_configuration: true,
		advanced_config_data: {
			taxonomies: [],
			supports: ['title', 'editor', 'thumbnail', 'custom-fields'],
			appearance_menus_support: true,
			active: true,
			show_ui: true,
			show_in_menu: true,
			show_in_admin_bar: true,
			can_export: true,
			rename_capabilities: false,
			can_export: true,
			delete_with_user: false,
		},
		duplicate_field_names: false,
	};
};

export const handleSaveAsFieldGroup = (values, navigate, dispatch) => {
	const singularName = values?.singular_name;
	const slug = values?.post_slug;
	const field_groups = values?.custom_fields;

	if (!field_groups.length) {
		dispatch(
			showNotification({
				message: __('Add a field first', 'easy-content-manager'),
				isShow: true,
				type: 'error',
			})
		);
		return;
	}
	if (!values?.post_id) {
		dispatch(
			showNotification({
				message: __('First save the post type', 'easy-content-manager'),
				isShow: true,
				type: 'error',
			})
		);
		return;
	}

	const location = [
		[
			{
				logic_type: 'and',
				selected_page: 'post',
				selected_type: 'post',
				variant: '',
			},
		],
	];

	const data = {
		group_name: singularName,
		location,
		field_groups,
		slug,
	};
	dispatch(createFieldGroup(data)).then(({ payload: data }) => {
		if (data?.id) {
			const url = `${route_path}admin.php?page=easy-content-manager-field-groups&action=edit&id=${data.id}`;
			navigate(url);
		}
	});
};

export function generateUniqueId() {
	const randomNum1 = Math.floor(Math.random() * 10);
	const randomNum2 = Math.floor(Math.random() * 10);
	const randomNum3 = Math.floor(Math.random() * 10);

	return `${randomNum1}${randomNum2}${randomNum3}`;
}

export const CustomOption = (props) => (
	<components.Option {...props}>
		<Flex alignItems="center" gap="8px">
			{props.data.icon}
			<span>{props.data.label}</span>
		</Flex>
	</components.Option>
);

export const CustomSingleValue = (props) => (
	<components.SingleValue {...props}>
		<Flex alignItems="center" gap="8px">
			{props.data.icon}
			<span>{props.data.label}</span>
		</Flex>
	</components.SingleValue>
);

export const optionsPlaceholder = (props) => (
	<components.Placeholder {...props}>
		<Flex alignItems="center" gap="8px">
			<span className="easy-content-manager-icon easy-content-manager-icon--text" />
			<span>{props.children}</span>
		</Flex>
	</components.Placeholder>
);

export const presetButtonData = [
	{
		label: __('Portfolio', 'easy-content-manager'),
		value: 'portfolio',
	},
	{
		label: __('Projects', 'easy-content-manager'),
		value: 'projects',
	},
	{
		label: __('Team Members', 'easy-content-manager'),
		value: 'team-members',
	},
	{
		label: __('Recipes', 'easy-content-manager'),
		value: 'recipes',
	},
	{
		label: __('Jobs', 'easy-content-manager'),
		value: 'jobs',
	},
	{
		label: __('Clients', 'easy-content-manager'),
		value: 'clients',
	},
	{
		label: __('Listings', 'easy-content-manager'),
		value: 'listings',
	},
];

const getPostFieldPresets = (params, preset_name, dispatch) => {
	return makeRequest(`get_post_fields_preset`, {
		post_custom_fields: params,
		preset_name,
	}).then((res) => {
		if (res.status === 200) {
			dispatch(
				showNotification({
					message: __(
						'Successfully preset added!',
						'easy-content-manager'
					),
					isShow: true,
					type: 'success',
				})
			);

			return res.data.data;
		}
		dispatch(
			showNotification({
				message: __('Failed to add preset!', 'easy-content-manager'),
				isShow: true,
				type: 'error',
			})
		);
	});
};

export const presetsHandler = (
	value,
	setFieldValue,
	dispatch,
	setActiveIndex,
	index
) => {
	setActiveIndex(index);

	const presetConfigs = {
		portfolio: [
			'text-field',
			'image-field',
			'date-field',
			'url-field',
			'url-field-1',
			'switch-field',
		],
		projects: [
			'text-field',
			'image-field',
			'image-field-1',
			'date-field',
			'url-field',
			'switch-field',
		],
		'team-members': [
			'email-field',
			'text-field',
			'image-field',
			'url-field',
			'url-field-1',
			'url-field-2',
		],
		recipes: [
			'rich-text-field',
			'image-field',
			'time-field',
			'image-field-1',
			'switch-field',
		],
		jobs: [
			'text-field',
			'number-field',
			'text-field-2',
			'rich-text-field-1',
			'number-field-1',
			'date-field',
		],
		clients: [
			'text-field',
			'email-field',
			'text-field-2',
			'date-field',
			'image-field',
			'image-field-1',
		],
		listings: [
			'select-field',
			'text-field',
			'number-field',
			'number-field-1',
			'text-field-2',
			'switch-field',
			'image-field',
			'date-field',
			'text-field-3',
			'text-field-4',
		],
	};

	if (!presetConfigs[value]) return;

	const label = capitalizeFirstLetter(value); // e.g., 'team-members' -> 'Team Members'
	const postSlug = generateSlug(label);

	setFieldValue('singular_name', label);
	setFieldValue('plural_name', label);
	setFieldValue('post_slug', postSlug);

	const fields = presetConfigs[value];

	getPostFieldPresets(fields, value, dispatch).then((res) => {
		const presetData = res.map((item, _) => ({
			...item,
			post_meta_args: {
				...item.post_meta_args,
			},
			meta_key: item?.general?.field_name,
		}));

		setFieldValue('custom_fields', presetData);
	});
};
