import React from 'react';
import { __ } from '@wordpress/i18n';
import {
	Box,
	Stack,
	Text,
	ButtonGroup,
	Button,
	Separator,
	Flex,
	Checkbox,
	NumberInput,
} from '@chakra-ui/react';
import { useFormikContext } from 'formik';

const HIDE_ITEMS = [
	'Toggle All',
	'Permalink',
	'Content Editor',
	'Excerpt',
	'Discussion',
	'Comments',
	'Revisions',
	'Slug',
	'Author',
	'Format',
	'Page Attributes',
	'Featured Image',
	'Categories',
	'Tags',
	'Send Trackbacks',
];

const Presentation = () => {
	const { values, setFieldValue } = useFormikContext();

	const pres = values.presentation ?? {};
	const hide = pres.hideOnScreen ?? [];

	const setPres = (key, v) => setFieldValue(`presentation.${key}`, v);

	const toggleHide = (item) => {
		const next = hide.includes(item)
			? hide.filter((i) => i !== item)
			: [...hide, item];
		setPres('hideOnScreen', next);
	};

	const allChecked = hide.length === HIDE_ITEMS.length;
	const isIndeterminate = hide.length > 0 && !allChecked;
	const toggleAll = () =>
		setPres('hideOnScreen', allChecked ? [] : [...HIDE_ITEMS]);

	return (
		<Flex p={4} border="none" rounded="md" justifyContent="space-between">
			<Stack spacing={5} maxW="500px">
				<Flex direction="column">
					<Text
						mb={2}
						fontWeight="bold"
						color={'var(--ecm-font-color)'}
					>
						{__('Style', 'easy-content-manager')}
					</Text>
					<ButtonGroup
						size="sm"
						gap={0}
						width="fit-content"
						border="1px solid var(--ecm-border-color)"
						rounded="sm"
					>
						<Button
							onClick={() => setPres('style', 'standard')}
							bg={
								pres.style === 'standard'
									? 'var(--ecm-primary)'
									: 'transparent'
							}
							color={
								pres.style === 'standard'
									? '#ffffff'
									: 'var(--ecm-font-color)'
							}
							rounded={'none'}
						>
							{__(
								'Standard (WP metabox)',
								'easy-content-manager'
							)}
						</Button>
						<Separator
							orientation="vertical"
							color="var(--ecm-border-color)"
							height="36px"
							size="md"
						/>
						<Button
							onClick={() => setPres('style', 'seamless')}
							bg={
								pres.style === 'seamless'
									? 'var(--ecm-primary)'
									: 'transparent'
							}
							color={
								pres.style === 'seamless'
									? '#ffffff'
									: 'var(--ecm-font-color)'
							}
							rounded={'none'}
						>
							{__(
								'Seamless (no metabox)',
								'easy-content-manager'
							)}
						</Button>
					</ButtonGroup>
				</Flex>

				<Flex direction="column">
					<Text
						mb={2}
						fontWeight="bold"
						color={'var(--ecm-font-color)'}
					>
						{__('Position', 'easy-content-manager')}
					</Text>
					<ButtonGroup
						size="sm"
						gap={0}
						width="fit-content"
						border="1px solid var(--ecm-border-color)"
						rounded="sm"
					>
						<Button
							onClick={() => setPres('position', 'high')}
							bg={
								pres.position === 'high'
									? 'var(--ecm-primary)'
									: 'transparent'
							}
							color={
								pres.position === 'high'
									? '#ffffff'
									: 'var(--ecm-font-color)'
							}
							rounded={'none'}
						>
							{__('High (after title)', 'easy-content-manager')}
						</Button>
						<Separator
							orientation="vertical"
							color="var(--ecm-border-color)"
							height="36px"
							size="md"
						/>
						<Button
							onClick={() => setPres('position', 'normal')}
							bg={
								pres.position === 'normal'
									? 'var(--ecm-primary)'
									: 'transparent'
							}
							color={
								pres.position === 'normal'
									? '#ffffff'
									: 'var(--ecm-font-color)'
							}
							rounded={'none'}
						>
							{__(
								'Normal (after content)',
								'easy-content-manager'
							)}
						</Button>
						<Separator
							orientation="vertical"
							color="var(--ecm-border-color)"
							height="36px"
							size="md"
						/>
						<Button
							onClick={() => setPres('position', 'side')}
							bg={
								pres.position === 'side'
									? 'var(--ecm-primary)'
									: 'transparent'
							}
							color={
								pres.position === 'side'
									? '#ffffff'
									: 'var(--ecm-font-color)'
							}
							rounded={'none'}
						>
							{__('Side', 'easy-content-manager')}
						</Button>
					</ButtonGroup>
					<Text fontSize="xs" mt={1} color={'var(--ecm-font-color)'}>
						{__(
							'‘High’ position not supported in the Block Editor',
							'easy-content-manager'
						)}
					</Text>
				</Flex>

				<Flex direction="column">
					<Text
						mb={2}
						fontWeight="bold"
						color={'var(--ecm-font-color)'}
					>
						{__('Label Placement', 'easy-content-manager')}
					</Text>
					<ButtonGroup
						size="sm"
						gap={0}
						width="fit-content"
						border="1px solid var(--ecm-border-color)"
						rounded="sm"
					>
						<Button
							onClick={() => setPres('labelPlacement', 'top')}
							bg={
								pres.labelPlacement === 'top'
									? 'var(--ecm-primary)'
									: 'transparent'
							}
							color={
								pres.labelPlacement === 'top'
									? '#ffffff'
									: 'var(--ecm-font-color)'
							}
							rounded={'none'}
						>
							{__('Top aligned', 'easy-content-manager')}
						</Button>
						<Separator
							orientation="vertical"
							color="var(--ecm-border-color)"
							height="36px"
							size="md"
						/>
						<Button
							onClick={() => setPres('labelPlacement', 'left')}
							bg={
								pres.labelPlacement === 'left'
									? 'var(--ecm-primary)'
									: 'transparent'
							}
							color={
								pres.labelPlacement === 'left'
									? '#ffffff'
									: 'var(--ecm-font-color)'
							}
							rounded={'none'}
						>
							{__('Left aligned', 'easy-content-manager')}
						</Button>
					</ButtonGroup>
				</Flex>

				<Flex direction="column">
					<Text
						mb={2}
						fontWeight="bold"
						color={'var(--ecm-font-color)'}
					>
						{__('Instruction Placement', 'easy-content-manager')}
					</Text>
					<ButtonGroup
						size="sm"
						gap={0}
						width="fit-content"
						border="1px solid var(--ecm-border-color)"
						rounded="sm"
					>
						<Button
							onClick={() =>
								setPres('instructionPlacement', 'belowLabel')
							}
							bg={
								pres.instructionPlacement === 'belowLabel'
									? 'var(--ecm-primary)'
									: 'transparent'
							}
							color={
								pres.instructionPlacement === 'belowLabel'
									? '#ffffff'
									: 'var(--ecm-font-color)'
							}
							rounded={'none'}
						>
							{__('Below label', 'easy-content-manager')}
						</Button>
						<Separator
							orientation="vertical"
							color="var(--ecm-border-color)"
							height="36px"
							size="md"
						/>
						<Button
							onClick={() =>
								setPres('instructionPlacement', 'belowFields')
							}
							bg={
								pres.instructionPlacement === 'belowFields'
									? 'var(--ecm-primary)'
									: 'transparent'
							}
							color={
								pres.instructionPlacement === 'belowFields'
									? '#ffffff'
									: 'var(--ecm-font-color)'
							}
							rounded={'none'}
						>
							{__('Below fields', 'easy-content-manager')}
						</Button>
					</ButtonGroup>
				</Flex>

				<Flex direction="column">
					<Text
						mb={2}
						fontWeight="bold"
						color={'var(--ecm-font-color)'}
					>
						{__('Order No.', 'easy-content-manager')}
					</Text>
					<NumberInput.Root
						width="200px"
						value={pres.order ?? 0}
						min={0}
						onChange={(_, n) =>
							setPres('order', Number.isNaN(n) ? 0 : n)
						}
					>
						<NumberInput.Control />
						<NumberInput.Input
							background={'transparent !important'}
							color={'var(--ecm-font-color)'}
						/>
					</NumberInput.Root>
					<Text fontSize="xs" mt={1} color={'var(--ecm-font-color)'}>
						{__(
							'Field groups with a lower order will appear first',
							'easy-content-manager'
						)}
					</Text>
				</Flex>
			</Stack>

			<Box p={4} minW="300px" borderWidth="1px" rounded="md">
				<Flex
					width={'100%'}
					justifyContent={'space-between'}
					alignItems={'center'}
				>
					<Text fontWeight="bold" color={'var(--ecm-font-color)'}>
						{__('Hide on screen', 'easy-content-manager')}
					</Text>
					<span className="easy-content-manager-icon easy-content-manager-icon--questions"></span>
				</Flex>
				<Stack spacing={2}>
					{HIDE_ITEMS.map((item) => (
						<Checkbox.Root
							key={item}
							isChecked={hide.includes(item)}
							onChange={() => toggleHide(item)}
						>
							<Checkbox.HiddenInput />
							<Checkbox.Control />
							<Checkbox.Label color={'var(--ecm-font-color)'}>
								{__(item, 'easy-content-manager')}
							</Checkbox.Label>
						</Checkbox.Root>
					))}
				</Stack>
			</Box>
		</Flex>
	);
};

export default Presentation;
