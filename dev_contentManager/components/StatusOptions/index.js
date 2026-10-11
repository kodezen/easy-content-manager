import React, { useEffect, useRef, useState } from 'react';
import PropTypes from 'prop-types';
import { __ } from '@wordpress/i18n';
import { createPortal } from 'react-dom';
import './styles.scss';

const propTypes = {
	children: PropTypes.object,
	value: PropTypes.string,
	options: PropTypes.object,
	onChangeHandler: PropTypes.func,
};

const StatusOptions = (props) => {
	const {
		value = 'pending',
		options = {},
		onChangeHandler = () => {},
	} = props;

	const [isOpenDropdown, setOpenDropdown] = useState(false);

	const statusLabel = {
		publish: __('Published', 'easy-content-manager'),
		pending: __('Pending', 'easy-content-manager'),
		draft: __('Draft', 'easy-content-manager'),
		trash: __('Trash', 'easy-content-manager'),
		completed: __('Approved', 'easy-content-manager'),
		approved: __('Approve', 'easy-content-manager'),
		processing: __('Processing', 'easy-content-manager'),
		cancel: __('Cancel', 'easy-content-manager'),
		private: __('Private', 'easy-content-manager'),
	};

	const menuItemRef = useRef(null);
	const relativeTo = useRef(null);

	const handleClick = (e) => {
		if (menuItemRef?.current && !menuItemRef?.current?.contains(e.target)) {
			setOpenDropdown(false);
		}
	};

	const handleMenuToggle = () => {
		setOpenDropdown(!isOpenDropdown);
	};

	useEffect(() => {
		document.addEventListener('mousedown', handleClick);
		return () => document.removeEventListener('mousedown', handleClick);
	}, []);

	useEffect(() => {
		if (isOpenDropdown && relativeTo.current) {
			const rect = relativeTo.current.getBoundingClientRect();
			const x = rect.left + window.pageXOffset;
			const y = rect.top + window.pageYOffset;
			const buttonHeight = relativeTo.current.offsetHeight;
			menuItemRef.current.style.position = 'absolute';
			menuItemRef.current.style.left = `${x - 50}px`;
			menuItemRef.current.style.top = `${y + buttonHeight - 27}px`;
			document.body.appendChild(menuItemRef.current);
		} else if (
			relativeTo.current &&
			relativeTo.current.parentNode === document.body
		) {
			document.body.removeChild(menuItemRef.current);
		}
	}, [isOpenDropdown, relativeTo]);

	return (
		<>
			<button
				className={`content-manager-dropdown-option content-manager-dropdown-option--${value}`}
				type="button"
				ref={relativeTo}
				onClick={handleMenuToggle}
			>
				<span className="label">{statusLabel[value]}</span>
				<span
					className={`easy-content-manager-icon easy-content-manager-icon--angle-down`}
				/>
			</button>
			{createPortal(
				<div
					className="content-manager-dropdown-option__lists"
					ref={menuItemRef}
				>
					{isOpenDropdown && (
						<ul className="content-manager-more-status-options">
							{options.items.map((item, index) => (
								<li
									className={`content-manager-more-status-options__item content-manager-more-status-options__item--${
										item.value
									} ${item.value === value && 'active'}`}
									role="presentation"
									onClick={() => {
										onChangeHandler(item.value);
										setOpenDropdown(false);
									}}
									key={index}
								>
									{item.label}
								</li>
							))}
						</ul>
					)}
				</div>,
				document.body
			)}
		</>
	);
};

StatusOptions.propTypes = propTypes;
export default StatusOptions;
