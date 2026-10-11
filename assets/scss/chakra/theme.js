import { createSystem, defaultConfig } from '@chakra-ui/react';

const SYSTEM_FONT_STACK =
	'-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif';

export const theme = createSystem(defaultConfig, {
	theme: {
		tokens: {
			fonts: {
				body: { value: SYSTEM_FONT_STACK },
				heading: { value: SYSTEM_FONT_STACK },
			},
			colors: {
				brand: {
					primary: { value: 'var(--ecm-primary)' },
					secondary: { value: 'var(--ecm-secondary)' },
					gray: { value: 'var(--ecm-gray)' },
					darkGray: { value: 'var(--ecm-text-muted)' },
					border: { value: 'var(--ecm-border-color)' },
					disabled: { value: 'var(--ecm-text-muted)' },
					bgGray: { value: 'var(--ecm-body-background)' },
					warning: { value: 'var(--ecm-warning)' },
					blue: { value: 'var(--ecm-primary)' },
				},
			},
		},
	}
})

