import React, { useState } from 'react';

import Button from '@ECMComponents/Button';
import PropTypes from 'prop-types';
import Select from 'react-select';
import { __ } from '@wordpress/i18n';

const propTypes = {
	data: PropTypes.any,
	applyActionHandler: PropTypes.func,
	confirmMessage: PropTypes.string,
	options: PropTypes.array,
};

export default function BulkAction({
	data = [],
	applyActionHandler,
	confirmMessage = '',
	options = [
		{ value: 'delete', label: __('Delete', 'easy-content-manager') },
	],
}) {
	const [bulkAction, setBulkAction] = useState({});
	const bulkMessage =
		bulkAction?.value === 'restore'
			? __(
					'Are you sure you want to restore selected items?',
					'easy-content-manager'
				)
			: confirmMessage;

	return (
		<React.Fragment>
			<div className="easy-content-manager-bulk-actions">
				<Select
					className="easy-content-manager-select"
					classNamePrefix="easy-content-manager-react-select"
					placeholder={__('Bulk Actions', 'easy-content-manager')}
					isClearable={true}
					value={options?.find(
						(item) => item?.value === bulkAction?.value
					)}
					options={options}
					onChangeHandler={(e) => setBulkAction(e)}
				/>
				<Button
					type="button"
					label={__('Apply', 'easy-content-manager')}
					preset="light-purple"
					onClick={() => {
						if (
							data.selectedRows &&
							data.selectedRows.length > 0 &&
							bulkAction.value &&
							confirm(bulkMessage) //eslint-disable-line
						) {
							applyActionHandler(data.selectedRows, bulkAction);
							setBulkAction('');
						}
					}}
				/>
			</div>
		</React.Fragment>
	);
}

BulkAction.propTypes = propTypes;
