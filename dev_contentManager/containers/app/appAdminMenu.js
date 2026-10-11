import React, { useEffect } from 'react';
import { Link } from 'react-router-dom';
import { __ } from '@wordpress/i18n';
import {
	route_path,
	toplevel_menu_title,
	capitalizeFirstLetter,
} from '@ECMUtils/helper';
import { ecm_app_slug, get_ecm_app_slug } from '@ECMUtils/helper';
import MenuItem from './MenuItem';

const AdminMenu = ({
	appName,
	adminmenu,
	toplevelMenuTitle,
	iconClass = 'dashicons-admin-post',
}) => {
	const searchParams = new URLSearchParams(window.location.search);
	const page = searchParams.get('page');
	const path = searchParams.get('path');
	const route_separator =
		ecm_app_slug.startsWith('ecm-app') && appName ? '-' : '';

	useEffect(() => {
		document.title =
			capitalizeFirstLetter(
				page.split(ecm_app_slug + route_separator)[1]
			) +
			' - ' +
			toplevel_menu_title;
	}, [page]);

	return (
		<React.Fragment>
			<Link
				to={`${route_path}admin.php?page=${ecm_app_slug}${route_separator}${appName}`}
				className="wp-has-submenu wp-has-current-submenu wp-menu-open menu-top toplevel_page_content_manager menu-top-last"
				aria-haspopup="false"
			>
				<div
					className={`dashicons-before ${iconClass}`}
					aria-hidden="true"
					style={{
						padding: '8px',
						display: 'flex',
						alignItems: 'center',
						justifyContent: 'flex-start',
						gap: '6px',
					}}
				>
					<div>{toplevelMenuTitle}</div>
				</div>
			</Link>
			{adminmenu.length > 0 && (
				<ul className="wp-submenu wp-submenu-wrap ecm-custom-portal">
					<li className="wp-submenu-head" aria-hidden="true">
						{toplevelMenuTitle}
					</li>

					{adminmenu.length > 0 &&
						adminmenu.map((item, index) => {
							const menuItemClassName =
								(index === 0 ? 'wp-first-item ' : '') +
								(page === `${get_ecm_app_slug(item.slug)}`
									? 'current'
									: '');
							const showSubMenu =
								page === `${get_ecm_app_slug(item.slug)}` &&
								item?.sub_items.length > 0;

							return (
								<MenuItem
									key={index}
									className={menuItemClassName}
									parent={item.slug}
									currentPath={path}
									subMenuItems={item?.sub_items ?? []}
									showSubMenu={showSubMenu}
								>
									<Link
										key={item.slug}
										to={`${route_path}admin.php?page=${get_ecm_app_slug(item.slug)}`}
										className={
											index === 0 ? 'wp-first-item' : ''
										}
									>
										{capitalizeFirstLetter(item.title)}
										{item?.sub_items && (
											<span className="content-manager-icon content-manager-icon--angle-right" />
										)}
									</Link>
								</MenuItem>
							);
						})}
				</ul>
			)}
		</React.Fragment>
	);
};

export default AdminMenu;
