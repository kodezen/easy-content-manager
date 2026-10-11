import React from 'react';
import { Link } from 'react-router-dom';
import { route_path, capitalizeFirstLetter } from '@ECMUtils/helper';

export default function MenuItem(props) {
	const { className, children, parent, currentPath, subMenuItems } = props;

	const isSubMenuItems = className === 'current' && subMenuItems.length > 0;

	return (
		<React.Fragment>
			<li className={className}>
				{children}
				{isSubMenuItems && (
					<>
						<ul className="wp-submenu">
							{/* <li
								className={
									(!subMenuItems.slug && !currentPath) ||
									currentPath === subMenuItems.slug
										? 'current'
										: ''
								}
							>
								<Link
									to={`${route_path}admin.php?page=${parent}${
										subMenuItems.title}`}
								>
									{capitalizeFirstLetter(subMenuItems.title)}
								</Link>
							</li> */}
							{subMenuItems?.length > 0 &&
								subMenuItems.map((item, index) => {
									return (
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
												to={`${route_path}admin.php?page=${parent}${
													(item.title && (parent.includes('addons') || index !== 0))
														? '&path=' + item.slug
														: ''
												}`}
											>
												{capitalizeFirstLetter(
													item.title
												)}
											</Link>
										</li>
									);
								})}
						</ul>
					</>
				)}
			</li>
		</React.Fragment>
	);
}
