import React, { useEffect } from 'react';
import { Link, useLocation } from 'react-router-dom';
import { __ } from '@wordpress/i18n';
import {
	route_path,
	toplevel_menu_icon_url,
	toplevel_menu_title,
	capitalizeFirstLetter,
	is_pro,
} from '@ECMUtils/helper';
import { useSelector } from 'react-redux';
import MenuItem from './MenuItem';

function useQuery() {
	const { search } = useLocation();

	return React.useMemo(() => new URLSearchParams(search), [search]);
}

const AdminMenu = () => {
	const adminmenu = useSelector((state) => state.menu);
	const location = useQuery();
	const page = location.get('page');
	const path = location.get('path');

	useEffect(() => {
		document.title = `${adminmenu?.data[page]?.title} - ${toplevel_menu_title}`;
	}, [page]);

	return (
		<React.Fragment>
			<Link
				to={`${route_path}admin.php?page=easy-content-manager`}
				className="wp-has-submenu wp-has-current-submenu wp-menu-open menu-top toplevel_page_content_manager menu-top-last"
				aria-haspopup="false"
			>
				<div className="wp-menu-arrow">
					<div></div>
				</div>
				<div
					className="wp-menu-image svg"
					style={{
						backgroundImage: `url('${toplevel_menu_icon_url}')`,
					}}
					aria-hidden="true"
				>
					<br />
				</div>
				<div className="wp-menu-name">{toplevel_menu_title}</div>
			</Link>
			<ul className="wp-submenu wp-submenu-wrap">
				<li className="wp-submenu-head" aria-hidden="true">
					{toplevel_menu_title}
				</li>
				{Object.entries(adminmenu.data).map(([key, item], index, self) => {
					if (
						[
							'content-manager-get-pro',
							'content-manager-license',
						].includes(key)
					) {
						return null;
					}

					const menuItemClassName =
						(index === 0 || index === self[self.length - 2]) ? 'wp-first-item ' : '';

					return (
						<MenuItem
							className={
								page === key
									? menuItemClassName + 'current'
									: menuItemClassName
							}
							key={index}
							parent={key}
							currentPath={path}
							subMenuItems={item?.sub_items ?? []}
						>
							<Link
								key={key}
								to={`${route_path}admin.php?page=${key}`}
								className={menuItemClassName}
							>
								{item.title}
								{item?.sub_items && (
									<span
										className={`has-ecm-blue-bg easy-content-manager-icon easy-content-manager-icon--${page === key ? 'angle-down' : 'angle-right'}`}
										style={{
											float: 'right',
											marginTop: '2px',
										}}
									/>
								)}
							</Link>
						</MenuItem>
					);
				})}

				{/* {Boolean(Number(is_pro)) ? (
					<li
						className={
							page === 'storeengine-license' ? 'current' : ''
						}
					>
						<a href="admin.php?page=storeengine-license">
							{__('License', 'storeengine')}
						</a>
					</li>
				) : (
					<li
						className={
							page === 'storeengine-get-pro' ? 'current' : ''
						}
					>
						<a href="admin.php?page=storeengine-get-pro">
							<span className="dashicons dashicons-awards storeengine-blue-color"></span>{' '}
							{__('Get Pro', 'storeengine')}
						</a>
					</li>
				)} */}
			</ul>
		</React.Fragment>
	);
};

export default AdminMenu;
