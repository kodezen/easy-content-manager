import { showNotification } from '@ECMRedux/Slices/notificationSlice/notificationSlice';
import { copyToClipboard, is_pro } from '@ECMUtils/helper';
import { __ } from '@wordpress/i18n';

export const tabsArray = [
    {
        label: __('Export/Import', 'easy-content-manager'),
        icon: 'easy-content-manager-icon easy-content-manager-icon--import',
        subtitle: __('Export/Import your data', 'easy-content-manager'),
        value: 'export-import',
        isDisabled: false,
        tag: false,
        submenu: false,
    },
    ...(is_pro ? [{
        label: __('Shortcodes', 'easy-content-manager'),
        icon: 'easy-content-manager-icon easy-content-manager-icon--shortcode',
        subtitle: __('Manage and copy functional shortcodes', 'easy-content-manager'),
        value: 'shortcodes',
        isDisabled: false,
        tag: false,
        submenu: false,
    }] : []),
];

export const isParentActive = (currentPath, parent) =>
    currentPath === parent || currentPath?.startsWith(`${parent}/`);

export const getSubPath = (parent, child) => `${parent}/${child}`;