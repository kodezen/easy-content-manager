import { createRoot } from '@wordpress/element';
import { LoadClassicMetaField } from '@ClassicEditor/meta-box';
import { getCustomData } from '@ECMUtils/helper';

import './../assets/scss/classic-editor.scss';

document.addEventListener('DOMContentLoaded', () => {
	const classicEditor = document.querySelector('#postdivrich'); // present only in Classic Editor

	const customData = getCustomData(
		document.getElementById('post_type')?.value
	);

	if (classicEditor && customData.length) {
		// Add a container for React if it doesn’t exist
		let container = document.getElementById('classic-meta-loader');
		if (!container) {
			container = document.createElement('div');
			container.id = 'classic-meta-loader';
			document.body.appendChild(container);
		}

		createRoot(container).render(<LoadClassicMetaField />);
	}
});
