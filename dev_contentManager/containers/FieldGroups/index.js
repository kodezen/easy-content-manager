import React, { useCallback, useEffect, useState } from 'react';
import { __ } from '@wordpress/i18n';
import FieldGroupsTable from './FieldGroupsTable';
import { Formik } from 'formik';
import FormInner from './Forminner';
import { showNotification } from '@ECMRedux/Slices/notificationSlice/notificationSlice';
import {
	createFieldGroup,
	getAllFieldGroups,
	getFieldGroups,
	updateFieldGroups,
} from '@ECMRedux/Slices/fieldGroupsSlice/fieldGroupsSlice';
import { useDispatch, useSelector } from 'react-redux';
import { useNavigate } from 'react-router-dom';
import { route_path } from '@ECMUtils/helper';
import { getInitialValues, handleUpdateFieldGroupLocalize } from './helper';
import {
	createFieldGroupLocalize,
	updateFieldGroupLocalize,
} from '@ECMRedux/Slices/localizeSlice/localizeSlice';
import NavigationBlocker from '@ECMComponents/NavigationBlocker';

import '../FieldGroups/styles/styles.scss';

const FieldGroups = ({ action, ID }) => {
	const { data } = useSelector((state) => state.field_groups);

	const [isError, setIsError] = useState(false);
	const [open, setOpen] = useState([]);
	const [currentTabNames, setCurrentTabNames] = useState('');
	const [loading, setLoading] = useState(false);
	const [oldSlug, setOldSlug] = useState('');

	const dispatch = useDispatch();
	const navigate = useNavigate();

	const setCurrentTabName = useCallback((metaKey, tabName) => {
		setCurrentTabNames((prev) => ({ ...prev, [metaKey]: tabName }));
	}, []);

	useEffect(() => {
		if (ID) {
			dispatch(getAllFieldGroups({ status: 'all' })).then(() => {
				dispatch(getFieldGroups({ ID })).then(({ payload: data }) => {
					setOldSlug(data.slug);
				});
			});
		}
	}, [ID]);

	const onSubmitHandler = (values, actions) => {
		const { all_fields, duplicate_field_names, ...rest } = values;
		if (values.duplicate_field_names) {
			dispatch(
				showNotification({
					message:
						'You have duplicate field names. Please check your fields.',
					isShow: true,
					type: 'error',
				})
			);
			actions.setSubmitting(false);
			return;
		}

		if (!rest.group_name) {
			dispatch(
				showNotification({
					message: __(
						'First enter a group name',
						'easy-content-manager'
					),
					isShow: true,
					type: 'error',
				})
			);
			actions.setSubmitting(false);
		} else if (rest.id) {
			dispatch(updateFieldGroups(rest))
				.then(({ payload: data }) => {
					const updatedLocalizeData = handleUpdateFieldGroupLocalize(
						data,
						oldSlug
					);
					dispatch(updateFieldGroupLocalize(updatedLocalizeData));
					setOldSlug('');
				})
				.finally(() => {
					actions.setSubmitting(false);
				});
		} else {
			dispatch(createFieldGroup(rest))
				.then(({ payload: data }) => {
					if (data.id) {
						const updatedLocalizeData =
							handleUpdateFieldGroupLocalize(data);
						dispatch(
							createFieldGroupLocalize({
								...updatedLocalizeData,
								post_slug: data.slug,
							})
						);
						const url = `${route_path}admin.php?page=easy-content-manager-field-groups&action=edit&id=${data.id}`;
						navigate(url);
					}
				})
				.finally(() => actions.setSubmitting(false));
		}
	};

	return (
		<>
			<Formik
				enableReinitialize={true}
				initialValues={getInitialValues(ID, data)}
				onSubmit={onSubmitHandler}
			>
				{({ dirty }) => {
					return action === 'add' || action === 'edit' ? (
						<>
							<NavigationBlocker when={dirty} />
							<FormInner
								isError={isError}
								setIsError={setIsError}
								open={open}
								setOpen={setOpen}
								currentTabNames={currentTabNames}
								setCurrentTabName={setCurrentTabName}
								loading={loading}
								setLoading={setLoading}
							/>
						</>
					) : (
						<FieldGroupsTable
							setOpen={setOpen}
							setCurrentTabName={setCurrentTabName}
							ID={ID}
							setLoading={setLoading}
						/>
					);
				}}
			</Formik>
		</>
	);
};

export default FieldGroups;
