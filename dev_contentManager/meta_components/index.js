import React, { useEffect, useState } from 'react';
import { __ } from '@wordpress/i18n';
import { useSelect, useDispatch } from '@wordpress/data';
import {
	ArrowDown,
	ArrowUp,
	createSvgFromDataUri,
	handleRenderErrorText,
	SettingsSvg,
} from './helper';
import {
	admin_url,
	API,
	capitalizeFirstLetter,
	getCustomData,
	namespace,
	rest_url,
	varColors,
} from '../utils/helper';
import { SequentialFieldRenderer } from '../components/RenderField/helper';
import closeSvgData from '../../assets/images/close.svg';
import { Flex } from '@chakra-ui/react';

const isGutenberg = document.body.classList.contains('block-editor-page');

const MetaField = () => {
	const [savedMeta, setSavedMeta] = useState({});
	const [isOpen, setIsOpen] = useState(false);
	const [sidebarWidth, setSidebarWidth] = useState(0);

	const gutenbergMeta = isGutenberg
		? useSelect(
				(select) =>
					select('core/editor').getEditedPostAttribute('meta'),
				[]
			)
		: null;

	const editPost = isGutenberg ? useDispatch('core/editor')?.editPost : null;

	const postType = document.getElementById('post_type')?.value;
	const postId = document.getElementById('post_ID')?.value;
	const meta = isGutenberg ? gutenbergMeta : savedMeta;
	const customData = getCustomData(postType);

	const handleSaveFieldValue = async (field_name, value) => {
		let newFieldName = '';
		if (field_name.startsWith('meta.'))
			newFieldName = field_name.replace('meta.', '');
		else newFieldName = field_name;

		if (isGutenberg) {
			editPost({ meta: { ...gutenbergMeta, [newFieldName]: value } });
			handleRenderErrorText(isGutenberg);
		} else {
			try {
				await API.post(`${rest_url}${namespace}${postType}/${postId}`, {
					meta: { ...savedMeta, [newFieldName]: value },
				});
				handleRenderErrorText(isGutenberg);
			} catch (error) {
				console.error('Failed to save meta field:', error);
			}
		}
	};

	const updateContainerWidth = () => {
		const resizableDiv = document.getElementById('resizableDiv');
		if (!resizableDiv) return;

		const sidebar = document.querySelector(
			'.interface-interface-skeleton__sidebar'
		);
		const isCollapsed = sidebar?.classList.contains('is-collapsed');

		let newWidth = '100%';

		if (sidebar && !isCollapsed) {
			const sidebarRect = sidebar.getBoundingClientRect();
			newWidth = `calc(100% - ${sidebarRect.width}px)`;
			setSidebarWidth(sidebarRect.width);
		} else {
			setSidebarWidth(0);
		}

		resizableDiv.style.width = newWidth;
	};

	useEffect(() => {
		if (isGutenberg) return;
		const getClassicMeta = async () => {
			try {
				const response = await API.get(
					`${rest_url}${namespace}${postType}`
				);
				const { meta } = response.data[0] || {};
				setSavedMeta(meta || {});
			} catch (error) {
				console.error('Error fetching meta:', error.message);
			}
		};
		getClassicMeta();
	}, [isGutenberg]);

	useEffect(() => {
		const observeSidebar = () => {
			const sidebar = document.querySelector(
				'.interface-interface-skeleton__sidebar'
			);
			const body = document.body;

			if (!sidebar) return;

			updateContainerWidth();

			const resizeObserver = new ResizeObserver(() => {
				updateContainerWidth();
			});

			const mutationObserver = new MutationObserver((mutations) => {
				mutations.forEach((mutation) => {
					if (
						mutation.type === 'attributes' &&
						(mutation.attributeName === 'class' ||
							mutation.attributeName === 'style')
					) {
						setTimeout(updateContainerWidth, 50);
					}
				});
			});

			resizeObserver.observe(sidebar);

			mutationObserver.observe(sidebar, {
				attributes: true,
				attributeFilter: ['class', 'style'],
			});

			mutationObserver.observe(body, {
				attributes: true,
				attributeFilter: ['class'],
			});

			window.addEventListener('resize', updateContainerWidth);

			return () => {
				resizeObserver.disconnect();
				mutationObserver.disconnect();
				window.removeEventListener('resize', updateContainerWidth);
			};
		};

		const timer = setTimeout(observeSidebar, 100);

		return () => clearTimeout(timer);
	}, []);

	useEffect(() => {
		const closeSvg = createSvgFromDataUri(closeSvgData);
		closeSvg.setAttribute('width', '16');
		closeSvg.setAttribute('height', '16');

		const path = closeSvg.querySelector('path');
		if (path) {
			path.setAttribute('stroke', '#FF4D4D');
		}

		const observer = new MutationObserver(() => {
			const spanElement = document.querySelectorAll(
				'span.easy-content-manager-icon--close'
			);
			if (spanElement.length > 0 && closeSvg) {
				spanElement.forEach((element) => {
					element.parentNode.replaceChild(
						closeSvg.cloneNode(true),
						element
					);
				});
			}
		});

		observer.observe(document.body, { childList: true, subtree: true });
		return () => observer.disconnect();
	}, []);

	useEffect(() => {
		const resizableDiv = document.getElementById('resizableDiv');
		const editorVisual = document.querySelector('.editor-visual-editor');
		if (!resizableDiv || !editorVisual) return;

		if (isOpen) {
			resizableDiv.classList.add(
				'content-manager-meta-field-container--open'
			);
		} else {
			resizableDiv.classList.remove(
				'content-manager-meta-field-container--open'
			);
		}

		setTimeout(updateContainerWidth, 50);
	}, [isOpen]);

	const handleCollapse = () => setIsOpen(!isOpen);

	const handleNavigateToFieldGroup = () =>
		(window.location.href = `${admin_url}admin.php?page=easy-content-manager-field-groups`);

	useEffect(() => {
		if (isGutenberg) return;

		const interval = setInterval(() => {
			const editorHeaderBtns = document.getElementById(
				'field-editor-header'
			);
			if (editorHeaderBtns && editorHeaderBtns.parentNode) {
				editorHeaderBtns.parentNode.removeChild(editorHeaderBtns);
				clearInterval(interval);
			}
		}, 100);

		return () => clearInterval(interval);
	}, [isGutenberg]);

	const classicEditorClass = {
		background: '#fff',
		padding: '24px',
		border: '1px solid #c3c4c7',
		borderRadius: '4px',
	};

	return (
		<div className="field-editor-container">
			<style>{varColors}</style>
			<div
				id="field-editor-header"
				className="field-editor-header content-manager-flex"
			>
				<span className="content-manager-title">
					{__(
						capitalizeFirstLetter(postType),
						'easy-content-manager'
					)}
				</span>
				<Flex gap="12px" alignItems="center">
					<SettingsSvg
						onClick={handleNavigateToFieldGroup}
						className="field-editor-header--icon-settings"
					/>
					{isOpen ? (
						<ArrowUp
							onClick={handleCollapse}
							className={`field-editor-header--icon-arrow-up`}
						/>
					) : (
						<ArrowDown
							onClick={handleCollapse}
							className={`field-editor-header--icon-arrow-down`}
						/>
					)}
				</Flex>
			</div>

			<div
				className={`field-editor-fields`}
				style={isGutenberg ? undefined : classicEditorClass}
			>
				<SequentialFieldRenderer
					customData={customData}
					values={isGutenberg ? meta : savedMeta}
					setFieldValue={handleSaveFieldValue}
				/>
			</div>
		</div>
	);
};

export default MetaField;
