const {
	admin_url: adminUrl,
	allowed_react_routes: allowedReactRoutes,
	first_allowed_ecm_url: firstAllowedEcmUrl,
} = window.EasyContentManagerGlobal;

export const isReactRouteAllowed = (page) =>
	Array.isArray(allowedReactRoutes) && allowedReactRoutes.includes(page);

export const redirectToFirstAllowedRoute = () => {
	const fallbackUrl = firstAllowedEcmUrl || adminUrl;

	if (window.location.href === fallbackUrl) {
		window.location.replace(adminUrl);
		return;
	}

	window.location.replace(fallbackUrl);
};
