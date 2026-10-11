import { createRoot } from 'react-dom/client';
import { createPortal } from '@wordpress/element';
import { Provider } from 'react-redux';
import store from '@ECMRedux/store';
import { BrowserRouter as Router } from 'react-router-dom';
import AppBackendDashboard from '@ECMContainers/app';
import AdminMenu from '@ECMContainers/app/appAdminMenu';
import {
	capitalizeFirstLetter,
	post_types,
	ecm_app_slug,
	ecm_settings_slugs,
} from '@ECMUtils/helper';
import RouteAccessGuard from '@ECMComponents/RouteAccessGuard';
import {
	isReactRouteAllowed,
	redirectToFirstAllowedRoute,
} from '@ECMUtils/routeAccess';

import './../assets/scss/backend.scss';

const getMenuDataWithTaxonomies = (topLevelMenuElement) => {
	const parent_slug = topLevelMenuElement.id.includes(
		`toplevel_page_${ecm_app_slug}-`
	)
		? topLevelMenuElement.id.replace(`toplevel_page_${ecm_app_slug}-`, '')
		: topLevelMenuElement.id;

	const missingClass = [
		'wp-has-submenu',
		'wp-has-current-submenu',
		'wp-menu-open',
	];
	missingClass.forEach((className) => {
		if (!topLevelMenuElement.classList.contains(className)) {
			topLevelMenuElement.classList.add(className);
		}
	});

	const title = capitalizeFirstLetter(
		topLevelMenuElement
			.querySelector('.wp-menu-name')
			?.textContent.trim() || parent_slug
	);

	const submenuItems = Array.from(
		topLevelMenuElement.querySelectorAll(
			'.wp-submenu li:not(.wp-submenu-head)'
		)
	)
		.map((li) => {
			const a = li.querySelector('a');
			if (!a) return null;

			const href = new URL(
				a.getAttribute('href'),
				window.location.origin
			);
			const pageParam = new URLSearchParams(new URL(href).search).get('page');
			const slug = pageParam?.includes(ecm_app_slug + '-')
				? pageParam.replace(ecm_app_slug + '-', '')
				: '';

			const text = a.textContent.trim() || slug;

			const matchedPost =
				post_types[slug] || post_types[text.toLowerCase()];

			const sub_items =
				matchedPost?.taxonomy.map((tax) => ({
					slug: tax.label,
					parent_slug: slug,
					title: capitalizeFirstLetter(tax.label),
				})) || [];

			return {
				slug,
				title: text,
				sub_items,
			};
		})
		.filter(Boolean);

	// 👇 If there are no submenuItems, create one using post_types directly
	if (submenuItems.length === 0) {
		const matchedPost = post_types[parent_slug];

		if (matchedPost) {
			const sub_items = matchedPost.taxonomy.map((tax) => ({
				slug: tax.label,
				parent_slug,
				title: capitalizeFirstLetter(tax.label),
			}));

			submenuItems.push({
				slug: parent_slug,
				title,
				sub_items,
			});

			const submenuContainer = document.createElement('ul');
			submenuContainer.className = 'wp-submenu';

			const heading = document.createElement('li');
			heading.className = 'wp-submenu-head';
			heading.textContent = title;
			submenuContainer.appendChild(heading);

			sub_items.forEach((item) => {
				const li = document.createElement('li');
				const a = document.createElement('a');
				a.href = `admin.php?page=${ecm_app_slug}-${item.slug}`;
				a.textContent = item.title;
				li.appendChild(a);
				submenuContainer.appendChild(li);
			});

			// Append it to the top-level menu
			topLevelMenuElement.appendChild(submenuContainer);
		}
	}

	const iconDiv = topLevelMenuElement.querySelector('.wp-menu-image');
	let iconClass = 'dashicons-admin-post';
	if (iconDiv) {
		const icon = Array.from(iconDiv.classList).find(
			(cls) => cls.startsWith('dashicons-') && cls !== 'dashicons-before'
		);
		iconClass = `dashicons-before${icon ? ' ' + icon : ''}`;
	}

	const commonObj = {
		parent_slug,
		title,
		capability: 'manage_options',
		sub_items: submenuItems,
		iconClass,
	};

	const isSettingsBuilder =
		topLevelMenuElement.classList.contains('settings-menu');

	if (isSettingsBuilder) return { ...commonObj, settings_builder: true };
	return { ...commonObj, settings_builder: false };
};

