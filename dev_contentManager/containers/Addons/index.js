import React from 'react';
import { __ } from '@wordpress/i18n';
import { Formik } from 'formik';
import FormInner from './FormInner';
import { useSelector, useDispatch } from 'react-redux';
import { saveAddonStatus } from '@ECMRedux/Slices/addonSlice/addonSlice';
import { fetchUpdatedMenu } from '@ECMRedux/Slices/menuSlice/menuSlice';
import { getInitialValues } from './helper';
import TopBar from '@ECMComponents/TopBar';
import ECMLabel from '@ECMComponents/Labels/ECMLabel';
import { Box } from '@chakra-ui/react';

import './styles.scss';
import SubTopBar from '@ECMComponents/SubTopBar';

const Addons = () => {
	const addons = useSelector((state) => state.addons);

	const dispatch = useDispatch();

	return (
		<>
			<TopBar
				leftContent={() => (
					<>
						<span className="ecm-topbar-logo ecm-primary easy-content-manager-icon easy-content-manager-icon--ecm-primary" />

						<span className="easy-content-manager-icon easy-content-manager-icon--angle-right" />

						<ECMLabel
							type="subtitle"
							fontWeight="medium"
							label={__('Addons', 'easy-content-manager')}
						/>
					</>
				)}
			/>
			<Formik
				enableReinitialize={true}
				initialValues={getInitialValues(addons.data)}
				onSubmit={(values, actions) => {
					dispatch(saveAddonStatus(JSON.stringify(values)))
						.then(() => {
							dispatch(fetchUpdatedMenu());
						})
						.finally(() => {
							actions.setSubmitting(false);
						})
				}}
			>
				{() => {
					return (
						<Box className='content-manager-page-content'>
							<SubTopBar
								heading={__('Addons', 'easy-content-manager')}
							/>
							<FormInner />
						</Box>
					)
				}}
			</Formik>
		</>
	);
};

export default Addons;
