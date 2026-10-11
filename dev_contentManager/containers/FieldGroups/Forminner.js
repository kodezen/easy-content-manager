import React, { useEffect } from 'react';
import { __, sprintf } from '@wordpress/i18n';
import TopBar from '@ECMComponents/TopBar';
import { Field, useFormikContext } from 'formik';
import { useDispatch, useSelector } from 'react-redux';
import { showNotification } from '@ECMRedux/Slices/notificationSlice/notificationSlice';
import {
	generateSlug,
	useQuery,
	route_path,
	makeRequest,
} from '@ECMUtils/helper';
import FieldGroupsSettings from './Settings';
import { Flex, Button } from '@chakra-ui/react';
import ECMLabel from '@ECMComponents/Labels/ECMLabel';
import { primaryBtn } from '../../../assets/scss/chakra/recipe';
import CustomFields from '@ECMComponents/CustomFields/CustomFields';
import {
	getFieldValueByPath,
	handleAddCustomField,
} from '@ECMComponents/CustomFields/helper';
import { updateAllFieldsLocalize } from '@ECMRedux/Slices/localizeSlice/localizeSlice';
import { isEqual } from 'lodash';
import { all_field_types } from '@ECMUtils/customField';

import '../FieldGroups/styles/FormInner.scss';
import SubTopBar from '@ECMComponents/SubTopBar';

const FormInner = ({
	isError,
	setIsError,
	open,
	setOpen,
	setCurrentTabName,
	currentTabNames,
	loading,
	setLoading,
}) => {
	const { all_fields } = useSelector((state) => state.localized_data);
	const { values, setFieldValue, handleSubmit, dirty, isSubmitting } =
		useFormikContext();

	const fieldPath = ['field_groups'];
	const customFields = getFieldValueByPath(values, fieldPath) || [];

	const dispatch = useDispatch();
	const location = useQuery();
	const ID = parseInt(location.get('id'));
	const btnLabel = ID
		? __('Update Field Groups', 'easy-content-manager')
		: __('Save Field Groups', 'easy-content-manager');

	const handleAddNewFieldGroup = () => {
		if (values?.group_name.length === 0) {
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
			return;
		}

		handleAddCustomField(
			values,
			setFieldValue,
			setCurrentTabName,
			setOpen,
			setLoading,
			all_fields,
			fieldPath
		);
	};

	const handleToggleCustomFields = () => {
		if (customFields?.length > 0 && open.length === 0) {
			const metaKey = customFields?.map((key, index) => {
				const togglePath = [...fieldPath, index];
				return `${key?.meta_key}_${togglePath.join('.')}`;
			});
			setOpen(metaKey);
			return;
		}
		if (customFields?.length > 0) {
			setOpen([]);
		}
	};

	useEffect(() => {
		if (all_fields && Object.keys(all_fields).length > 0) {
			const shallowCopy = { ...all_fields };
			const newAllFields = Object.fromEntries(
				Object.entries(shallowCopy).map(([key, value]) => {
					if (value?.object_type === 'term') {
						return [key, { ...value, object_type: 'post' }];
					}
					return [key, value];
				})
			);
			if (!isEqual(newAllFields, all_fields)) {
				dispatch(updateAllFieldsLocalize(newAllFields));
			}
			return;
		}

		makeRequest('get_all_default_custom_field', {
			field_type: all_field_types,
			post_type: '',
			object_type: 'post',
		}).then((response) => {
			const { data: fields } = response?.data;
			dispatch(updateAllFieldsLocalize(fields));

			if (customFields?.length > 0 || ID) return;

			const textField = fields['text-field'];

			textField &&
				setFieldValue('field_groups', [
					{
						...textField,
						meta_key: textField?.general?.field_name,
						post_meta_args: {
							...textField?.post_meta_args,
							label: textField?.general?.field_label,
						},
					},
				]);
			setOpen([`${textField?.general?.field_name}_field_groups.0`]);
		});
	}, [customFields?.length, setFieldValue]);

	return (
		<React.Fragment>
			<TopBar
				leftContent={() => (
					<>
						<span className="ecm-topbar-logo ecm-primary easy-content-manager-icon easy-content-manager-icon--ecm-primary" />

						<span className="easy-content-manager-icon easy-content-manager-icon--angle-right" />

						<ECMLabel
							type="subtitle"
							fontWeight="medium"
							label={__('Field Groups', 'easy-content-manager')}
							href={`${route_path}/admin.php?page=easy-content-manager-field-groups`}
						/>

						<span className="easy-content-manager-icon easy-content-manager-icon--angle-right" />

						<Field
							id="group-name"
							name="group_name"
							type="text"
							placeholder={__(
								'Group Name',
								'easy-content-manager'
							)}
							className="content-manager-input content-manager-input-320"
							onBlur={(e) =>
								setFieldValue(
									'slug',
									generateSlug(e.target.value)
								)
							}
						/>
					</>
				)}
				rightContent={() => (
					<Button
						loading={isSubmitting}
						disabled={!dirty}
						onClick={handleSubmit}
						{...primaryBtn}
					>
						{btnLabel}
					</Button>
				)}
			/>

			<div className="content-manager-page-content">
				<SubTopBar
					heading={sprintf(
						__('%s', 'easy-content-manager'),
						values?.id ? 'Update Group' : 'Create Group'
					)}
				/>
				<Flex
					align="center"
					justifyContent="space-between"
					gap={2}
					className={`ecm--field-groups ecm-content-wrap`}
				>
					<h3 className="content-manager-title">
						{__('Field Groups', 'easy-content-manager')}
					</h3>

					<div className="ecm--field-groups__actions">
						{customFields?.length > 0 ? (
							<Button
								bg="transparent"
								borderColor="var(--ecm-border-color)"
								borderWidth="1px"
								borderRadius="50%"
								padding="0"
								onClick={(e) => {
									handleToggleCustomFields();
								}}
							>
								<span
									className={`easy-content-manager-icon easy-content-manager-icon--${customFields?.length > 0 ? 'angle-up' : 'angle-down'}`}
								/>
							</Button>
						) : (
							<Button
								bg="var(--ecm-primary)"
								onClick={handleAddNewFieldGroup}
							>
								<span className="easy-content-manager-icon easy-content-manager-icon--simple-add has-ecm-blue-bg" />
								{__('Add', 'easy-content-manager')}
							</Button>
						)}
					</div>
				</Flex>

				<CustomFields
					open={open}
					setOpen={setOpen}
					currentTabNames={currentTabNames}
					setCurrentTabName={setCurrentTabName}
					isError={isError}
					setIsError={setIsError}
					isLoading={loading}
					setIsLoading={setLoading}
					fieldPath={fieldPath}
				/>

				<FieldGroupsSettings />
			</div>
		</React.Fragment>
	);
};

export default FormInner;