document.addEventListener('DOMContentLoaded', () => {
	const container = document.getElementById(`${ecm_app_slug}-wrap`);
	if (!container) return;

	if (container.dataset.ecmReactMounted === 'true') {
		console.warn('React app already mounted — skipping duplicate render.');
		return;
	}
	container.dataset.ecmReactMounted = 'true';

	const urlParams = new URLSearchParams(window.location.search);
	const activeSlug = urlParams.get('page');
	if (!activeSlug) return;
	if (!isReactRouteAllowed(activeSlug)) {
		redirectToFirstAllowedRoute();
		return;
	}
	let topLevelId = '';

	const settingsRoute =
		ecm_settings_slugs.length > 0
			? ecm_settings_slugs.some((item) => item.slug === activeSlug)
			: false;

	const currentSlug = activeSlug.replace(ecm_app_slug + '-', '');
	let topLevelMenu = document.getElementById(
		`toplevel_page_${ecm_app_slug}-` + currentSlug
	);
	if (topLevelMenu) {
		topLevelId = `toplevel_page_${ecm_app_slug}-` + currentSlug;
	}

	if (!topLevelMenu) {
		const link = document.querySelector(`a[href*="page=${activeSlug}"]`);

		if (link) {
			const topLevelSubmenuMenu = link.closest('li.wp-has-submenu');
			topLevelId = topLevelSubmenuMenu?.id;
			topLevelMenu = document.getElementById(topLevelId);
		}
	}

	const structuredMenu = getMenuDataWithTaxonomies(topLevelMenu);

	const root = createRoot(container);
	function MenuPortal({ children }) {
		if (
			topLevelId.includes(
				// ecm_app_slug.startsWith("ecm-app")
				// ?
				`toplevel_page_${ecm_app_slug}` //this is not the best way to fix
				// : `toplevel_page_${ecm_app_slug}`
			)
		) {
			topLevelMenu.innerHTML = '';
			return createPortal(children, topLevelMenu);
		}
		const submenu = topLevelMenu.querySelector('.wp-submenu');

		if (!submenu) return null;

		submenu
			.querySelectorAll('.ecm-injected-item')
			.forEach((el) => el.remove());

		const wrapper = document.createElement('div');
		createRoot(wrapper).render(children);

		Array.from(wrapper.childNodes).forEach((child) => {
			const li = document.createElement('li');
			li.classList.add('ecm-injected-item');
			li.appendChild(child);
			submenu.appendChild(li);
		});

		return null;
	}

	// // replace existing MenuPortal component with this
	// function MenuPortal({ children }) {
	// 	if (topLevelId.includes(`toplevel_page_${ecm_app_slug}`)) {
	// 		topLevelMenu.innerHTML = '';
	// 		return createPortal(children, topLevelMenu);
	// 	}

	// 	const submenu = topLevelMenu.querySelector('.wp-submenu');
	// 	if (!submenu) return null;

	// 	useEffect(() => {
	// 		Array.from(submenu.children).forEach((child) => {
	// 			if (!child.classList.contains('ecm-injected-item')) {
	// 				child.style.display = 'none';
	// 			}
	// 		});
	// 	}, [submenu]);
	// 	return createPortal(
	// 		<div className="ecm-injected-item">{children}</div>,
	// 		submenu
	// 	);
	// }

	root.render(
		<Provider store={store}>
			<Router>
				<RouteAccessGuard>
					<MenuPortal>
						<AdminMenu
							appName={structuredMenu?.parent_slug}
							adminmenu={structuredMenu?.sub_items}
							toplevelMenuTitle={structuredMenu?.title}
							iconClass={structuredMenu?.iconClass}
							settingsBuilder={settingsRoute}
						/>
					</MenuPortal>
					<AppBackendDashboard settingsBuilder={settingsRoute} />
				</RouteAccessGuard>
			</Router>
		</Provider>
	);
});
