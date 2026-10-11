import { __ } from '@wordpress/i18n';
import { components } from 'react-select';
import { Flex } from '@chakra-ui/react';

export const getInitialValues = (ID = null, data = false) => {
	if (ID && data) {
		const foundData = data.find(
			(item) => parseInt(item.id) === parseInt(ID)
		);

		if (foundData) {
			const { id, custom_fields, location, title, slug, date, status } =
				foundData;

			return {
				id,
				group_name: title,
				slug,
				field_groups: custom_fields,
				location,
				date,
				status,
				duplicate_field_names: false,
			};
		}
	}

	return {
		id: '',
		status: 'publish',
		group_name: '',
		slug: '',
		field_groups: [],
		date: '',
		location: [
			[
				{
					logic_type: 'and',
					selected_page: 'post',
					selected_type: 'post_type',
					variant: 'is-equal-to',
				},
			],
		],
		duplicate_field_names: false,
	};
};

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

export const handleUpdateFieldGroupLocalize = (data, oldSlug = '') => {
	const { custom_fields, location } = data;
	const selectedPages = (location || [])
		.flat()
		.filter((item) => item.variant === 'is-equal-to')
		.map((item) => item.selected_page);
	return {
		old_slug: oldSlug,
		slug: data.slug ?? data.post_slug,
		custom_fields,
		location: selectedPages,
	};
};
