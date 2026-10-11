import React, { useState } from 'react';

import './styles.scss';

const ECMTooltip = ({
	children,
	content,
	position = 'top',
	showArrow = true,
	disabled = false,
	type = false,
	width = 'auto',
	noWrap = false,
}) => {
	const [visible, setVisible] = useState(false);
	const shouldShow = !disabled && content;

	return (
		<div
			className="ecm-tooltip"
			onMouseEnter={() => shouldShow && setVisible(true)}
			onMouseLeave={() => shouldShow && setVisible(false)}
		>
			{type === 'info' && (
				<span className="easy-content-manager-icon easy-content-manager-icon--info-circle" />
			)}
			{type === 'ques' && (
				<span className="easy-content-manager-icon easy-content-manager-icon--questions" />
			)}
			{type === 'pro' && (
				<span className="easy-content-manager-icon easy-content-manager-icon--lock-light" />
			)}
			{children}
			{shouldShow && visible && (
				<div
					className={`ecm-tooltip__box ecm-tooltip__box--${position}`}
					style={{
						width,
						whiteSpace: noWrap ? 'nowrap' : 'normal',
						overflowWrap: 'break-word',
						wordBreak: 'break-word',
					}}
				>
					{content}
					{showArrow && (
						<div
							className={`ecm-tooltip__arrow ecm-tooltip__arrow--${position}`}
						/>
					)}
				</div>
			)}
		</div>
	);
};

export default ECMTooltip;
