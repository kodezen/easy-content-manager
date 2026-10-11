import React, { useState } from 'react';
import { __ } from '@wordpress/i18n';
import { settingsTabs } from './helper';
import General from './General';
import Labels from './Labels';
import Permissions from './Permissions';;
import Visibility from './Visibility';
import { Flex } from '@chakra-ui/react';
import './styles.scss';

const AdvancedConfig = ({ disabled = false }) => {
	const [activeTabName, setActiveTabName] = useState('general');
	const [isError, setIsError] = useState(false);

	const renderSwitch = (urlPath) => {
		const components = {
			general: <General isError={isError} setIsError={setIsError} />,
			labels: <Labels isError={isError} setIsError={setIsError} />,
			visibility: (
				<Visibility isError={isError} setIsError={setIsError} />
			),
			permissions: (
				<Permissions isError={isError} setIsError={setIsError} />
			),
		};

		return components[urlPath] || components.general;
	};

	const handleAccordionToggle = (tabName) => {
		if (disabled) return;
		setActiveTabName((prevTabName) =>
			prevTabName === tabName ? '' : tabName
		);
	};

	const [tooltip, setTooltip] = useState({ visible: false, x: 0, y: 0 });

	const handleMouseMove = (e) => {
		if (!disabled) return;
		setTooltip({
			visible: true,
			x: e.clientX + 12,
			y: e.clientY + 12,
		});
	};

	const handleMouseLeave = () => {
		setTooltip({ ...tooltip, visible: false });
	};

	const tabsData = settingsTabs();

	return (
		<>
			<div
				className={`ecm--advanced-config ${disabled ? ' ecm--advanced-config--disabled' : ''}`}
				onMouseMove={handleMouseMove}
				onMouseLeave={handleMouseLeave}
			>
				<Flex alignItems="center" gap="2" padding="0 0 16px 0">
					<h3 className="ecm-heading-label">
						<span
							className={`easy-content-manager-icon easy-content-manager-icon--settings`}
						/>
						{__(' Advance Settings', 'easy-content-manager')}
					</h3>
				</Flex>

				<div className="ecm--advanced-config__accordion">
					{tabsData.map((tabItem) => (
						<div
							key={tabItem?.id}
							className={`ecm--advanced-config__accordion-item ecm--advanced-config__accordion-item--${activeTabName === tabItem?.name ? 'open' : ''}`}
						>
							<div
								className={`ecm--advanced-config__accordion-header${activeTabName === tabItem?.name ? ' ecm--advanced-config__accordion-header-active' : ''}`}
								onClick={() =>
									handleAccordionToggle(tabItem?.name)
								}
								role="button"
								tabIndex={disabled ? -1 : 0}
								aria-expanded={activeTabName === tabItem?.name}
								aria-controls={`panel-${tabItem?.name}`}
							>
								<span className="ecm--advanced-config__accordion-header-title">
									{tabItem?.title}
								</span>
								<span
									className={`easy-content-manager-icon easy-content-manager-icon--${activeTabName === tabItem?.name ? 'angle-up' : 'angle-down'}`}
								/>
							</div>

							<div
								id={`panel-${tabItem?.name}`}
								className={`ecm--advanced-config__accordion-panel ${activeTabName === tabItem?.name
										? 'ecm--advanced-config__accordion-panel--open'
										: ''
									}`}
							>
								{renderSwitch(tabItem?.name)}
							</div>
						</div>
					))}
				</div>
			</div>

			{disabled && tooltip.visible && (
				<div
					className="ecm--advanced-config--disabled-tooltip"
					style={{
						top: tooltip.y + 25,
						left: tooltip.x,
					}}
				>
					{__(
						'Please enable Advanced Configuration',
						'easy-content-manager'
					)}
					<br />
					{__(' to access these settings', 'easy-content-manager')}
				</div>
			)}
		</>
	);
};

export default AdvancedConfig;
