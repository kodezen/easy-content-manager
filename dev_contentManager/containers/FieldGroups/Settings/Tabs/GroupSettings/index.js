import React, { useContext } from 'react';
import { __ } from '@wordpress/i18n';
import { Box, Flex, Switch, Text, Button, Spacer } from '@chakra-ui/react';
import { useFormikContext } from 'formik';
import { useDispatch } from 'react-redux';
import { useNavigate } from 'react-router-dom';
import { route_path } from '@ECMUtils/helper';
import { deleteFieldGroup } from '@ECMRedux/Slices/fieldGroupsSlice/fieldGroupsSlice';

const GroupSettings = () => {
	const { values, setFieldValue } = useFormikContext();

	const dispatch = useDispatch();
	const navigate = useNavigate();
	const isActive = values?.status === 'publish';

	const handleDeleteFieldGroup = () => {
		if (
			window.confirm(
				__(
					'Are you sure you want to delete this field group?',
					'easy-content-manager'
				)
			)
		) {
			dispatch(deleteFieldGroup(values?.id));
			navigate(
				`${route_path}admin.php?page=easy-content-manager-field-groups`
			);
		}
	};

	return (
		<Box p={4} border="none" rounded="md">
			<Flex align="center" mb={3} gap={2}>
				<Switch.Root
					disabled={!values?.id}
					defaultChecked={isActive}
					onChange={(e) =>
						setFieldValue(
							'status',
							e.target.checked ? 'publish' : 'draft'
						)
					}
					colorScheme="purple"
				>
					<Switch.HiddenInput />
					<Switch.Control />
					<Switch.Label color={'var(--ecm-font-color)'}>
						{__('Active', 'easy-content-manager')}
					</Switch.Label>
				</Switch.Root>
			</Flex>

			<Flex
				align="center"
				pt={3}
				borderTop="1px solid"
				borderColor="var(--ecm-border-color)"
			>
				<Flex alignItems={'center'} gap={'5px'}>
					<span className="easy-content-manager-icon easy-content-manager-icon--time-picker"></span>
					<Text
						fontSize="sm"
						color={'var(--ecm-font-color)'}
						margin={0}
					>
						{values?.date ??
							__(
								`Created on ${values?.date}`,
								'easy-content-manager'
							)}
					</Text>
				</Flex>
				<Spacer />
				<Button
					colorPalette="red"
					size="sm"
					disabled={!values?.id}
					onClick={handleDeleteFieldGroup}
				>
					<span className="easy-content-manager-icon easy-content-manager-icon--trash has-ecm-blue-bg"></span>
					{__('Delete Field Group', 'easy-content-manager')}
				</Button>
			</Flex>
		</Box>
	);
};

export default GroupSettings;
