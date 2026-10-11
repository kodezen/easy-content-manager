import { showNotification } from '@ECMRedux/Slices/notificationSlice/notificationSlice';
import { __ } from '@wordpress/i18n';
import axios from 'axios';
import { isArrayLikeObject } from 'lodash';
import { useLocation } from 'react-router-dom';

export const {
	admin_menu_lists,
	ecm_app_slug,
	admin_url,
	ajaxurl,
	current_user_id,
	current_user_roles,
	current_user_can,
	ecm_nonce,
	is_admin,
	is_premium: is_pro,
	menu,
	is_rtl,
	namespace,
	nonce,
	plugin_root_path,
	toplevel_menu_title,
	plugin_root_url,
	rest_url,
	route_path,
	site_url,
	toplevel_menu_icon_url,
	post_types,
	field_groups,
	color_theme_mode,
	ecm_settings_slugs,
	current_post_id,
	addons,
	upvote_settings,
	svg_icons,
	frontend_submission,
	frontend_submission_settings,
} = window.EasyContentManagerGlobal;

export const API = axios.create({
	baseURL: rest_url,
	headers: {
		'content-type': 'application/json',
		'X-WP-Nonce': nonce,
	},
});

export const renderCurrencyWithPrice = (
	value,
	symbol = true,
	withoutCurrency = false
) => {
	const formattedNumber = new Intl.NumberFormat('en-US', {
		minimumFractionDigits: currency_options.currency_decimal_limit,
		maximumFractionDigits: currency_options.currency_decimal_limit,
	});

	if (!symbol && !withoutCurrency) {
		return `${formattedNumber.format(value)} ${currency_options.currency}`;
	}

	if (withoutCurrency) {
		return `${formattedNumber.format(value)}`;
	}

	switch (currency_options.currency_position) {
		case 'left':
			return `${currency_options.currency_symbol
				}${formattedNumber.format(value)}`;
		case 'right':
			return `${formattedNumber.format(value)}${currency_options.currency_symbol
				}`;

		default:
			return `${currency_options.currency_symbol
				}${formattedNumber.format(value)}`;
	}
};

export const renderPaymentMethod = (type) => {
	const methods = {
		bank_transfer: __('"Bank Transfer"', 'storeengine'),
		check_payment: __('"Check Payment"', 'storeengine'),
		cash_on_delivery: __('Cash On Delivery', 'storeengine'),
		paypal: __('Paypal', 'storeengine'),
		stripe: __('Stripe', 'storeengine'),
	};

	return methods[type];
};

export const modalFullWidthStyles = {
	overlay: {
		background: 'rgba(35, 40, 45, 0.62)',
		zIndex: 9999,
	},
	content: {
		top: '50%',
		left: '50%',
		right: 'auto',
		bottom: 'auto',
		width: '50%',
		marginRight: '-50%',
		padding: 0,
		transform: 'translate(-50%, -50%)',
	},
};

/**
 * Make AxiosError object.
 *
 * @param  message
 * @param  response
 * @param  code
 * @return {AxiosError}
 */
const makeAxiosError = (message, response, code = 'UNKNOWN') => {
	return new AxiosError(
		message,
		'ERR_' + (code ? code : 'UNKNOWN'),
		response.config,
		response.request,
		response
	);
};

/**
 * Process ajax error data and throw error.
 *
 * @param  data
 * @param  response
 *
 * @throws {Error|AxiosError}
 */
const processAjaxError = (data, response) => {
	if ('string' === typeof data) {
		throw new Error(data);
	}

	if (data && 'object' === typeof data && data?.message) {
		const error = new Error(data.message);
		if (data?.code) error.code = data.code;

		throw error;
	}

	if (
		data &&
		Array.isArray(data) &&
		data.length &&
		data[0].code &&
		data[0].message
	) {
		if (1 === data.length) {
			const error = new Error(data[0].message);
			error.code = data[0].code;

			throw error;
		}

		throw makeAxiosError(
			__(
				'Something went wrong. Please try again after sometime.',
				'zenboards'
			),
			response
		);
	}

	throw new Error(
		__(
			'Something went wrong. Please try again after sometime.',
			'zenboards'
		)
	);
};

