import React, { useEffect, useMemo, useState } from 'react';
import { __ } from '@wordpress/i18n';
import { Field, useFormikContext } from 'formik';
import { getSupportsData } from './helper';
import Checkbox from '@ECMComponents/Formik/Checkbox';
import './styles.scss';
import { Box } from '@chakra-ui/react';

const General = ({ setIsError, isError }) => {
	const { values, setFieldValue } = useFormikContext();

	const supportsLists = getSupportsData();

	return (
		<div className="ecm--advanced-config--general">
			<Box padding={3}>
				<div className="content-manager-title-subtitle">
					<h3 className="content-manager-title">
						{__('Supports', 'easy-content-manager')}
					</h3>
					<span className="content-manager-sub-title">
						{__(
							'Enable various features in the content editor.',
							'easy-content-manager'
						)}
					</span>
				</div>

				<div className="ecm--advanced-config--general__supports-content">
					{supportsLists.map((item) => {
						const isChecked =
							values?.advanced_config_data?.supports?.find(
								(supportItem) => supportItem.includes(item.name)
							);
						return (
							<Checkbox
								key={item.id}
								name={item.name}
								title={item.title}
								type="checkbox"
								checked={isChecked ? true : false}
								handleCheckboxChangeProps={(e) => {
									setFieldValue(
										'advanced_config_data.supports',
										isChecked
											? values.advanced_config_data.supports.filter(
													(slug) => slug !== item.name
												)
											: [
													...values
														.advanced_config_data
														.supports,
													item.name,
												]
									);
								}}
								suffix="adv-settings-general"
							/>
						);
					})}
				</div>
			</Box>

			<div className="ecm--advanced-config--general__description">
				<h3 className="content-manager-title">
					{__('Description', 'easy-content-manager')}
				</h3>

				<Field
					name="advanced_config_data.description"
					className="content-manager-input"
					placeholder={__(
						'Enter description',
						'easy-content-manager'
					)}
					onBlur={(e) =>
						setFieldValue(
							'advanced_config_data.description',
							e.target.value
						)
					}
				/>

				<span className="content-manager-sub-title">
					{__(
						'A descriptive summary of the post type.',
						'easy-content-manager'
					)}
				</span>
			</div>
		</div>
	);
};

export default General;
