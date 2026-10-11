import React, { useEffect, useRef } from 'react';
import { Button } from '@chakra-ui/react';
import { __ } from '@wordpress/i18n';
import { createPortal } from 'react-dom';
import PropTypes from 'prop-types';
import { ecm_app_slug } from '@ECMUtils/helper';
import './styles.scss';

const propTypes = {
	actionButtons: PropTypes.array,
	itemsLength: PropTypes.number,
	isActionSelected: PropTypes.object,
	confirmHandler: PropTypes.function,
	resetHandler: PropTypes.function,
};

const SnackbarAction = ({
	actionButtons,
	itemsLength,
	isActionSelected,
	confirmHandler,
	resetHandler,
}) => {
	const snackbarRef = useRef(null);
	const targetElement =
		document.querySelector('#contentManagerWrap') ||
		document.querySelector(`#${ecm_app_slug}-wrap`);

	useEffect(() => {
		if (itemsLength && targetElement) {
			snackbarRef.current.style.position = 'fixed';
			snackbarRef.current.style.left = `40%`; // Adjust left position as needed
			snackbarRef.current.style.bottom = `50px`; // Place it at the bottom
			document.body.appendChild(snackbarRef.current);
		} else if (
			snackbarRef.current &&
			snackbarRef.current.parentNode === document.body
		) {
			document.body.removeChild(snackbarRef.current);
		}

		return () => {
			if (
				snackbarRef.current &&
				snackbarRef.current.parentNode === document.body
			) {
				document.body.removeChild(snackbarRef.current);
			}
		};
	}, []);

	return (
		<>
			{itemsLength &&
				createPortal(
					<div
						className="easy-content-manager-snackbar-action"
						ref={snackbarRef}
					>
						<Button bg="transparent" onClick={resetHandler}>
							<span className="easy-content-manager-icon easy-content-manager-icon--close" />
						</Button>

						{isActionSelected?.value ? (
							<div className="easy-content-manager-snackbar-action__after">
								<div className="easy-content-manager-snackbar-action__left">
									{isActionSelected?.message}
								</div>
								<div className="easy-content-manager-snackbar-action__left">
									<Button
										onClick={resetHandler}
										bg="transparent"
										color="var(--ecm-font-color)"
										borderColor="var(--ecm-border-color)"
										borderWidth="1px"
									>
										{__('Cancel', 'easy-content-manager')}
									</Button>
									<Button
										onClick={() => confirmHandler(true)}
										bg="var(--ecm-primary)"
									>
										{__('Confirm', 'easy-content-manager')}
									</Button>
								</div>
							</div>
						) : (
							<div className="easy-content-manager-snackbar-action__before">
								<div className="easy-content-manager-snackbar-action__left">
									<div className="easy-content-manager-snackbar-action__items-length">
										<span className="">{itemsLength}</span>
										<p>
											{__(
												'Items selected',
												'easy-content-manager'
											)}
										</p>
									</div>
								</div>
								<div className="easy-content-manager-snackbar-action__right">
									{actionButtons.map(
										(actionButton, index) => (
											<Button
												key={index}
												bg="var(--ecm-warning)"
												_hover={{
													bg: '#C53C32',
												}}
												{...actionButton}
											>
												{actionButton?.label}
											</Button>
										)
									)}
								</div>
							</div>
						)}
					</div>,
					document.body
				)}
		</>
	);
};

SnackbarAction.propTypes = propTypes;
export default SnackbarAction;