export const generateSlug = (value) => {
	return value
		.toLowerCase()
		.replace(/\s+/g, '-')
		.replace(/[^a-z0-9-]/g, '');
};

export const generatePostType = (value) => {
	return value
		.toLowerCase()
		.replace(/\s+/g, '_')
		.replace(/[^a-z0-9-]/g, '_');
};

export const generatePluralName = (value) => {
	if (!value) return '';

	const word = value.trim().toLowerCase();

	if (word.endsWith('es') || word.endsWith('ies') || word.endsWith('s'))
		return word;

	if (word.endsWith('y') && !/[aeiou]y$/.test(word)) {
		return word.slice(0, -1) + 'ies';
	} else if (
		word.endsWith('s') ||
		word.endsWith('sh') ||
		word.endsWith('ch') ||
		word.endsWith('x') ||
		word.endsWith('z')
	) {
		return word + 'es';
	}
	return word + 's';
};

export const generateRandomSlug = (length = 3) => {
	return (
		Math.random().toString(20).substr(2, length) +
		new Date().getMilliseconds()
	);
};

export const copyToClipboard = async (text) => {
	try {
		await navigator.clipboard.writeText(text);
	} catch (err) {
		const tempInput = document.createElement('input');
		tempInput.value = text;
		document.body.appendChild(tempInput);
		tempInput.select();
		document.execCommand('copy');
		document.body.removeChild(tempInput);
	}
};

export const prepare_term_data = (terms, parentId = 0) => {
	if (!terms) {
		return [];
	}
	const data = [];
	terms.forEach((term) => {
		if (Number(term.parent) === Number(parentId)) {
			term.children = prepare_term_data(terms, term.id);
			data.push(term);
		}
	});
	return data;
};

export const getTagSuggestions = (data) => {
	return (
		data &&
		data.reduce(function (acc, item) {
			acc.push(item.name);
			return acc;
		}, [])
	);
};

export const prepareSavedTags = (data, values) => {
	if (values && data && data.length > 0) {
		return data.reduce(function (acc, item) {
			if (values.includes(item.id)) {
				acc.push(item.name);
				return acc;
			}
			return acc;
		}, []);
	}
	return [];
};

export const IsJsonString = (str) => {
	try {
		JSON.parse(str);
	} catch (e) {
		return false;
	}
	return true;
};

export const getUserInitialValues = (data) => {
	if (data) {
		const {
			first_name,
			last_name,
			profile_photo,
			cover_photo,
			phone_number,
			designation,
			bio,
			website_url,
			github_url,
			facebook_url,
			twitter_url,
			linkedin_url,
		} = data?.storeengine_meta;
		return {
			first_name,
			last_name,
			storeengine_profile_photo: profile_photo,
			storeengine_cover_photo: cover_photo,
			storeengine_phone_number: phone_number,
			storeengine_profile_designation: designation,
			storeengine_profile_bio: bio,
			storeengine_website_url: website_url,
			storeengine_github_url: github_url,
			storeengine_facebook_url: facebook_url,
			storeengine_twitter_url: twitter_url,
			storeengine_linkedin_url: linkedin_url,
		};
	}
};

export const generateUserName = (item) => {
	if (item?.first_name || item?.last_name) {
		return item.first_name + ' ' + item.last_name;
	}
	return item?.user_nicename;
};

export const makeRequest = async (
	action,
	payload = {},
	isRaw = false,
	suffix = ''
) => {
	try {
		let form_data = new FormData(); // eslint-disable-line
		form_data.append('action', `easy-content-manager${suffix}/${action}`);
		if (!payload.security) {
			form_data.append('security', ecm_nonce);
		}
		Object.entries(payload).forEach(([key, value]) => {
			if (!isRaw && typeof value === 'object' && value !== null) {
				form_data.append(key, JSON.stringify(value));
			} else {
				form_data.append(key, value);
			}
		});
		const response = await axios.post(ajaxurl, form_data);
		return response;
	} catch (error) {
		// processAjaxError( error, response );;
		notification(
			error?.response?.data?.data?.message ??
			error?.response?.data?.message,
			'error'
		);
	}
};

