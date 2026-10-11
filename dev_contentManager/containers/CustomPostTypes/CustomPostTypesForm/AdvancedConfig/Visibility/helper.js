import { __ } from '@wordpress/i18n';

export const getVisibilityFieldList = [
	{
		name: 'advanced_config_data.admin_menu_parent',
		type: 'text',
		title: __('Admin Menu Parent', 'easy-content-manager'),
		placeholder: __(
			'edit.php?post_type={parent_page}',
			'easy-content-manager'
		),
		subtitle: __(
			'By default the post type will get a new top level item in the admin menu. If an existing top level item is supplied here, the post type will be added as a submenu item under it.',
			'easy-content-manager'
		),
	},
	{
		name: 'advanced_config_data.menu_position',
		type: 'number',
		title: __('Menu Position', 'easy-content-manager'),
		placeholder: __('menu position', 'easy-content-manager'),
		subtitle: __(
			'The position in the sidebar menu in the admin dashboard.',
			'easy-content-manager'
		),
	},
	{
		name: 'advanced_config_data.register_meta_box_cb',
		type: 'text',
		title: __('Custom Meta Box Callback', 'easy-content-manager'),
		placeholder: __('', 'easy-content-manager'),
		subtitle: __(
			'A PHP function name to be called when setting up the meta boxes for the edit screen. For security, this callback will be executed in a special context without access to any superglobals like $_POST or $_GET.',
			'easy-content-manager'
		),
	},
];

export const layoutOptions = [
	{
		label: __('Classic', 'easy-content-manager'),
		value: 'classic',
	},
	{
		label: __('Modern', 'easy-content-manager'),
		value: 'modern',
	},
];
