import { is_pro } from "./helper";
import { __ } from "@wordpress/i18n";

export const customFieldSelectOptions = [
    {
        label: __('Basic', 'easy-content-manager'),
        options: [
            {
                label: __('Text', 'easy-content-manager'),
                value: 'text-field',
                icon: (
                    <span className="easy-content-manager-icon easy-content-manager-icon--text" />
                ),
            },
            {
                label: __('Textarea', 'easy-content-manager'),
                value: 'textarea-field',
                icon: (
                    <span className="easy-content-manager-icon easy-content-manager-icon--textarea" />
                ),
            },
            {
                label: __('Range', 'easy-content-manager'),
                value: 'range-field',
                icon: (
                    <span className="easy-content-manager-icon easy-content-manager-icon--range" />
                ),
            },
            {
                label: __('Number', 'easy-content-manager'),
                value: 'number-field',
                icon: (
                    <span className="easy-content-manager-icon easy-content-manager-icon--hash" />
                ),
            },
            {
                label: __('Email', 'easy-content-manager'),
                value: 'email-field',
                icon: (
                    <span className="easy-content-manager-icon easy-content-manager-icon--uniE918" />
                ),
            },
            {
                label: __('Url', 'easy-content-manager'),
                value: 'url-field',
                icon: (
                    <span className="easy-content-manager-icon easy-content-manager-icon--url" />
                ),
            },
            {
                label: __('Password', 'easy-content-manager'),
                value: 'password-field',
                icon: (
                    <span className="easy-content-manager-icon easy-content-manager-icon--key" />
                ),
            },
        ],
    },
    {
        label: __('Content', 'easy-content-manager'),
        options: [
            {
                label: __('Rich Text', 'easy-content-manager'),
                value: 'rich-text-field',
                icon: (
                    <span className="easy-content-manager-icon easy-content-manager-icon--text" />
                ),
            },
            {
                label: __('File', 'easy-content-manager'),
                value: 'file-field',
                icon: (
                    <span className="easy-content-manager-icon easy-content-manager-icon--file" />
                ),
            },
            {
                label: __('Single Image', 'easy-content-manager'),
                value: 'image-field',
                icon: (
                    <span className="easy-content-manager-icon easy-content-manager-icon--image" />
                ),
            },
            {
                label: __(
                    `Gallery ${!is_pro ? '(Pro Only)' : ''}`,
                    'easy-content-manager'
                ),
                value: 'gallery-field',
                disabled: is_pro === '1' ? false : true,
                icon: (
                    <span className="easy-content-manager-icon easy-content-manager-icon--gallery" />
                ),
            },
            {
                label: __('Video', 'easy-content-manager'),
                value: 'video-field',
                icon: (
                    <span className="easy-content-manager-icon easy-content-manager-icon--video" />
                ),
            },
            {
                label: __('oEmbed', 'easy-content-manager'),
                value: 'oembed-field',
                icon: (
                    <span className="easy-content-manager-icon easy-content-manager-icon--oEmbed" />
                ),
            },
        ],
    },
    {
        label: __('Relational', 'easy-content-manager'),
        options: [
            {
                label: __('Link', 'easy-content-manager'),
                value: 'link-field',
                icon: (
                    <span className="easy-content-manager-icon easy-content-manager-icon--link" />
                ),
            },
            {
                label: __('Post Object', 'easy-content-manager'),
                value: 'post-object-field',
                icon: (
                    <span className="easy-content-manager-icon easy-content-manager-icon--post-object" />
                ),
            },
            {
                label: __('Page Link', 'easy-content-manager'),
                value: 'page-link-field',
                icon: (
                    <span className="easy-content-manager-icon easy-content-manager-icon--page-link" />
                ),
            },
            // {
            // 	label: __('Relationship', 'easy-content-manager'),
            // 	value: 'relationship-field',
            // 	icon: (
            // 		<span className="easy-content-manager-icon easy-content-manager-icon--Relationship" />
            // 	),
            // },
            {
                label: __('Taxonomy', 'easy-content-manager'),
                value: 'taxonomy-field',
                icon: (
                    <span className="easy-content-manager-icon easy-content-manager-icon--taxonomy" />
                ),
            },
            {
                label: __('User', 'easy-content-manager'),
                value: 'user-field',
                icon: (
                    <span className="easy-content-manager-icon easy-content-manager-icon--user" />
                ),
            },
        ],
    },
    {
        label: __('Choice', 'easy-content-manager'),
        options: [
            {
                label: __('Select', 'easy-content-manager'),
                value: 'select-field',
                icon: (
                    <span className="easy-content-manager-icon easy-content-manager-icon--select" />
                ),
            },
            {
                label: __('Button Group', 'easy-content-manager'),
                value: 'button-group-field',
                icon: (
                    <span className="easy-content-manager-icon easy-content-manager-icon--button-group" />
                ),
            },
            {
                label: __('Switch', 'easy-content-manager'),
                value: 'switch-field',
                icon: (
                    <span className="easy-content-manager-icon easy-content-manager-icon--switch" />
                ),
            },
            {
                label: __('Checkbox', 'easy-content-manager'),
                value: 'checkbox-field',
                icon: (
                    <span className="easy-content-manager-icon easy-content-manager-icon--check-box" />
                ),
            },
            {
                label: __('Radio Button', 'easy-content-manager'),
                value: 'radio-field',
                icon: (
                    <span className="easy-content-manager-icon easy-content-manager-icon--radio-button" />
                ),
            },
            {
                label: __('True/False', 'easy-content-manager'),
                value: 'true-false-field',
                icon: (
                    <span className="easy-content-manager-icon easy-content-manager-icon--true-false" />
                ),
            },
        ],
    },
    {
        label: __('Advanced', 'easy-content-manager'),
        options: [
            {
                label: __('Time Picker', 'easy-content-manager'),
                value: 'time-field',
                icon: (
                    <span className="easy-content-manager-icon easy-content-manager-icon--time-picker" />
                ),
            },
            {
                label: __('Date Picker', 'easy-content-manager'),
                value: 'date-field',
                icon: (
                    <span className="easy-content-manager-icon easy-content-manager-icon--date-time-picker" />
                ),
            },
            {
                label: __('Date Time Picker', 'easy-content-manager'),
                value: 'date-time-field',
                icon: (
                    <span className="easy-content-manager-icon easy-content-manager-icon--date-time-picker" />
                ),
            },
            {
                label: __('Color Picker', 'easy-content-manager'),
                value: 'color-field',
                icon: (
                    <span className="easy-content-manager-icon easy-content-manager-icon--color-picker" />
                ),
            },
            {
                label: __('Icon Picker', 'easy-content-manager'),
                value: 'icon-field',
                icon: (
                    <span className="easy-content-manager-icon easy-content-manager-icon--icon-picker" />
                ),
            },
            {
                label: __('Map', 'easy-content-manager'),
                value: 'map-field',
                icon: (
                    <span className="easy-content-manager-icon easy-content-manager-icon--map" />
                ),
            },
        ],
    },
    {
        label: __('Layout', 'easy-content-manager'),
        options: [
            {
                label: __('Group', 'easy-content-manager'),
                value: 'group-field',
                icon: (
                    <span className="easy-content-manager-icon easy-content-manager-icon--group" />
                ),
            },
            {
                label: __('Message', 'easy-content-manager'),
                value: 'message-field',
                icon: (
                    <span className="easy-content-manager-icon easy-content-manager-icon--message" />
                ),
            },
            {
                label: __('Accordion', 'easy-content-manager'),
                value: 'accordion-field',
                icon: (
                    <span className="easy-content-manager-icon easy-content-manager-icon--accordion" />
                ),
            },
            {
                label: __('Tab', 'easy-content-manager'),
                value: 'tab-field',
                icon: (
                    <span className="easy-content-manager-icon easy-content-manager-icon--Tab" />
                ),
            },
            {
                label: __(
                    `Repeater ${!is_pro ? '(Pro Only)' : ''}`,
                    'easy-content-manager'
                ),
                value: 'repeater-field',
                disabled: is_pro === '1' ? false : true,
                icon: (
                    <span className="easy-content-manager-icon easy-content-manager-icon--repeater" />
                ),
            },
        ],
    },
]