export const handleSliceError = (thunkAPI, error) => {
	thunkAPI.dispatch(
		showNotification({
			message: error?.message || error,
			isShow: true,
			type: 'error',
		})
	);
	return thunkAPI.rejectWithValue(error.message);
};

export const useQuery = () => {
	return new URLSearchParams(useLocation().search);
};

export const debounce = (callback, wait) => {
	let timeout;
	return (...args) => {
		clearTimeout(timeout);
		timeout = setTimeout(function () {
			callback.apply(this, args);
		}, wait);
	};
};

/**
 *
 * @param {'error'|'info'|'warning'|'success'} type
 * @return {string}
 */
const getIconClass = (type) => {
	switch (type) {
		case 'success':
			return 'notify-success';
		case 'warning':
			return 'notify-warning';
		case 'error':
			return 'notify-error';
		case 'info':
		default:
			return 'notify-information';
	}
};

/**
 * Determine if a value is an Array
 *
 * @param {Object} val The value to test
 *
 * @return {boolean} True if value is an Array, otherwise false
 */
export const { isArray } = Array;

/**
 * Shows Notification toast.
 *
 * @param {string|[{code:string,message:string}]|{code:string,message:string}} message Message to show.
 * @param {'error'|'info'|'warning'|'success'}                                 [type]  [Optional] Notification type. Default type is `info`
 *
 * @return void
 */
export const notification = (message, type = 'info') => {
	let timer = null;
	let closeIn = 5000;

	if (!message) {
		return;
	}

	type = ['success', 'warning', 'error', 'info'].includes(type)
		? type
		: 'info';

	const closeHandler = (e) => {
		e && e.preventDefault();
		notificationDiv.remove();
		if (timer) {
			clearInterval(timer);
			timer = null;
		}
	};

	const checkInterval = () => {
		closeIn -= 1000;
		if (closeIn <= 0) {
			closeHandler();
		}
	};

	const notificationDiv = document.createElement('div');

	notificationDiv.className = `ecm-notification ecm-notification--dashboard ecm-notification--${type}`;
	notificationDiv.style.boxShadow = '3px 3px 8px -2px rgba(29, 35, 39,0.63)';
	notificationDiv.setAttribute('aria-live', 'polite');
	notificationDiv.onmouseenter = () => clearInterval(timer);
	notificationDiv.onmouseleave = () =>
		(timer = setInterval(checkInterval, 1000));

	const messageParagraph = document.createElement('div');
	messageParagraph.className = 'ecm-notification__message';
	messageParagraph.style.gap = '10px';

	if (isArray(message) && message[0].code && message[0].message) {
		if (message.length === 1) {
			message = message[0].message;
		} else {
			const errors = [...message];
			message =
				'<ul class="content-manager-m-0" style="padding-left:1rem;">';
			for (const err of errors) {
				message += `<li><label class="error-msg" for="${err.code}">${err.message}</label></li>`;
			}

			message += '</ul>';
		}
	} else if (message?.error?.message) {
		message = message?.error.message;
	}

	const icon = `<span class="easy-content-manager-icon easy-content-manager-icon--${getIconClass(
		type
	)}" style="font-size:20px"></span>`;

	if (
		message?.includes('<') &&
		(message?.includes('</') || message?.includes('/>'))
	) {
		message = icon + message;
	} else {
		message =
			icon +
			'<span class="ecm-notification__message content-manager-mr-12">' +
			message +
			'</span>';
	}

	messageParagraph.innerHTML = message;

	notificationDiv.appendChild(messageParagraph);

	const closeButton = document.createElement('button');
	closeButton.className =
		'content-manager-btn content-manager-btn--bg-transparent';
	closeButton.style.background = 'transparent';
	closeButton.style.position = 'absolute';
	closeButton.style.top = '18px';
	closeButton.style.right = '16px';
	closeButton.style.padding = 0;
	closeButton.setAttribute('data-suffix', 'close-notification');
	closeButton.setAttribute('aria-label', __('Close', 'storeengine'));
	closeButton.innerHTML = `<span class="easy-content-manager-icon easy-content-manager-icon--close" style="display:flex;" aria-hidden="true"></span>`;
	closeButton.onclick = closeHandler;

	notificationDiv.appendChild(closeButton);

	timer = setInterval(checkInterval, 1000);

	document.body.appendChild(notificationDiv);

	try {
		const links = document.querySelectorAll('.ecm-notification__message a');
		for (const idx in links) {
			if (links[idx].href && '#' !== links[idx].href) {
				links[idx].target = '_blank';
			}
		}
	} catch (er) { }
};


