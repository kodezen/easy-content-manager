import { makeRequest } from '../utils/helper';
import { __ } from '@wordpress/i18n';

export const handleClick = (e) => {
	const hasError = document.querySelector('.content-manager-sub-title');
	const saveBtn = e.target;

	if (hasError) {
		e.preventDefault();
		e.stopPropagation();

		alert('Please fill all required fields.');

		if (saveBtn) {
			saveBtn.setAttribute('aria-disabled', 'true');
			saveBtn.disabled = true;
		}

		return false;
	}
	saveBtn?.removeAttribute('aria-disabled');
	saveBtn.disabled = false;
};

export const observerCallback = () => {
	const saveBtnClassic = document.querySelector('#publish');
	const saveBtnGutenberg = document.querySelector(
		'.editor-post-publish-button'
	);

	if (saveBtnClassic) {
		saveBtnClassic.removeEventListener('click', handleClick);
		saveBtnClassic.addEventListener('click', handleClick);
		return;
	}

	if (saveBtnGutenberg) {
		saveBtnGutenberg.removeEventListener('click', handleClick);
		saveBtnGutenberg.addEventListener('click', handleClick);
	}
};

export const handleRenderErrorText = (isGutenberg) => {
	const saveInputClassic = document.querySelector('#publish');
	const saveBtnGutenberg = document.querySelector(
		'.editor-post-publish-button'
	);
	const requiredFields = document.querySelectorAll('[required]');

	const validateFields = () => {
		let hasError = false;
		requiredFields.forEach((field) => {
			if (!field.value.trim()) {
				hasError = true;

				const existingError = field.parentNode.querySelector(
					'.content-manager-sub-title'
				);
				if (!existingError) {
					const errorMessage = document.createElement('span');
					errorMessage.className = 'content-manager-sub-title';
					errorMessage.style.color = 'red';
					errorMessage.textContent = __(
						'This field is required',
						'easy-content-manager'
					);
					field.parentNode.insertBefore(
						errorMessage,
						field.nextSibling
					);
				}
			} else {
				// Remove error if fixed
				if (existingError && existingError.parentNode) {
					existingError.parentNode.removeChild(existingError);
				}
			}
		});

		return !hasError;
	};
	// Gutenberg editor
	if (isGutenberg && saveBtnGutenberg) {
		saveBtnGutenberg.addEventListener('click', (e) => {
			if (!validateFields()) {
				e.preventDefault();
				e.stopPropagation();
				alert(
					__(
						'Please fill all required fields before publishing.',
						'easy-content-manager'
					)
				);
			}
		});
	}
	// Classic editor
	if (!isGutenberg && saveInputClassic) {
		saveInputClassic.addEventListener('click', (e) => {
			if (!validateFields()) {
				e.preventDefault();
				e.stopPropagation();
				alert(
					__(
						'Please fill all required fields before publishing.',
						'easy-content-manager'
					)
				);
			}
		});
	}
};

export const getFieldGroupsCustomFields = (
	meta,
	setCustomData,
	setLoading,
	setFieldGroupId
) => {
	const foundGrouup = Object.keys(meta).find((key) =>
		key.split('_').includes('fg')
	);
	if (!foundGrouup) return;

	setLoading(true);
	const group_id = foundGrouup?.split('_')[1];
	setFieldGroupId(group_id);
	makeRequest('get_field_groups', { group_id }).then(({ data: { data } }) => {
		setCustomData((prev) => {
			if (prev) return [...prev, ...data.custom_fields];
			return data.custom_fields;
		});
		setLoading(false);
	});
};

export function createSvgFromDataUri(dataUri) {
	try {
		const base64 = dataUri.split(',')[1];
		const svgString = atob(base64);
		const parser = new DOMParser();
		const svgDoc = parser.parseFromString(svgString, 'image/svg+xml');
		return svgDoc.documentElement;
	} catch (error) {
		console.error('Error decoding SVG:', error);
		return null;
	}
}

