import React from 'react';
import { __ } from '@wordpress/i18n';
import Checkbox from '@ECMComponents/Formik/Checkbox';
import { useFormikContext } from 'formik';
import { Flex } from '@chakra-ui/react';

import './styles.scss';

const URL = () => {
	const { setFieldValue } = useFormikContext();

	return (
		<Flex
			flexDirection="column"
			gap="6"
			padding="12px 12px"
			maxHeight="372px"
			minHeight="372px"
		>
			<h1 className="content-manager-title">
				{__(
					'This feature not available at this moment.',
					'easy-content-manager'
				)}
			</h1>
		</Flex>
	);
};

export default URL;
