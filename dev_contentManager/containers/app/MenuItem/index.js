import React from 'react';
import { ecm_app_slug } from '@ECMUtils/helper';
import { Link } from 'react-router-dom';
import { route_path, capitalizeFirstLetter } from '@ECMUtils/helper';

export default function MenuItem(props) {
	const {
		className,
		children,
		parent,
		currentPath,
		subMenuItems,
		showSubMenu,
	} = props;
	return (
		<li className={className}>
			{children}
			{showSubMenu && subMenuItems?.length > 0 && (
				<ul className="wp-submenu">
					{subMenuItems?.length > 0 &&
						subMenuItems.map((item, index) => (
							<li
								className={
									(!item.slug && !currentPath) ||
									currentPath === item.slug
										? 'current'
										: ''
								}
								key={index}
							>
								<Link
									to={`${route_path}admin.php?page=${ecm_app_slug}-${parent}${
										item.title ? '&path=' + item.slug : ''
									}`}
								>
									{capitalizeFirstLetter(item.title)}
								</Link>
							</li>
						))}
				</ul>
			)}
		</li>
	);
}
