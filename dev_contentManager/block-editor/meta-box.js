import { createElement, createRoot } from '@wordpress/element';
import MetaField from '../meta_components';
import { theme } from '../../assets/scss/chakra/theme';
import { ChakraProvider } from '@chakra-ui/react';

export const renderMetaField = (container) => {
	if (!container) return;

	const root = createRoot(container);
	const renderMetaField = createElement(MetaField);

	root.render(
		<ChakraProvider value={theme}>{renderMetaField}</ChakraProvider>
	);
};