/**
 * Adds a query parameter to a URL.
 *
 * @param {string} url - The URL to add the parameter to.
 * @param {string} key - The key of the parameter to add.
 * @param {string} value - The value of the parameter to add.
 * @returns {string} The URL with the added parameter.
 */

export const addParam = (url, key, value) => {
	if (value == null || value === '') return url;
	const separator = url.includes('?') ? '&' : '?';
	return `${url}${separator}${key}=${value}`;
};

export const runUploader = (config = {}, isMulti = false) => {
	return new Promise((resolve) => {
		let frame; // eslint-disable-line
		// If the media frame already exists, reopen it.
		if (frame) {
			frame.open();
			return;
		}

		// Create a new media frame
		frame = wp.media({
			title: __('Select or Upload Media', 'easy-content-manager'),
			button: {
				text: __('Use this media', 'easy-content-manager'),
			},
			multiple: isMulti, // Set to true to allow multiple files to be selected
			...config,
		});

		// When an image is selected in the media frame...
		frame.on('select', function () {
			const selection = frame.state().get('selection');
			const selected = isMulti
				? selection.toArray().map((item) => item.toJSON())
				: selection.first().toJSON();
			resolve(selected);
		});
		// Finally, open the modal on click
		frame.open();
	});
};

export const sliceString = (text, length = 20, more = '...') => {
	if (!text || text.length < length) {
		return text;
	}
	return text.slice(0, length).replace(/(^[\s]+|[\s]+$)/g, '') + more;
};

export const hideHtmlTags = (html) => {
	const div = document.createElement('div');
	div.innerHTML = html;
	return div.textContent || div.innerText || '';
};

export const renderError = (e) => {
	notification(
		e?.response?.data?.message ? e?.response?.data?.message : e?.message,
		'error'
	);
};

export const getTranslatedStatus = (status) => {
	const statusLabel = {
		publish: __('Published', 'storeengine'),
		submitted: __('Submitted', 'storeengine'),
		pending: __('Pending', 'storeengine'),
		draft: __('Draft', 'storeengine'),
		trash: __('Trash', 'storeengine'),
		completed: __('Completed', 'storeengine'),
		approved: __('Approve', 'storeengine'),
		processing: __('Processing', 'storeengine'),
		cancel: __('Cancel', 'storeengine'),
		private: __('Private', 'storeengine'),
		pass: __('Pass', 'storeengine'),
		passed: __('Passed', 'storeengine'),
		success: __('Success', 'storeengine'),
		answered: __('Answered', 'storeengine'),
		active: __('Active', 'storeengine'),
		expired: __('Expired', 'storeengine'),
		running: __('Running', 'storeengine'),
		rejected: __('Rejected', 'storeengine'),
		failed: __('Failed', 'storeengine'),
		paid: __('Paid', 'storeengine'),
		converted: __('Converted', 'storeengine'),
	};

	return statusLabel[status];
};

export const capitalizeFirstLetter = (string = '') => {
	if (string?.includes('-') || string?.includes('_')) {
		const newString = string.replace(/[-_]/g, ' ');
		return newString.charAt(0).toUpperCase() + newString.slice(1);
	}
	return string.charAt(0).toUpperCase() + string.slice(1);
};

