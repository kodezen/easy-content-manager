import React, { useCallback, useState } from 'react';
import { __ } from '@wordpress/i18n';
import CustomFields from '@ECMComponents/CustomFields/CustomFields';
import { Button } from '@chakra-ui/react';
import { useFormikContext } from 'formik';
import {
	getFieldValueByPath,
	handleAddCustomField,
} from '@ECMComponents/CustomFields/helper';
import { useDispatch } from 'react-redux';
import { showNotification } from '@ECMRedux/Slices/notificationSlice/notificationSlice';

const CustomFieldFormInner = () => {
	const { values, setFieldValue } = useFormikContext();

	const [open, setOpen] = useState([]);
	const [error, setError] = useState('');
	const [currentTabNames, setCurrentTabNames] = useState({});
	const [isLoading, setIsLoading] = useState(false);

	const fieldPath = ['custom_fields'];
	const customFields = getFieldValueByPath(values, fieldPath) || [];

	const dispatch = useDispatch();

	const setCurrentTabName = useCallback((metaKey, tabName) => {
		setCurrentTabNames((prev) => ({ ...prev, [metaKey]: tabName }));
	}, []);

	const notifySingularNameError = useCallback(() => {
		dispatch(
			showNotification({
				message: __(
					'First enter a singular name',
					'easy-content-manager'
				),
				isShow: true,
				type: 'error',
			})
		);
	}, [dispatch]);

	const handleCustomFieldToggle = () => {
		if (values?.singular_name.length === 0)
			return notifySingularNameError();

		if (customFields?.length > 0 && open.length === 0) {
			const metaKey = customFields?.map((key, i) => {
				const togglePath = [...fieldPath, i];
				return `${key?.meta_key}_${togglePath.join('.')}`;
			});
			setOpen(metaKey);
			return;
		}

		if (customFields?.length > 0) return setOpen([]);

		if (customFields?.length === 0)
			return handleAddCustomField(
				values,
				setFieldValue,
				setCurrentTabName,
				setOpen,
				setIsLoading,
				fieldPath
			);
	};

	return (
		<div className="ecm--custom-field-form-inner">
			<div
				className={`ecm--custom-field-form-inner__add-custom-field ecm--custom-field-form-inner__add-custom-field${customFields.length > 0 ? '-has' : ''}`}
				onClick={() => handleCustomFieldToggle()}
			>
				<h3 className="content-manager-title">
					{__('Custom fields', 'easy-content-manager')}
				</h3>

				<div className="ecm--custom-field-form-inner__actions">
					{customFields?.length > 0 ? (
						<Button
							bg="transparent"
							borderColor="var(--ecm-border-color)"
							borderWidth="1px"
							padding="4px"
							borderRadius="50%"
							onClick={(e) => {
								e.stopPropagation();
								handleCustomFieldToggle();
							}}
						>
							<span
								className={`easy-content-manager-icon easy-content-manager-icon--${open.length > 0 ? 'angle-down' : 'angle-up'}`}
							/>
						</Button>
					) : (
						<Button
							bg="var(--ecm-primary)"
							onClick={(e) => {
								e.stopPropagation();
								if (!values?.singular_name)
									return notifySingularNameError();
								handleAddCustomField(
									values,
									setFieldValue,
									setCurrentTabName,
									setOpen,
									setIsLoading,
									fieldPath
								);
							}}
							isLoading={isLoading}
							isDisabled={isLoading}
						>
							<span className="easy-content-manager-icon easy-content-manager-icon--simple-add has-ecm-blue-bg" />
							{__('Add', 'easy-content-manager')}
						</Button>
					)}
				</div>
			</div>

			<CustomFields
				open={open}
				setOpen={setOpen}
				currentTabNames={currentTabNames}
				setCurrentTabName={setCurrentTabName}
				isError={error}
				setIsError={setError}
				isLoading={isLoading}
				setIsLoading={setIsLoading}
				fieldPath={fieldPath}
				obj_subtype={values?.taxonomy_slug}
			/>
		</div>
	);
};

export default CustomFieldFormInner;
