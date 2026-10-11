import { useEffect } from 'react';
import { subscribe, select } from '@wordpress/data';
import { registerPlugin } from '@wordpress/plugins';
import { renderMetaField } from '@BlockEditor/meta-box';
import { getCustomData } from '@ECMUtils/helper';
import './../assets/scss/block-editor.scss';

let hasRendered = false;

const addEditorBottomPanelSection = (customData) => {
	const blocksRootContainer = document.querySelector('.edit-post-layout__content') || document.querySelector('.interface-interface-skeleton__content');
	if (!blocksRootContainer || !customData.length || hasRendered) return;

	let bottomPanelSection = document.getElementById('ecm-meta-box');
	if (!bottomPanelSection) {
		hasRendered = true;

		bottomPanelSection = document.createElement('div');
		bottomPanelSection.id = 'ecm-meta-box';
		bottomPanelSection.className = 'ecm-meta-box';

		const sectionWrap = document.createElement('div');
		sectionWrap.className = 'ecm-meta-box-wrapper';

		const metaFieldContainer = document.createElement('div');
		metaFieldContainer.className =
			'content-manager-meta-field-container content-manager-column-items';
		metaFieldContainer.id = 'resizableDiv';
		metaFieldContainer.style.backgroundColor = '#ffffff';

		const resizerTopDiv = document.createElement('div');
		const resizerTopBtn = document.createElement('button');
		resizerTopDiv.className = 'resizer-top-div';
		resizerTopBtn.className = 'resizer-top-btn';
		resizerTopDiv.append(resizerTopBtn);
		sectionWrap.append(metaFieldContainer);

		bottomPanelSection.append(sectionWrap);
		blocksRootContainer.append(bottomPanelSection);

		renderMetaField(metaFieldContainer);
	}
};

const EditorBottomPanel = () => {
	useEffect(() => {
		const postType = document.getElementById('post_type')?.value;
		const customData = getCustomData(postType);

		const unsubscribe = subscribe(() => {
			const isReady =
				select('core/editor')?.isEditedPostNew() !== undefined;
			if (!isReady) return;

			addEditorBottomPanelSection(customData);
		});

		return () => {
			unsubscribe();
		};
	}, []);

	return null;
};

registerPlugin('ecm-metabox', {
	render: EditorBottomPanel,
});
