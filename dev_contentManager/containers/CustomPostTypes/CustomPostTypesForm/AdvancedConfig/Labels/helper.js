import { __ } from '@wordpress/i18n';

export const getLabelsFieldData = [
	{
		id: 'label_item_1',
		name: 'advanced_config_data.labels.menu_name',
		label: __('Menu Name', 'easy-content-manager'),
		placeholder: __('Posts', 'easy-content-manager'),
		subtitle: __(
			'Admin menu name for the post type.',
			'easy-content-manager'
		),
	},
	{
		id: 'label_item_2',
		name: 'advanced_config_data.labels.all_items',
		label: __('All Items', 'easy-content-manager'),
		placeholder: __('All Posts', 'easy-content-manager'),
		subtitle: __(
			'In the post type submenu in the admin dashboard.',
			'easy-content-manager'
		),
	},
	{
		id: 'label_item_3',
		name: 'advanced_config_data.labels.edit_item',
		label: __('Edit Item', 'easy-content-manager'),
		placeholder: __('Edit Post', 'easy-content-manager'),
		subtitle: __(
			'At the top of the editor screen when editing an item.',
			'easy-content-manager'
		),
	},
	{
		id: 'label_item_4',
		name: 'advanced_config_data.labels.view_item',
		label: __('View Item', 'easy-content-manager'),
		placeholder: __('View Post', 'easy-content-manager'),
		subtitle: __(
			'In the admin bar to view item when editing it.',
			'easy-content-manager'
		),
	},
	{
		id: 'label_item_5',
		name: 'advanced_config_data.labels.view_items',
		label: __('View Items', 'easy-content-manager'),
		placeholder: __('View Posts', 'easy-content-manager'),
		subtitle: __(
			"Appears in the admin bar in the 'All Posts' view, provided the post type supports archives and the home page is not an archive of that post type.",
			'easy-content-manager'
		),
	},
	{
		id: 'label_item_6',
		name: 'advanced_config_data.labels.add_new_item',
		label: __('Add New Item', 'easy-content-manager'),
		placeholder: __('Add New Post', 'easy-content-manager'),
		subtitle: __(
			'At the top of the editor screen when adding a new item.',
			'easy-content-manager'
		),
	},
	{
		id: 'label_item_7',
		name: 'advanced_config_data.labels.add_new',
		label: __('Add New', 'easy-content-manager'),
		placeholder: __('Add New Post', 'easy-content-manager'),
		subtitle: __(
			'In the post type submenu in the admin dashboard.',
			'easy-content-manager'
		),
	},
	{
		id: 'label_item_8',
		name: 'advanced_config_data.labels.new_item',
		label: __('New Item', 'easy-content-manager'),
		placeholder: __('New Post', 'easy-content-manager'),
		subtitle: __(
			'In the post type submenu in the admin dashboard.',
			'easy-content-manager'
		),
	},
	{
		id: 'label_item_9',
		name: 'advanced_config_data.labels.parent_item_prefix',
		label: __('Parent Item Prefix', 'easy-content-manager'),
		placeholder: __('Parent Page:', 'easy-content-manager'),
		subtitle: __(
			'For hierarchical types in the post type list screen.',
			'easy-content-manager'
		),
	},
	{
		id: 'label_item_10',
		name: 'advanced_config_data.labels.search_items',
		label: __('Search Items', 'easy-content-manager'),
		placeholder: __('Search Posts', 'easy-content-manager'),
		subtitle: __(
			'At the top of the items screen when searching for an item.',
			'easy-content-manager'
		),
	},
	{
		id: 'label_item_11',
		name: 'advanced_config_data.labels.no_items_found',
		label: __('No Items Found', 'easy-content-manager'),
		placeholder: __('No posts found', 'easy-content-manager'),
		subtitle: __(
			'At the top of the post type list screen when there are no posts to display.',
			'easy-content-manager'
		),
	},
	{
		id: 'label_item_12',
		name: 'advanced_config_data.labels.no_items_found_in_trash',
		label: __('No Items Found in Trash', 'easy-content-manager'),
		placeholder: __('No posts found in trash', 'easy-content-manager'),
		subtitle: __(
			'At the top of the post type list screen when there are no posts in the trash.',
			'easy-content-manager'
		),
	},
	{
		id: 'label_item_13',
		name: 'advanced_config_data.labels.archives_nav_menu',
		label: __('Archives Nav Menu', 'easy-content-manager'),
		placeholder: __('Post Archives', 'easy-content-manager'),
		subtitle: __(
			"Adds 'Post Type Archive' items with this label to the list of posts shown when adding items to an existing menu in a CPT with archives enabled. Only appears when editing menus in 'Live Preview' mode and a custom archive slug has been provided.",
			'easy-content-manager'
		),
	},
	{
		id: 'label_item_14',
		name: 'advanced_config_data.labels.attributes_meta_box',
		label: __('Attributes Meta Box', 'easy-content-manager'),
		placeholder: __('Post Attributes', 'easy-content-manager'),
		subtitle: __(
			'In the editor used for the title of the post attributes meta box.',
			'easy-content-manager'
		),
	},
	{
		id: 'label_item_15',
		name: 'advanced_config_data.labels.featured_image_meta_box',
		label: __('Featured Image Meta Box', 'easy-content-manager'),
		placeholder: __('Featured Image', 'easy-content-manager'),
		subtitle: __(
			'In the editor used for the title of the featured image meta box.',
			'easy-content-manager'
		),
	},
	{
		id: 'label_item_16',
		name: 'advanced_config_data.labels.set_featured_image',
		label: __('Set Featured Image', 'easy-content-manager'),
		placeholder: __('Set featured image', 'easy-content-manager'),
		subtitle: __('Used in the media frame button.', 'easy-content-manager'),
	},
	{
		id: 'label_item_17',
		name: 'advanced_config_data.labels.remove_featured_image',
		label: __('Remove Featured Image', 'easy-content-manager'),
		placeholder: __('Remove featured image', 'easy-content-manager'),
		subtitle: __('Used in the media frame button.', 'easy-content-manager'),
	},
	{
		id: 'label_item_18',
		name: 'advanced_config_data.labels.use_featured_image',
		label: __('Use Featured Image', 'easy-content-manager'),
		placeholder: __('Use as featured image', 'easy-content-manager'),
		subtitle: __('Used in the media frame menu.', 'easy-content-manager'),
	},
	{
		id: 'label_item_19',
		name: 'advanced_config_data.labels.items_list_navigation',
		label: __('Items List Navigation', 'easy-content-manager'),
		placeholder: __('Posts list navigation', 'easy-content-manager'),
		subtitle: __(
			'Used in screen reader text for the table pagination.',
			'easy-content-manager'
		),
	},
	{
		id: 'label_item_20',
		name: 'advanced_config_data.labels.items_list',
		label: __('Items List', 'easy-content-manager'),
		placeholder: __('Posts list', 'easy-content-manager'),
		subtitle: __(
			'Used in screen reader text for the items table.',
			'easy-content-manager'
		),
	},
	{
		id: 'label_item_21',
		name: 'advanced_config_data.labels.item_published',
		label: __('Item Published', 'easy-content-manager'),
		placeholder: __('Post published.', 'easy-content-manager'),
		subtitle: __('Shown after publishing an item.', 'easy-content-manager'),
	},
	{
		id: 'label_item_22',
		name: 'advanced_config_data.labels.item_published_privately',
		label: __('Item Published Privately', 'easy-content-manager'),
		placeholder: __('Post published privately.', 'easy-content-manager'),
		subtitle: __(
			'Shown after publishing an item privately.',
			'easy-content-manager'
		),
	},
	{
		id: 'label_item_23',
		name: 'advanced_config_data.labels.item_reverted_to_draft',
		label: __('Item Reverted to Draft', 'easy-content-manager'),
		placeholder: __('Post reverted to draft.', 'easy-content-manager'),
		subtitle: __(
			'Shown after reverting an item to draft.',
			'easy-content-manager'
		),
	},
	{
		id: 'label_item_24',
		name: 'advanced_config_data.labels.item_scheduled',
		label: __('Item Scheduled', 'easy-content-manager'),
		placeholder: __('Post scheduled.', 'easy-content-manager'),
		subtitle: __('Shown after scheduling an item.', 'easy-content-manager'),
	},
	{
		id: 'label_item_25',
		name: 'advanced_config_data.labels.item_updated',
		label: __('Item Updated', 'easy-content-manager'),
		placeholder: __('Post updated.', 'easy-content-manager'),
		subtitle: __('Shown after updating an item.', 'easy-content-manager'),
	},
	{
		id: 'label_item_26',
		name: 'advanced_config_data.labels.filter_items_list',
		label: __('Filter Items List', 'easy-content-manager'),
		placeholder: __('Filter posts list', 'easy-content-manager'),
		subtitle: __(
			'Used in screen reader text for the filter links.',
			'easy-content-manager'
		),
	},
	{
		id: 'label_item_27',
		name: 'advanced_config_data.labels.items_list_table',
		label: __('Items List Table', 'easy-content-manager'),
		placeholder: __('Posts list table', 'easy-content-manager'),
		subtitle: __(
			'Used in screen reader text for the items table.',
			'easy-content-manager'
		),
	},
	{
		id: 'label_item_28',
		name: 'advanced_config_data.labels.insert_into_item',
		label: __('Insert Into Item', 'easy-content-manager'),
		placeholder: __('Insert into post', 'easy-content-manager'),
		subtitle: __('Used in the media frame menu.', 'easy-content-manager'),
	},
	{
		id: 'label_item_29',
		name: 'advanced_config_data.labels.upload_to_this_item',
		label: __('Upload to This Item', 'easy-content-manager'),
		placeholder: __('Upload to this post', 'easy-content-manager'),
		subtitle: __('Used in the media frame menu.', 'easy-content-manager'),
	},
	{
		id: 'label_item_30',
		name: 'advanced_config_data.labels.item_link',
		label: __('Item Link', 'easy-content-manager'),
		placeholder: __('Post Link', 'easy-content-manager'),
		subtitle: __('Used for sharing links.', 'easy-content-manager'),
	},
	{
		id: 'label_item_31',
		name: 'advanced_config_data.labels.item_link_description',
		label: __('Item Link Description', 'easy-content-manager'),
		placeholder: __('A link to a post.', 'easy-content-manager'),
		subtitle: __('Used for describing links.', 'easy-content-manager'),
	},
	{
		id: 'label_item_32',
		name: 'advanced_config_data.labels.back_to_items',
		label: __('Back to Items', 'easy-content-manager'),
		placeholder: __('← Back to Posts', 'easy-content-manager'),
		subtitle: __('Used in the editor toolbar.', 'easy-content-manager'),
	},
];
