import React, { useEffect, useState } from 'react';
import { __ } from '@wordpress/i18n';
import { useDispatch } from 'react-redux';
import { getAllUserRoles, getExistingPostType } from '@ECMRedux/Slices/fieldGroupsSlice/fieldGroupsSlice';
import { capitalizeFirstLetter, is_pro } from '@ECMUtils/helper';
import LocationRules from './Tabs/LocationRules/LocationRules';
import GroupSettings from './Tabs/GroupSettings';
import { Tabs, Heading } from '@chakra-ui/react';
import { getSettings } from '@ECMRedux/Slices/settingsSlice/settingsSlice';
import { getAllTaxonomiesTag } from '@ECMRedux/Slices/taxonomySlice/taxonomySlice';

import './styles/styles.scss';

const FieldGroupsSettings = () => {
	const dispatch = useDispatch();

	const [pageOptions, setPageOptions] = useState([]);
	const [settingsOptions, setSettingsOptions] = useState([]);
	const [taxonomiesOptions, setTaxonomiesOptions] = useState([]);
	const [userOptions, setUserOptions] = useState([]);

	const fetchData = async (action, setter, mapper) => {
		const response = await dispatch(action);
		if (response?.payload) {
			setter(mapper(response.payload));
		}
	};

	useEffect(() => {
		fetchData(getExistingPostType(), setPageOptions, (payload) =>
			Object.entries(payload).map(([key, value]) => ({
				value: key,
				label: capitalizeFirstLetter(value),
			}))
		);

		fetchData(getAllTaxonomiesTag(), setTaxonomiesOptions, (payload) =>
			payload.map((item) => ({ value: item.name, label: item.label }))
		);

		fetchData(getAllUserRoles(), setUserOptions, (payload) =>
			payload.map((item) => ({ value: item.key, label: item.name }))
		);

		if (is_pro === '1') {
			fetchData(getSettings(), setSettingsOptions, (payload) => payload.items || []);
		}
	}, [dispatch]);

	return (
		<div className="ecm-content-wrap ecm--field-groups-settings">
			<Heading color="var(--ecm-font-color)" margin="0 0 1rem 0">
				{__('Advanced Settings', 'easy-content-manager')}
			</Heading>

			<div className="ecm--field-groups-settings__content">
				<Tabs.Root defaultValue="locationRules">
					<Tabs.List
						borderTopWidth="1px"
						borderColor="var(--ecm-border-color)"
						bg="var(--ecm-secondary)"
					>
						<Tabs.Trigger value="locationRules" color="var(--ecm-font-color)">
							{__('Location Rules', 'easy-content-manager')}
						</Tabs.Trigger>
						<Tabs.Trigger value="groupSettings" color="var(--ecm-font-color)">
							{__('Group Settings', 'easy-content-manager')}
						</Tabs.Trigger>
					</Tabs.List>

					<Tabs.Content value="locationRules">
						<LocationRules
							pageOptions={pageOptions}
							settingsOptions={settingsOptions}
							taxonomiesOptions={taxonomiesOptions}
							userOptions={userOptions}
						/>
					</Tabs.Content>

					<Tabs.Content value="groupSettings">
						<GroupSettings />
					</Tabs.Content>
				</Tabs.Root>
			</div>
		</div>
	);
};

export default FieldGroupsSettings;