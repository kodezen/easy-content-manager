import { useEffect } from 'react';
import { __ } from '@wordpress/i18n';

const NavigationBlocker = ({ when, redirectURL = '' }) => {
	useEffect(() => {
		if (!when) return;
		const url = redirectURL ?? window.location.pathname;

		const handleBeforeUnload = (event) => {
			event.preventDefault();
			event.returnValue = '';
		};

		const handlePopState = () => {
			const confirmLeave = window.confirm(
				__(
					'You have unsaved changes. Are you sure you want to leave?',
					'easy-content-manager'
				)
			);
			if (!confirmLeave) {
				window.history.pushState(null, '', url);
			}
		};

		const handleClick = (event) => {
			const anchor = event.target.closest('a');
			if (anchor && anchor.href && anchor.target !== '_blank') {
				const url = new URL(anchor.href);
				if (url.origin === window.location.origin) {
					const confirmLeave = window.confirm(
						__(
							'You have unsaved changes. Are you sure you want to leave?',
							'easy-content-manager'
						)
					);
					if (!confirmLeave) {
						event.preventDefault();
					}
				}
			}
		};

		window.addEventListener('beforeunload', handleBeforeUnload);
		window.addEventListener('popstate', handlePopState);
		document.addEventListener('click', handleClick, true);
		window.history.pushState(null, '', url);

		return () => {
			window.removeEventListener('beforeunload', handleBeforeUnload);
			window.removeEventListener('popstate', handlePopState);
			document.removeEventListener('click', handleClick, true);
		};
	}, [when]);

	return null;
};

export default NavigationBlocker;
