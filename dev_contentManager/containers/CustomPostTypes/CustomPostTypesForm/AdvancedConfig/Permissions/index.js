import React from 'react';
import { __ } from '@wordpress/i18n';
import Checkbox from '@ECMComponents/Formik/Checkbox';
import { Flex } from '@chakra-ui/react';

import './styles.scss';

const Permissions = ({ isError, setIsError }) => {
	return (
		<Flex
			flexDirection="column"
			gap="6"
			padding="12px 12px"
			maxHeight="372px"
			minHeight="372px"
		>
			<Checkbox
				title={__('Rename Capabilities', 'easy-content-manager')}
				subtitle={__(
					"By default the capabilities of the post type will inherit the 'Post' capability names, eg. edit_post, delete_posts. Enable to use post type specific capabilities, eg. edit_{singular}, delete_{plural}.",
					'easy-content-manager'
				)}
				name="advanced_config_data.rename_capabilities"
			/>

			<Checkbox
				title={__('Can Export', 'easy-content-manager')}
				subtitle={__(
					"Allow the post type to be exported from 'Tools' > 'Export'.",
					'easy-content-manager'
				)}
				name="advanced_config_data.can_export"
			/>

			<Checkbox
				title={__('Delete With user', 'easy-content-manager')}
				subtitle={__(
					'Delete items by a user when that user is deleted.',
					'easy-content-manager'
				)}
				name="advanced_config_data.delete_with_user"
			/>
		</Flex>
	);
};

export default Permissions;
