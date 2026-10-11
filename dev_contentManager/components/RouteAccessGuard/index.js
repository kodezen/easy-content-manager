import { useEffect } from 'react';
import { useLocation } from 'react-router-dom';
import {
	isReactRouteAllowed,
	redirectToFirstAllowedRoute,
} from '@ECMUtils/routeAccess';

const RouteAccessGuard = ({ children }) => {
	const { search } = useLocation();
	const page = new URLSearchParams(search).get('page');
	const isAllowed = isReactRouteAllowed(page);

	useEffect(() => {
		if (!isAllowed) {
			redirectToFirstAllowedRoute();
		}
	}, [isAllowed]);

	return isAllowed ? children : null;
};

export default RouteAccessGuard;
