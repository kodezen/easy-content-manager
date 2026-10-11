import React, { useEffect, useRef, useState } from 'react';
import PropTypes from 'prop-types';
import './styles.scss';
import { createPortal } from 'react-dom';
import Button from '@ECMComponents/Button';

const propTypes = {
	icon: PropTypes.string,
	suffix: PropTypes.string,
	options: PropTypes.array,
	iconClass: PropTypes.node,
	alwaysShowOptions: PropTypes.bool,
};

const OptionMenu = (props) => {
	const {
		icon = 'menu-flat',
		options = [],
		iconClass,
		suffix = '',
		alwaysShowOptions = false,
	} = props;

	const [itemSelected, setItemSelected] = useState(false);
	const menuItemRef = useRef(null);
	const relativeTo = useRef(null);

	const handleClick = (e) => {
		if (
			menuItemRef?.current &&
			!menuItemRef?.current?.contains(e.target) &&
			!relativeTo.current.contains(e.target)
		) {
			setItemSelected(false);
		}
	};

	const handleMenuToggle = () => {
		setItemSelected(!itemSelected);
	};

	useEffect(() => {
		if (!alwaysShowOptions) {
			document.addEventListener('mousedown', handleClick);
			return () => document.removeEventListener('mousedown', handleClick);
		}
	}, [alwaysShowOptions]);

	useEffect(() => {
		if (alwaysShowOptions) return;

		if (itemSelected && relativeTo.current) {
			const rect = relativeTo.current.getBoundingClientRect();
			const x = rect.left + window.pageXOffset;
			const y = rect.top + window.pageYOffset;
			const buttonHeight = relativeTo.current.offsetHeight;
			menuItemRef.current.style.position = 'absolute';
			menuItemRef.current.style.left = `${x - 155}px`;
			menuItemRef.current.style.top = `${y + buttonHeight - 25}px`;
			document.body.appendChild(menuItemRef.current);
		} else if (
			menuItemRef.current &&
			menuItemRef.current.parentNode === document.body
		) {
			document.body.removeChild(menuItemRef.current);
		}
	}, [itemSelected, alwaysShowOptions]);

	const renderOptions = () => (
		<div
			className={`content-manager-dropdown-menu__lists  ${alwaysShowOptions ? 'content-manager-dropdown-menu--inline' : ''} ${
				suffix && `content-manager-dropdown-menu--list-${suffix}`
			}`}
			ref={menuItemRef}
		>
			<ul
				className={`${alwaysShowOptions ? 'content-manager-dropdown-menu__inline' : 'content-manager-more-options'}`}
			>
				{options.map((item, itemIndex) => {
					const handleItemClick = () => {
						setItemSelected(false);
						if ('button' === item.type) {
							return item?.onClick();
						}
						return null;
					};
					return (
						<React.Fragment key={itemIndex}>
							{item.action ? (
								<form
									className={`${alwaysShowOptions ? 'content-manager-dropdown-menu__inline-form' : 'content-manager-more-options__item'}`}
									action={item.action}
									method={item.method}
								>
									<Button
										preset="transparent"
										iconPosition="left"
										{...item}
										suffix={`${alwaysShowOptions ? 'inline' : 'block'}`}
									/>
									{item.hasBorder && (
										<hr className="content-manager-option-separator" />
									)}
								</form>
							) : (
								<li
									className={`${alwaysShowOptions ? 'content-manager-dropdown-menu__inline-form' : 'content-manager-more-options__item'}`}
								>
									<Button
										preset="transparent"
										iconPosition="left"
										{...item}
										onClick={handleItemClick}
										suffix={`${alwaysShowOptions ? 'inline' : 'block'}`}
									/>
									{item.hasBorder && (
										<hr className="content-manager-option-separator" />
									)}
								</li>
							)}
						</React.Fragment>
					);
				})}
			</ul>
		</div>
	);

	return (
		<>
			{!alwaysShowOptions && (
				<button
					className={`content-manager-dropdown-menu ${
						suffix && `content-manager-dropdown-menu--${suffix}`
					}`}
					type="button"
					ref={relativeTo}
					onClick={handleMenuToggle}
				>
					{iconClass ? (
						iconClass
					) : (
						<span
							className={`easy-content-manager-icon easy-content-manager-icon--${icon}`}
						></span>
					)}
				</button>
			)}
			{alwaysShowOptions
				? renderOptions()
				: itemSelected && createPortal(renderOptions(), document.body)}
		</>
	);
};

OptionMenu.propTypes = propTypes;
export default OptionMenu;