export const numberToRoman = (num) => {
	const lookup = {
		M: 1000,
		CM: 900,
		D: 500,
		CD: 400,
		C: 100,
		XC: 90,
		L: 50,
		XL: 40,
		X: 10,
		IX: 9,
		V: 5,
		IV: 4,
		I: 1,
	};
	let roman = '';
	for (const i in lookup) {
		const q = Math.floor(num / lookup[i]);
		num -= q * lookup[i];
		roman += i.repeat(q);
	}
	return roman;
};

export const resetStateToFalse = (state) => {
	return Object.keys(state).reduce((acc, key) => {
		acc[key] = false;
		return acc;
	}, {});
};

export const replaceTableRowItemWhitSpace = (string) => {
	if (string) {
		return string;
	}
	return '-';
};

export const formatTime = (date, format = 'HH:mm') => {
	if (!date || isNaN(date.getTime())) return null;

	let hours = date.getHours();
	const minutes = String(date.getMinutes()).padStart(2, '0');
	const seconds = String(date.getSeconds()).padStart(2, '0');

	if (format.includes('hh')) {
		const ampm = hours >= 12 ? 'PM' : 'AM';
		hours = hours % 12 || 12;
		return format
			.replace('hh', String(hours).padStart(2, '0'))
			.replace('mm', minutes)
			.replace('ss', seconds)
			.replace('A', ampm);
	}

	return format
		.replace('HH', String(hours).padStart(2, '0'))
		.replace('mm', minutes)
		.replace('ss', seconds);
};

export const formatDate = (date, format = 'MM/DD/YYYY') => {
	if (!date || isNaN(date.getTime())) return null;

	const month = String(date.getMonth() + 1).padStart(2, '0');
	const day = String(date.getDate()).padStart(2, '0');
	const year = date.getFullYear();

	return format.replace('YYYY', year).replace('MM', month).replace('DD', day);
};

export const detectValueType = (value) => {
	if (typeof value !== 'string') {
		if (isArray(value)) return 'array';
		else return typeof value;
	}

	if (!isNaN(value) && value.trim() !== '') return 'number';

	const dateRegex =
		/^(?:\d{4}-\d{2}-\d{2}|(?:0?[1-9]|1[0-2])[/-](0?[1-9]|[12][0-9]|3[01])[/-]\d{4})$/;
	if (dateRegex.test(value)) return 'date';

	const timeRegex =
		/^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])? ?([APap][Mm])?$/;
	if (timeRegex.test(value)) return 'time';

	const checkboxValues = ['true', 'false', 'yes', 'no', 'on', 'off'];
	if (checkboxValues.includes(value.toLowerCase())) return 'checkbox';

	return 'text';
};

export const formatMetaValue = (metaValue, detectedType) => {
	if (metaValue == null) return '';

	if (metaValue === '') return '';

	switch (detectedType) {
		case 'date':
			return formatDate(metaValue);
		case 'time':
			return formatTime(metaValue);
		case 'number':
			return Number(metaValue);
		case 'checkbox':
		case 'file':
		case 'image':
		case 'video':
		case 'switch':
			return String(metaValue).toLowerCase();
		case 'url':
		case 'email':
		case 'rich-text':
		case 'textarea':
		case 'password':
		case 'gallery':
		case 'icon':
		case 'map':
		case 'oembed':
		case 'select':
		case 'date-time':
		case 'post-object':
			return String(metaValue);
		case 'text':
			const metaValueType = detectValueType(metaValue);

			switch (metaValueType) {
				case 'object':
					return metaValue?.value;
				case 'array':
					return metaValue;
				default:
					return String(metaValue);
			}
		default:
			return String(metaValue);
	}
};

export const convertStringTounderScore = (string) => {
	if (!string) return null;
	const newString = string.replace(/[- ]/g, '_');
	return newString.toLowerCase();
};

export const getPostType = (post_type) => {
	switch (post_type) {
		case 'post':
			return 'posts';
		case 'page':
			return 'pages';
		default:
			return post_type;
	}
};

