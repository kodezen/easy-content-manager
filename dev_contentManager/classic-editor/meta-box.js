import { useEffect } from 'react';
import { createRoot } from '@wordpress/element';
import React from 'react';
import MetaField from '../meta_components';
import { ChakraProvider } from '@chakra-ui/react';
import { theme } from '../../assets/scss/chakra/theme';

export const LoadClassicMetaField = () => {
	useEffect(() => {
		const classicEditor = document.querySelector('#postdivrich');
		if (classicEditor) {
			let container = document.getElementById('react-classic-meta');
			if (!container) {
				container = document.createElement('div');
				container.id = 'react-classic-meta';
				container.className =
					'react-classic-meta content-manager-column-items';
				classicEditor.parentNode.insertBefore(
					container,
					classicEditor.nextSibling
				);
			}

			createRoot(container).render(
				<ChakraProvider value={theme}>
					<MetaField />
				</ChakraProvider>
			);
		}
	}, []);

	return null;
};
