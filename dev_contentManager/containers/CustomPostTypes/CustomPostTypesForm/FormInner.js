import React, {
	useCallback,
	useEffect,
	useRef,
	useState,
} from 'react';
import Checkbox from '@ECMComponents/Formik/Checkbox';
import { Flex, Heading, Button } from '@chakra-ui/react';
import { useFormikContext, Field } from 'formik';
import { __ } from '@wordpress/i18n';
import { generateSlug, makeRequest, useQuery } from '@ECMUtils/helper';
import {
	presetsHandler,
	presetButtonData,
	handleSaveAsFieldGroup,
} from './helper';
import { useDispatch, useSelector } from 'react-redux';
import { showNotification } from '@ECMRedux/Slices/notificationSlice/notificationSlice';
import { useNavigate } from 'react-router-dom';
import FieldGroupsModal from './FieldGroupModal/FieldGroupsModal';
import { getAllFieldGroups } from '@ECMRedux/Slices/fieldGroupsSlice/fieldGroupsSlice';
import AdvancedConfig from './AdvancedConfig';
import Select from 'react-select';
import { getAllTaxonomiesTag } from '@ECMRedux/Slices/taxonomySlice/taxonomySlice';
import ECMSeparator from '@ECMComponents/ECMSeparator';
import { transparentMiniBtn } from '../../../../assets/scss/chakra/recipe';
import {
	getFieldValueByPath,
	handleAddCustomField,
} from '@ECMComponents/CustomFields/helper';
import CustomFields from '@ECMComponents/CustomFields/CustomFields';
import { updateAllFieldsLocalize } from '@ECMRedux/Slices/localizeSlice/localizeSlice';
import { isEqual } from 'lodash';
import { all_field_types } from '@ECMUtils/customField';

import './styles/styles.scss';