export const getEventValue = (e) => e?.target?.value;

export const getCleanedOpen = (open, key) => {
	return open.includes(key)
		? open.filter((prevKey) => prevKey !== key)
		: [...open, key];
};

export const getAllFiledOptions = (groups) => {
	return groups.flatMap((group) => group.options);
};

export const arrayToPathString = (arr) => {
	return arr
		.map((part) => (typeof part === 'number' ? `[${part}]` : `.${part}`))
		.join('')
		.replace(/^\./, '');
};

/**
 * Retrieves the custom field definitions for a given post type.
 *
 * If `localize_data` is provided, it uses that object to extract `field_groups` and `post_types`.
 * If not provided, it falls back to using the global `window.EasyContentManagerGlobal` object.
 *
 * The returned custom fields are constructed by:
 * - Including any `custom_fields` defined in `post_types[post_type]`
 * - Prepending `custom_fields` from any `field_groups` whose `location` includes the given post type
 *
 * @param {string} post_type       - The post type slug (CPT) to get custom fields for.
 * @param {Object} [localize_data] - Optional localized data object.
 *
 * @return {Array} A combined array of custom field configurations for the given post type.
 */

export const getCustomData = (post_type, localize_data = null) => {
	// Use provided data or fall back to global
	const source = localize_data || window.EasyContentManagerGlobal;

	if (!source) return [];

	const { field_groups = [], post_types = {} } = source;

	const baseFields = post_types[post_type]?.custom_fields || [];
	let customData = [...baseFields];

	field_groups.forEach((group) => {
		if (group?.location.post_type?.includes(post_type)) {
			const groupFields = group.custom_fields || [];
			customData = post_types[post_type]
				? [...groupFields, ...baseFields]
				: [...groupFields];
		}
	});

	return customData;
};

/**
 * Retrieves and filters custom field data based on the current user's login status and roles.
 * * This function checks the provided data (or falls back to the global `window.EasyContentManagerGlobal`)
 * against the current user's credentials to determine which field groups are accessible.
 * * @param {Object|null} [localize_data=null] - Optional object containing the plugin's localized data. 
 * Defaults to `window.EasyContentManagerGlobal`.
 * @returns {Array<Object>} An array of custom field objects gathered from all authorized field groups.
 * * @example
 * // Structure of expected 'source' object:
 * // {
 * //   field_groups: Array<{
 * //     location: {
 * //       current_user?: string[],
 * //       current_user_role?: string[]
 * //     },
 * //     custom_fields: Object[]
 * //   }>
 * // }
 */
export const getCustomDataForUser = (localize_data = null) => {
	const source = localize_data || window.EasyContentManagerGlobal;
	if (!source) return [];

	const userId = Number(current_user_id ?? 0);
	const userRoles = current_user_roles ?? [];
	const isCurrentUserLoggedIn = userId > 0;

	const { field_groups = [] } = source;
	let customData = [];

	field_groups.forEach((group) => {
		const location = group?.location ?? {};

		const isAllowedForLoggedUser =
			location["current_user"]?.includes('logged_in') &&
			isCurrentUserLoggedIn;

		const isAllowedForRoleBaseUser =
			isArray(location["current_user_role"]) &&
			location["current_user_role"].some((role) => userRoles.includes(role));

		if (isAllowedForLoggedUser || isAllowedForRoleBaseUser) {
			if (isArray(group.custom_fields)) {
				customData.push(...group.custom_fields);
			}
		}
	});

	return customData;
};

