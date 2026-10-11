import { Formik } from 'formik';
import { SequentialFieldRenderer } from '@ECMComponents/RenderField/helper';
import React from 'react';
import AutoSave from '@ECMComponents/Formik/AutoSave';
import { getCustomData } from '@ECMUtils/helper';
import { ChakraProvider } from '@chakra-ui/react';
import { theme } from '../../assets/scss/chakra/theme';

import './styles.scss';

const ReactEditor = ({
	values,
	setFieldValue: externalFieldHandler,
	post_type,
}) => {
	const customFields = getCustomData(post_type);

	return (
		<ChakraProvider value={theme}>
			<Formik
				enableReinitialize={true}
				initialValues={values}
				onSubmit={(values) => {
					Object.entries(values.meta).map(([key, value]) => {
						externalFieldHandler(`meta.${key}`, value);
					});
				}}
			>
				{({ values, setFieldValue }) => {
					return (
						<div className="easy-content-manager-custom-filed-render">
							<AutoSave />
							<SequentialFieldRenderer
								values={values}
								setFieldValue={setFieldValue}
								customData={customFields}
							/>
						</div>
					);
				}}
			</Formik>
		</ChakraProvider>
	);
};

export default ReactEditor;