const FormInner = ({ isError, setIsError }) => {
	const { all_fields } = useSelector((state) => state.localized_data);
	const { values, setFieldValue } = useFormikContext();

	const [open, setOpen] = useState([]);
	const [taxonomies, setTaxonomies] = useState([]);
	const [currentTabNames, setCurrentTabNames] = useState({});
	const [activeIndex, setActiveIndex] = useState(null);
	const [isMenuOpen, setIsMenuOpen] = useState(false);
	const [isPresetOpen, setIsPresetOpen] = useState(false);
	const [isModalOpen, setIsModalOpen] = useState(false);
	const [isLoading, setIsLoading] = useState(false);
	const currentRef = useRef(null);

	const fieldPath = ['custom_fields'];
	const customFields = getFieldValueByPath(values, fieldPath) || [];

	const dispatch = useDispatch();
	const navigate = useNavigate();
	const query = useQuery();

	const path = query.get('path');
	const action = query.get('action');

	const isEditMode = path === 'edit-post' && action === 'edit';

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

	const handleAddExistingFieldGroup = useCallback(() => {
		dispatch(getAllFieldGroups({})).then(() => {
			setIsMenuOpen(false);
			setIsModalOpen(true);
		});
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

	useEffect(() => {
		const handleClickOutside = (event) => {
			if (
				currentRef.current &&
				!currentRef.current.contains(event.target)
			) {
				setIsMenuOpen(false);
			}
		};

		document.addEventListener('mousedown', handleClickOutside);

		return () => {
			document.removeEventListener('mousedown', handleClickOutside);
		};
	}, []);

	useEffect(() => {
		dispatch(getAllTaxonomiesTag()).then(({ payload }) =>
			setTaxonomies(payload)
		);
	}, []);

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
			// setFieldValue('all_fields', fields);
		});
	}, [all_fields]);

	const taxonomiesOptions = [
		...(taxonomies
			?.filter((taxonomy) => taxonomy?.show_in_rest)
			?.map((taxonomy) => ({
				label: `${taxonomy.label} (${taxonomy.name})`,
				value: taxonomy.name,
				isShow: true,
			})) ?? []),

		...(taxonomies
			?.filter((taxonomy) => !taxonomy?.show_in_rest)
			?.map((taxonomy) => ({
				label: `${taxonomy.label} (${taxonomy.name})`,
				value: taxonomy.name,
				isShow: false,
			})) ?? []),
	];

	const defaultTaxonomyValues = values?.advanced_config_data?.taxonomies?.map(
		(taxonomy) => ({
			label: taxonomy,
			value: taxonomy,
		})
	);

	return (
		<>
			<div className={`ecm--add-post ecm-content-wrap`}>
				<Flex align="center" gap={2} marginBottom="24px">
					<Heading className="ecm-heading-label">
						{isEditMode
							? __('Edit Post Type', 'easy-content-manager')
							: __('Add Post Type', 'easy-content-manager')}
					</Heading>

					{!isEditMode && (
						<>
							<Button
								bg="var(--ecm-primary)"
								fontSize="12px"
								padding="2px 8px"
								lineHeight="16px"
								height="auto"
								onClick={() => {
									setIsPresetOpen(!isPresetOpen);
								}}
							>
								{__('Presets', 'easy-content-manager')}
								<span
									className={`easy-content-manager-icon easy-content-manager-icon--${isPresetOpen ? 'angle-left' : 'angle-right'} has-ecm-blue-bg`}
								/>
							</Button>

							<div
								className={`ecm--add-post__${isPresetOpen ? 'open' : 'presets'}`}
							>
								{isPresetOpen &&
									presetButtonData.map((item, index) => (
										<Button
											key={index}
											{...transparentMiniBtn}
											onClick={() => {
												presetsHandler(
													item.value,
													setFieldValue,
													dispatch,
													setActiveIndex,
													index
												);
											}}
										>
											{item.label}
										</Button>
									))}
							</div>
						</>
					)}
				</Flex>

				<Flex
					borderTopWidth="1px"
					borderColor="var(--ecm-border-color)"
				>
					<Flex
						width="70%"
						flexDirection="column"
						gap={4}
						padding="20px 0"
						borderRightWidth="1px"
						borderColor="var(--ecm-border-color)"
						marginRight="20px"
						paddingRight="20px"
					>
						<label
							htmlFor="plural-name"
							className="content-manager-column-items "
						>
							<span className="content-manager-title">
								{__('Plural Label', 'easy-content-manager')}
							</span>
							<Field
								id="plural-name"
								name="plural_name"
								className="content-manager-input content-manager-border ecm-width-full content-manager-p-02"
								placeholder={__(
									'Plural Label',
									'easy-content-manager'
								)}
								onBlur={(e) => {
									setFieldValue(
										'plural_name',
										e.target.value
									);
									setFieldValue(
										'advanced_config_data.labels.plural_name',
										e.target.value
									);
									setFieldValue(
										'post_slug',
										generateSlug(e.target.value)
									);
								}}
							/>
						</label>

						<label
							htmlFor="singular-name"
							className="content-manager-column-items "
						>
							<span className="content-manager-title">
								{__('Singular Label', 'easy-content-manager')}
							</span>
							<Field
								id="singular-name"
								name="singular_name"
								className="content-manager-input content-manager-border ecm-width-full content-manager-p-02"
								placeholder={__(
									'Singular Label',
									'easy-content-manager'
								)}
								onBlur={(e) => {
									setFieldValue(
										'advanced_config_data.labels.singular_name',
										e.target.value
									);
									setFieldValue(
										'advanced_config_data.labels.menu_name',
										e.target.value
									);
									setFieldValue(
										'advanced_config_data.labels.name',
										e.target.value
									);
									setFieldValue(
										'post_slug',
										generateSlug(e.target.value)
									);
									setFieldValue(
										'advanced_config_data.post_slug',
										generateSlug(e.target.value)
									);
								}}
							/>
						</label>

						<label
							htmlFor="slug-name"
							className="content-manager-column-items"
						>
							<span className="content-manager-title">
								{__('Post Type Key', 'easy-content-manager')}
							</span>
							<Field
								id="slug-name"
								name="post_slug"
								className="content-manager-input content-manager-border ecm-width-full content-manager-p-02"
								placeholder={__(
									'Post Type Key',
									'easy-content-manager'
								)}
								onBlur={(e) => {
									setFieldValue(
										'post_slug',
										generateSlug(e.target.value)
									);
									setFieldValue(
										'advanced_config_data.post_slug',
										generateSlug(e.target.value)
									);
								}}
							/>
						</label>

						<label
							htmlFor={`post-type-taxonomies-${values?.singular_name}`}
							className="content-manager-column-items"
						>
							<span className="content-manager-title">
								{__('Taxonomies', 'easy-content-manager')}
							</span>
							<Select
								id={`post-type-taxonomies-${values?.singular_name}`}
								name="advanced_config_data.taxonomies"
								options={taxonomiesOptions}
								className="content-manager-select"
								classNamePrefix="content-manager-select"
								placeholder={__(
									'Select Taxonomies',
									'easy-content-manager'
								)}
								isMulti={true}
								defaultValue={defaultTaxonomyValues}
								isOptionDisabled={(option) => !option.isShow}
								onChange={(options) => {
									const selectedValues =
										options?.map((opt) => opt.value) ?? [];
									setFieldValue(
										'advanced_config_data.taxonomies',
										selectedValues
									);
								}}
							/>
							<span className="content-manager-sub-title">
								{__(
									'Select existing taxonomies to classify items of the post type.',
									'easy-content-manager'
								)}
							</span>
						</label>

						<ECMSeparator isTop={true} isBottom={true} />

						<Checkbox
							title={__('Active', 'easy-content-manager')}
							subtitle={__(
								'Active post types are enabled and registered with WordPress.',
								'easy-content-manager'
							)}
							name="advanced_config_data.active"
							defaultValue={values?.advanced_config_data?.active}
							suffix="advanced-config-checkbox-active"
						/>

						<Checkbox
							name="is_public"
							title={__('Public', 'easy-content-manager')}
							subtitle={__(
								'Visible on the frontend and in the admin dashboard.',
								'easy-content-manager'
							)}
						/>

						<Checkbox
							name="hierarchical"
							title={__('Hierarchical', 'easy-content-manager')}
							subtitle={__(
								'Hierarchical post types can have descendants (like pages).',
								'easy-content-manager'
							)}
							suffix="dynamic-form-hierarchical"
						/>
					</Flex>

					<Flex
						width="30%"
						flexDirection="column"
						gap={6}
						padding="20px 0"
					>
						<AdvancedConfig
							disabled={!values?.advanced_configuration}
						/>
					</Flex>
				</Flex>
			</div>

			<div className="ecm--custom-field-form-inner">
				<div
					className={`ecm--custom-field-form-inner__add-custom-field ecm--custom-field-form-inner__add-custom-field${customFields.length > 0 ? '-has' : ''}`}
					onClick={() => handleCustomFieldToggle()}
				>
					<h3 className="content-manager-title">
						{__('Custom fields', 'easy-content-manager')}
					</h3>

					<div className="ecm--custom-field-form-inner__actions">
						<Button
							bg="transparent"
							onClick={(e) => {
								e.stopPropagation();
								if (!values?.singular_name)
									return notifySingularNameError();
								setIsMenuOpen((prev) => !prev);
							}}
						>
							<span className="easy-content-manager-icon easy-content-manager-icon--menu-flat" />
						</Button>

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

						{isMenuOpen && (
							<Flex
								ref={currentRef}
								flexDirection="column"
								gap={2}
								className={`ecm--custom-field-form-inner__custom-fields-menu ${customFields.length > 0 ? '' : 'easy-content-manager-custom-fields-menu--top'}`}
							>
								{customFields.length > 0 && (
									<Button
										bg="transparent"
										color="var(--ecm-font-color)"
										borderColor="var(--ecm-border-color)"
										borderWidth="1px"
										textAlign="left"
										onClick={(e) => {
											e.stopPropagation();
											handleSaveAsFieldGroup(
												values,
												navigate,
												dispatch
											);
											setIsMenuOpen(false);
										}}
									>
										<span className="easy-content-manager-icon easy-content-manager-icon--add" />
										{__(
											'Save As Field Group',
											'easy-content-manager'
										)}
									</Button>
								)}

								<Button
									bg="transparent"
									color="var(--ecm-font-color)"
									borderColor="var(--ecm-border-color)"
									borderWidth="1px"
									textAlign="left"
									onClick={(e) => {
										e.stopPropagation();
										handleAddExistingFieldGroup();
									}}
								>
									<span className="easy-content-manager-icon easy-content-manager-icon--add" />
									{__(
										'Add From Existing Field Group',
										'easy-content-manager'
									)}
								</Button>
							</Flex>
						)}
					</div>
				</div>

				<CustomFields
					open={open}
					setOpen={setOpen}
					currentTabNames={currentTabNames}
					setCurrentTabName={setCurrentTabName}
					isError={isError}
					setIsError={setIsError}
					isLoading={isLoading}
					setIsLoading={setIsLoading}
					fieldPath={fieldPath}
					obj_subtype={values?.post_slug}
				/>
			</div>

			<FieldGroupsModal
				isModalOpen={isModalOpen}
				setIsModalOpen={setIsModalOpen}
			/>
		</>
	);
};

export default FormInner;