export function hsbToHex({ hue, saturation, brightness, alpha = 1 }) {
	// Convert to fractions
	const h = hue;
	const s = saturation / 100;
	const v = brightness / 100;

	const c = v * s;
	const x = c * (1 - Math.abs(((h / 60) % 2) - 1));
	const m = v - c;

	let r, g, b;

	if (h >= 0 && h < 60) {
		r = c;
		g = x;
		b = 0;
	} else if (h >= 60 && h < 120) {
		r = x;
		g = c;
		b = 0;
	} else if (h >= 120 && h < 180) {
		r = 0;
		g = c;
		b = x;
	} else if (h >= 180 && h < 240) {
		r = 0;
		g = x;
		b = c;
	} else if (h >= 240 && h < 300) {
		r = x;
		g = 0;
		b = c;
	} else {
		r = c;
		g = 0;
		b = x;
	}

	// Convert to [0-255]
	r = Math.round((r + m) * 255);
	g = Math.round((g + m) * 255);
	b = Math.round((b + m) * 255);

	// Include alpha if not 1
	if (alpha < 1) {
		const a = Math.round(alpha * 255)
			.toString(16)
			.padStart(2, '0');
		return `#${((1 << 24) + (r << 16) + (g << 8) + b)
			.toString(16)
			.slice(1)}${a}`;
	}

	return `#${((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1)}`;
}

export const varColors = `
	:root {
		--ecm-border-color: #DDDDDF;
		--ecm-body-background: #F7F7F7;
		--ecm-background: #fff;
		--ecm-body-background: #F6F7F8; 
		--ecm-body-background: #fff; 
		--ecm-primary: #4038CA;
		--ecm-font-color: #141A24;
		--ecm-secondary: #F6F7F8;
		--ecm-secondary: #E0E4E8;
		--ecm-border-color: #CBD1D7;
		--ecm-text-muted: #738496;
		--ecm-warning: #F15B50;
		--ecm-font-color: #38414A;
		--ecm-secondary: #8E9BA9;
		--ecm-shadow: 0px 0px 1px 0px rgba(20, 26, 36, 0.20), 0px 1px 2px 0px rgba(20, 26, 36, 0.10);
		--ecm-shadow-2: 0px 0.5px 2px 0px rgba(16, 24, 40, 0.15);
		--ecm-gray: #BAC2CB;
		--ecm-gray-2: #fff;
		--ecm-body-background: #e4e4e799;
		--ecm-body-background2: #4038CA;
		--ecm-body-background3: #322eb0;
		background: var(--ecm-body-background);
	}
`;

export const get_ecm_app_slug = (sub_path = '') => {
	const separator = sub_path?.length ? '-' : '';
	return `${ecm_app_slug}${separator}${sub_path}`;
};

export const exportHandler = (data) => {
	if (!data || !data.post_title) return;

	const json = JSON.stringify(data, null, 2);

	const blob = new Blob([json], { type: 'application/json' });

	const url = URL.createObjectURL(blob);
	const link = document.createElement('a');
	link.href = url;

	link.download = `${data.post_title}.json`;

	document.body.appendChild(link);
	link.click();

	document.body.removeChild(link);
	URL.revokeObjectURL(url);
};

export const removeNodes = (wrapper) => {
	if (wrapper) {
		const selector = 'p:not([class]), p[class=""]';
		const nodes = wrapper.querySelectorAll(selector);

		nodes.forEach(node => {
			node.replaceWith(...node.childNodes);
		});

		const brs = wrapper.querySelectorAll('br:not([class]), br[class=""]');
		brs.forEach(br => br.remove());
	}
};


/**
 * Generates an SVG string using the given path, viewBox, fill and outline.
 * @param {string} d - The SVG path.
 * @param {string} viewBox - The viewBox attribute of the SVG.
 * @param {string} [fill=#141A24] - The fill color of the SVG.
 * @param {string} [outline=#141A24] - The outline color of the SVG.
 * @returns {string} - The generated SVG string.
 */
export const generateSvgUsingD = (d, viewBox, fill = '#141A24', outline = '#141A24') => {
	return `<svg height="20" width="20" xmlns="http://www.w3.org/2000/svg" viewBox="${viewBox}">
	<path d="${d}" fill="${fill}"/>
	</svg>`;
}

export const copyShortcode = (shortcode, dispatch) => {
	copyToClipboard(shortcode);
	dispatch(showNotification({
		message: __('Shortcode copied to clipboard', 'easy-content-manager'),
		isShow: true,
		type: 'success',
	}))
};
