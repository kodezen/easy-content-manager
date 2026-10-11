import { configureStore } from '@reduxjs/toolkit';

// reducers
import menuReducer from './Slices/menuSlice/menuSlice';
import collectionDataReducer from './Slices/collectionDataSlice/collectionDataSlice';
import notificationReducer from './Slices/notificationSlice/notificationSlice';
import fieldGroupsReducer from './Slices/fieldGroupsSlice/fieldGroupsSlice';
import taxonomyReducer from './Slices/taxonomySlice/taxonomySlice';
import settingsReducer from './Slices/settingsSlice/settingsSlice';
import localizeReducer from './Slices/localizeSlice/localizeSlice';
import addonReducer from './Slices/addonSlice/addonSlice';
import reviewReducer from './Slices/reviewSlice/reviewSlice';
import claimReducer from './Slices/claimSlice/claimSlice';

import logger from 'redux-logger';

let middleware = [];
if (process.env.NODE_ENV !== 'production') {
	middleware = [logger];
}

const store = configureStore({
	reducer: {
		addons: addonReducer,
		claims: claimReducer,
		field_groups: fieldGroupsReducer,
		localized_data: localizeReducer,
		menu: menuReducer,
		notification: notificationReducer,
		post_types: collectionDataReducer,
		reviews: reviewReducer, 
		settings: settingsReducer,
		taxonomies: taxonomyReducer,
	},
	middleware: (getDefaultMiddleware) =>
		getDefaultMiddleware().concat(...middleware),
});

export default store;
