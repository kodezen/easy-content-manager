import React, { useEffect, useRef } from 'react';
import { useSelector, useDispatch } from 'react-redux';
import { createPortal } from 'react-dom';
import { showNotification } from '@ECMRedux/Slices/notificationSlice/notificationSlice';
import { CloseButton } from '@chakra-ui/react';

import './styles.scss';

const Notification = () => {
	const notificationRef = useRef(null);
	const notification = useSelector((state) => state.notification);
	const dispatch = useDispatch();

	useEffect(() => {
		if (notification.showNotification && notificationRef.current) {
			const el = notificationRef.current;
			el.style.position = 'fixed';
			el.style.left = '50%';
			el.style.transform = 'translateX(-50%)';
			el.style.bottom = '50px';
		}
	}, [notification.showNotification]);

	useEffect(() => {
		if (notification.showNotification) {
			setTimeout(() => {
				closeHandler();
			}, 5000);
		}
	}, [notification.showNotification]);

	const closeHandler = () => {
		dispatch(
			showNotification({
				message: '',
				isShow: false,
				type: '',
			})
		);
	};

	const iconType = (type) => {
		switch (type) {
			case 'error':
				return (
					<span className="easy-content-manager-icon easy-content-manager-icon--notify-error" />
				);
			case 'info':
				return (
					<span className="easy-content-manager-icon easy-content-manager-icon--notify-information" />
				);
			case 'warning':
				return (
					<span className="easy-content-manager-icon easy-content-manager-icon--notify-warning" />
				);
			default:
				return (
					<span className="easy-content-manager-icon easy-content-manager-icon--notify-success" />
				);
		}
	};

	if (!notification.showNotification) return null;

	return (
		<>
			{notification?.showNotification &&
				createPortal(
					<div
						className={`ecm-notification ${
							notification.type &&
							`ecm-notification--${notification.type}`
						}`}
						ref={notificationRef}
					>
						<p className="ecm-notification__message">
							<span className="ecm-notification__message--icon">
								{iconType(notification.type)}
							</span>
							<span className="ecm-notification__message--text">
								{notification.message}
							</span>
						</p>

						<CloseButton
							color="inherit"
							_hover={{ color: 'var(--ecm-primary)' }}
							size="xs"
							onClick={closeHandler}
						/>
					</div>,
					document.body
				)}
		</>
	);
};

export default Notification;
