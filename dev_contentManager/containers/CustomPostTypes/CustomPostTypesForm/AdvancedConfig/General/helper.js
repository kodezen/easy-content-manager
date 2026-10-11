import { __ } from '@wordpress/i18n';

export const getSupportsData = () => {
	return [
		{
			id: 'supports-title',
			title: __('Title', 'easy-content-manager'),
			name: 'title',
			suffix: 'advancedConfig-checkbox',
		},
		{
			id: 'supports-author',
			title: __('Author', 'easy-content-manager'),
			name: 'author',
			suffix: 'advancedConfig-checkbox',
		},
		{
			id: 'supports-comments',
			title: __('Comments', 'easy-content-manager'),
			name: 'comments',
			suffix: 'advancedConfig-checkbox',
		},
		{
			id: 'supports-trackbacks',
			title: __('Trackbacks', 'easy-content-manager'),
			name: 'trackbacks',
			suffix: 'advancedConfig-checkbox',
		},
		{
			id: 'supports-editor',
			title: __('Editor', 'easy-content-manager'),
			name: 'editor',
			suffix: 'advancedConfig-checkbox',
		},
		{
			id: 'supports-excerpt',
			title: __('Excerpt', 'easy-content-manager'),
			name: 'excerpt',
			suffix: 'advancedConfig-checkbox',
		},
		{
			id: 'supports-revisions',
			title: __('Revisions', 'easy-content-manager'),
			name: 'revisions',
			suffix: 'advancedConfig-checkbox',
		},
		{
			id: 'supports-page-attributes',
			title: __('Page Attributes', 'easy-content-manager'),
			name: 'page-attributes',
			suffix: 'advancedConfig-checkbox',
		},
		{
			id: 'supports-featured-image',
			title: __('Featured Image', 'easy-content-manager'),
			name: 'thumbnail',
			suffix: 'advancedConfig-checkbox',
		},
		{
			id: 'supports-custom-fields',
			title: __('Custom Fields', 'easy-content-manager'),
			name: 'custom-fields',
			suffix: 'advancedConfig-checkbox',
		},
		{
			id: 'supports-post-formats',
			title: __('Post Formats', 'easy-content-manager'),
			name: 'post-formats',
			suffix: 'advancedConfig-checkbox',
		},
	];
};