export const SettingsSvg = (props) => (
	<svg
		xmlns="http://www.w3.org/2000/svg"
		width="24"
		height="24"
		viewBox="0 0 24 24"
		fill="none"
		{...props}
	>
		<path
			d="M12 15.7498C9.93 15.7498 8.25 14.0698 8.25 11.9998C8.25 9.9298 9.93 8.2498 12 8.2498C14.07 8.2498 15.75 9.9298 15.75 11.9998C15.75 14.0698 14.07 15.7498 12 15.7498ZM12 9.7498C10.76 9.7498 9.75 10.7598 9.75 11.9998C9.75 13.2398 10.76 14.2498 12 14.2498C13.24 14.2498 14.25 13.2398 14.25 11.9998C14.25 10.7598 13.24 9.7498 12 9.7498Z"
			fill="#13191B"
		/>
		<path
			d="M15.21 22.1898C15 22.1898 14.79 22.1598 14.58 22.1098C13.96 21.9398 13.44 21.5498 13.11 20.9998L12.99 20.7998C12.4 19.7798 11.59 19.7798 11 20.7998L10.89 20.9898C10.56 21.5498 10.04 21.9498 9.42 22.1098C8.79 22.2798 8.14 22.1898 7.59 21.8598L5.87 20.8698C5.26 20.5198 4.82 19.9498 4.63 19.2598C4.45 18.5698 4.54 17.8598 4.89 17.2498C5.18 16.7398 5.26 16.2798 5.09 15.9898C4.92 15.6998 4.49 15.5298 3.9 15.5298C2.44 15.5298 1.25 14.3398 1.25 12.8798V11.1198C1.25 9.6598 2.44 8.4698 3.9 8.4698C4.49 8.4698 4.92 8.2998 5.09 8.0098C5.26 7.7198 5.19 7.2598 4.89 6.7498C4.54 6.1398 4.45 5.4198 4.63 4.7398C4.81 4.0498 5.25 3.4798 5.87 3.1298L7.6 2.1398C8.73 1.4698 10.22 1.8598 10.9 3.0098L11.02 3.2098C11.61 4.2298 12.42 4.2298 13.01 3.2098L13.12 3.0198C13.8 1.8598 15.29 1.4698 16.43 2.1498L18.15 3.1398C18.76 3.4898 19.2 4.0598 19.39 4.7498C19.57 5.4398 19.48 6.1498 19.13 6.7598C18.84 7.2698 18.76 7.7298 18.93 8.0198C19.1 8.3098 19.53 8.4798 20.12 8.4798C21.58 8.4798 22.77 9.6698 22.77 11.1298V12.8898C22.77 14.3498 21.58 15.5398 20.12 15.5398C19.53 15.5398 19.1 15.7098 18.93 15.9998C18.76 16.2898 18.83 16.7498 19.13 17.2598C19.48 17.8698 19.58 18.5898 19.39 19.2698C19.21 19.9598 18.77 20.5298 18.15 20.8798L16.42 21.8698C16.04 22.0798 15.63 22.1898 15.21 22.1898ZM12 18.4898C12.89 18.4898 13.72 19.0498 14.29 20.0398L14.4 20.2298C14.52 20.4398 14.72 20.5898 14.96 20.6498C15.2 20.7098 15.44 20.6798 15.64 20.5598L17.37 19.5598C17.63 19.4098 17.83 19.1598 17.91 18.8598C17.99 18.5598 17.95 18.2498 17.8 17.9898C17.23 17.0098 17.16 15.9998 17.6 15.2298C18.04 14.4598 18.95 14.0198 20.09 14.0198C20.73 14.0198 21.24 13.5098 21.24 12.8698V11.1098C21.24 10.4798 20.73 9.9598 20.09 9.9598C18.95 9.9598 18.04 9.5198 17.6 8.7498C17.16 7.9798 17.23 6.9698 17.8 5.9898C17.95 5.7298 17.99 5.4198 17.91 5.1198C17.83 4.8198 17.64 4.5798 17.38 4.4198L15.65 3.4298C15.22 3.1698 14.65 3.3198 14.39 3.7598L14.28 3.9498C13.71 4.9398 12.88 5.4998 11.99 5.4998C11.1 5.4998 10.27 4.9398 9.7 3.9498L9.59 3.7498C9.34 3.3298 8.78 3.1798 8.35 3.4298L6.62 4.4298C6.36 4.5798 6.16 4.8298 6.08 5.1298C6 5.4298 6.04 5.7398 6.19 5.9998C6.76 6.9798 6.83 7.9898 6.39 8.7598C5.95 9.5298 5.04 9.9698 3.9 9.9698C3.26 9.9698 2.75 10.4798 2.75 11.1198V12.8798C2.75 13.5098 3.26 14.0298 3.9 14.0298C5.04 14.0298 5.95 14.4698 6.39 15.2398C6.83 16.0098 6.76 17.0198 6.19 17.9998C6.04 18.2598 6 18.5698 6.08 18.8698C6.16 19.1698 6.35 19.4098 6.61 19.5698L8.34 20.5598C8.55 20.6898 8.8 20.7198 9.03 20.6598C9.27 20.5998 9.47 20.4398 9.6 20.2298L9.71 20.0398C10.28 19.0598 11.11 18.4898 12 18.4898Z"
			fill="#13191B"
		/>
	</svg>
);

export const ArrowUp = (props) => (
	<svg
		xmlns="http://www.w3.org/2000/svg"
		width="24"
		height="24"
		viewBox="0 0 24 24"
		fill="none"
		{...props}
	>
		<g clipPath="url(#clip0_1477_22881)">
			<path
				d="M11.5263 6.91796C11.8209 6.67765 12.2556 6.69512 12.5302 6.96972L20.0302 14.4697L20.082 14.5264C20.3223 14.8209 20.3048 15.2557 20.0302 15.5303C19.7556 15.8049 19.3209 15.8223 19.0263 15.582L18.9697 15.5303L11.9999 8.56054L5.03022 15.5303C4.73732 15.8232 4.26256 15.8232 3.96967 15.5303C3.67678 15.2374 3.67678 14.7626 3.96967 14.4697L11.4697 6.96972L11.5263 6.91796Z"
				fill="#13191B"
			/>
		</g>
		<defs>
			<clipPath id="clip0_1477_22881">
				<rect width="24" height="24" fill="white" />
			</clipPath>
		</defs>
	</svg>
);

export const ArrowDown = (props) => (
	<svg
		xmlns="http://www.w3.org/2000/svg"
		width="24"
		height="24"
		viewBox="0 0 24 24"
		fill="none"
		{...props}
	>
		<g clipPath="url(#clip0_1477_22878)">
			<path
				d="M19.0265 8.41796C19.3211 8.17765 19.7558 8.19512 20.0304 8.46972C20.305 8.74433 20.3225 9.17905 20.0821 9.47363L20.0304 9.53027L12.5304 17.0303C12.2558 17.3049 11.8211 17.3223 11.5265 17.082L11.4698 17.0303L3.96984 9.53027L3.91809 9.47363C3.67778 9.17905 3.69524 8.74433 3.96984 8.46972C4.24445 8.19512 4.67917 8.17765 4.97375 8.41796L5.03039 8.46972L12.0001 15.4394L18.9698 8.46972L19.0265 8.41796Z"
				fill="#13191B"
			/>
		</g>
		<defs>
			<clipPath id="clip0_1477_22878">
				<rect width="24" height="24" fill="white" />
			</clipPath>
		</defs>
	</svg>
);
