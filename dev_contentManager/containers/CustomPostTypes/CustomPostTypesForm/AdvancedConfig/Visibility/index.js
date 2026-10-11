import React from 'react';
import { __ } from '@wordpress/i18n';
import Checkbox from '@ECMComponents/Formik/Checkbox';
import { Field, useFormikContext } from 'formik';
import { getVisibilityFieldList, layoutOptions } from './helper';
import { Flex } from '@chakra-ui/react';
import ECMSeparator from '@ECMComponents/ECMSeparator';

import './styles.scss';

const Visibility = () => {
	const { values, setFieldValue } = useFormikContext();

	const filteredFields = values?.advanced_config_data.show_in_menu
		? getVisibilityFieldList
		: getVisibilityFieldList.filter(
				(item) =>
					item.name === 'advanced_config_data.register_meta_box_cb'
			);

	return (
		<Flex
			flexDirection="column"
			gap="4"
			padding="12px 12px"
			maxHeight="372px"
			minHeight="372px"
			overflowY="scroll"
		>
			<Checkbox
				name="advanced_config_data.show_ui"
				title={__('Show In UI', 'easy-content-manager')}
				subtitle={__(
					'Items can be edited and managed in the admin dashboard.',
					'easy-content-manager'
				)}
			/>

			{values?.advanced_config_data?.show_ui && (
				<React.Fragment>
					<Checkbox
						name="advanced_config_data.show_in_menu"
						title={__('Show In Admin Menu', 'easy-content-manager')}
						subtitle={__(
							'Admin editor navigation in the sidebar menu.',
							'easy-content-manager'
						)}
					/>

					{values?.advanced_config_data?.show_in_menu && (
						<Flex alignItems="flex-end" gap={6}>
							<span className="content-manager-title">
								{__('UI Type', 'easy-content-manager')}
							</span>

							{layoutOptions.map((item, index) => (
								<label
									className="ecm-radio-option"
									key={index}
									htmlFor={`advanced_config_data.ui_type-${index}`}
								>
									<Field
										id={`advanced_config_data.ui_type-${index}`}
										name="advanced_config_data.ui_type"
										type="radio"
										checked={
											(values?.advanced_config_data
												?.ui_type || 'modern') ===
											item.value
										}
										value={item.value}
									/>
									<span>{item.label}</span>
								</label>
							))}
						</Flex>
					)}

					<ECMSeparator />

					{filteredFields.map((item, index) => (
						<Flex flexDirection="column" gap="2" key={index}>
							<p className="content-manager-title">
								{item.title}
							</p>

							<Field
								name={item.name}
								type={item.type}
								placeholder={item.placeholder}
								className="content-manager-input content-manager-border content-manager-p-02"
								onBlur={(e) =>
									setFieldValue(item.name, e.target.value)
								}
							/>

							<span className="content-manager-sub-title">
								{item.subtitle}
							</span>
						</Flex>
					))}

					<ECMSeparator />

					<Checkbox
						name="advanced_config_data.show_in_admin_bar"
						title={__('Show In Admin Bar', 'easy-content-manager')}
						subtitle={__(
							"Appears as an item in the 'New' menu in the admin bar.",
							'easy-content-manager'
						)}
					/>
				</React.Fragment>
			)}

			<Checkbox
				name="advanced_config_data.appearance_menus_support"
				title={__('Appearance Menus Support', 'easy-content-manager')}
				subtitle={__(
					"Allow items to be added to menus in the 'Appearance' > 'Menus' screen. Must be turned on in 'Screen options'",
					'easy-content-manager'
				)}
			/>

			<Checkbox
				name="advanced_config_data.exclude_from_search"
				title={__('Exclude From Search', 'easy-content-manager')}
				subtitle={__(
					'Sets whether posts should be excluded from search results and taxonomy archive pages.',
					'easy-content-manager'
				)}
			/>
		</Flex>
	);
};

export default Visibility;
