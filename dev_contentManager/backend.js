import { createRoot } from 'react-dom/client';
import { createPortal } from '@wordpress/element';
import AdminMenu from '@ECMContainers/backend-dashboard/AdminMenu';
import BackendDashboard from '@ECMContainers/backend-dashboard';
import { Provider } from 'react-redux';
import store from '@ECMRedux/store';
import { BrowserRouter as Router } from 'react-router-dom';
import { ReactEditorHook } from './ReactEditor/ReactEditorHook';
import RouteAccessGuard from '@ECMComponents/RouteAccessGuard';
import {
	isReactRouteAllowed,
	redirectToFirstAllowedRoute,
} from '@ECMUtils/routeAccess';

import './../assets/scss/backend.scss';

document.addEventListener('DOMContentLoaded', () => {
	const container = document.getElementById('contentManagerWrap');
	if (container) {
		const page = new URLSearchParams(window.location.search).get('page');
		if (!isReactRouteAllowed(page)) {
			redirectToFirstAllowedRoute();
			return;
		}

		const root = createRoot(container);
		const menuPage = document.getElementById(
			'toplevel_page_easy-content-manager'
		);
		function MenuPortal({ children }) {
			menuPage.innerHTML = '';
			return createPortal(children, menuPage);
		}
		root.render(
			<Provider store={store}>
				<Router>
					<RouteAccessGuard>
						<MenuPortal>
							<AdminMenu />
						</MenuPortal>
						<BackendDashboard />
					</RouteAccessGuard>
				</Router>
			</Provider>
		);

		ReactEditorHook();
	}
});