const baseFields = [
    'text-field',
    'number-field',
    'email-field',
    'rich-text-field',
    'url-field',
    'date-field',
    'file-field',
    'image-field',
    'video-field',
    'time-field',
    'switch-field',
    'password-field',
    'textarea-field',
    'button-group-field',
    'checkbox-field',
    'color-field',
    'radio-field',
    'icon-field',
    'true-false-field',
    'group-field',
    'message-field',
    'map-field',
    'link-field',
    'oembed-field',
    'range-field',
    'select-field',
    'tab-field',
    'accordion-field',
    'page-link-field',
    'post-object-field',
    'relationship-field',
    'taxonomy-field',
    'date-time-field',
    'user-field',
];

const proFields = is_pro ? ['gallery-field', 'repeater-field'] : [];

export const all_field_types = [...baseFields, ...proFields];

export const convertFieldTypeToKey = (fieldType) => {
	if (!fieldType) return null;
	const mapping = {
		'text-field': 'text',
		'number-field': 'number',
		'email-field': 'email',
		'rich-text-field': 'rich-text',
		'url-field': 'url',
		'date-field': 'date',
		'file-field': 'file',
		'image-field': 'image',
		'gallery-field': 'gallery',
		'video-field': 'video',
		'time-field': 'time',
		'switch-field': 'switch',
		'password-field': 'password',
		'textarea-field': 'textarea',
		'button-group-field': 'button-group',
		'checkbox-field': 'checkbox',
		'color-field': 'color',
		'radio-field': 'radio',
		'icon-field': 'icon',
		'true-false-field': 'true-false',
		'group-field': 'group',
		'message-field': 'message',
		'map-field': 'map',
		'link-field': 'link',
		'oembed-field': 'oembed',
		'range-field': 'range',
		'select-field': 'select',
		'tab-field': 'tab',
		'accordion-field': 'accordion',
		'page-link-field': 'page-link',
		'post-object-field': 'post-object',
		'relationship-field': 'relationship',
		'taxonomy-field': 'taxonomy',
		'repeater-field': 'repeater',
		'date-time-field': 'date-time',
		'user-field': 'user',
	};
	return mapping[fieldType] || null;
};

export const getMetaFieldName = (name) => {
	const metaFieldName = name.split('_')[0];
	const fieldName = convertFieldTypeToKey(metaFieldName);
	return fieldName;
};

export const getMetaKey = (fieldPath) => `${fieldPath.join('.')}.meta_key`;

export const getFieldGroupMetaKey = (index) =>
	`field_groups[${index}].meta_key`;

export const checkDuplicateFieldName = (fields) => {
	const fieldNames = fields.map((field) => field?.general?.field_name);
	const isDuplicate = fieldNames.some(
		(name, index) => fieldNames.indexOf(name) !== index
	);
	const duplicatedFieldNames = fieldNames.filter(
		(name, index) => fieldNames.indexOf(name) !== index
	);
	return { isDuplicate, duplicatedFieldNames };
};

export const generateCustomFieldName = (value) => {
    return value
        .toLowerCase()
        .replace(/\s+/g, '_')
        .replace(/[^a-z0-9-]/g, '_');
};