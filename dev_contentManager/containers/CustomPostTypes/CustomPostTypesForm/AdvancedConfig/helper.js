import { is_pro } from '@ECMUtils/helper';
import { __ } from '@wordpress/i18n';

export const settingsTabs = (isAddonActive) => {
	return [
		{
			id: 'a1',
			name: 'general',
			icon: 'general-options',
			title: __('General', 'easy-content-manager'),
		},
		{
			id: 'b2',
			name: 'labels',
			icon: 'labels-generation',
			title: __('Labels', 'easy-content-manager'),
		},
		{
			id: 'c3',
			name: 'visibility',
			icon: 'visibility-generation',
			title: __('Visibility', 'easy-content-manager'),
		},
		{
			id: 'c4',
			name: 'permissions',
			icon: 'permissions-generation',
			title: __('Permissions', 'easy-content-manager'),
		},
	];
};

