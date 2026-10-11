import React, { useEffect } from 'react';
import TopBar from '@ECMComponents/TopBar';
import { __ } from '@wordpress/i18n';
import { useNavigate } from 'react-router-dom';
import { route_path } from '@ECMUtils/helper';
import { useSelector, useDispatch } from 'react-redux';
import { fetchCollections } from '@ECMRedux/Slices/collectionDataSlice/collectionDataSlice';
import { getFieldGroupsCount } from '@ECMRedux/Slices/fieldGroupsSlice/fieldGroupsSlice';
import { getAllCustomTaxonomies } from '@ECMRedux/Slices/taxonomySlice/taxonomySlice';
import {
	Box,
	Heading,
	SimpleGrid,
	VStack,
	Text,
	Button,
	Container,
} from '@chakra-ui/react';
import { primaryBtn } from '../../../assets/scss/chakra/recipe';

const Dashboard = () => {
	const { totalPosts } = useSelector((state) => state.post_types);
	const { totalItems } = useSelector((state) => state.field_groups);
	const { totalItems: totalTaxonomies } = useSelector(
		(state) => state.taxonomies
	);
	const navigate = useNavigate();
	const dispatch = useDispatch();
	const cardData = [
		{
			title: __('Post Types', 'easy-content-manager'),
			data: totalPosts?.length > 0 ? totalPosts?.length : 0,
			url: `${route_path}admin.php?page=easy-content-manager-post-types&path=add-post&action=add`,
		},
		{
			title: __('Taxonomies', 'easy-content-manager'),
			data: totalTaxonomies ?? 0,
			url: `${route_path}admin.php?page=easy-content-manager-taxonomy&action=add`,
		},
		{
			title: __('Field Groups', 'easy-content-manager'),
			data: totalItems ?? 0,
			url: `${route_path}admin.php?page=easy-content-manager-field-groups&action=add`,
		},
	];

	useEffect(() => {
		dispatch(fetchCollections({}));
		dispatch(getFieldGroupsCount());
		dispatch(getAllCustomTaxonomies({}));
	}, []);

	return (
		<>
			<TopBar title={__('Dashboard', 'easy-content-manager')} />

			<Container maxW="1280px">
				<Box paddingX={8} paddingY={4}>
					<Heading color="var(--ecm-font-color)" size="md" mb={6}>
						{__(
							'Welcome to Content Manager',
							'easy-content-manager'
						)}
					</Heading>

					<SimpleGrid
						columns={{ base: 1, md: 3 }}
						spacing={6}
						gap={'30px'}
					>
						{cardData.map((cardItem, cardIndex) => {
							return (
								<VStack
									spacing={4}
									align="start"
									width="100%"
									gap={'16px'}
									key={cardIndex}
								>
									<Box
										p={6}
										bg="var(--ecm-background)"
										shadow="sm"
										rounded="md"
										border="1px solid"
										borderColor="var(--ecm-border-color)"
										width="100%"
										textAlign="left"
									>
										<Text
											color="var(--ecm-font-color)"
											fontSize="3xl"
											fontWeight="bold"
										>
											{cardItem.data}
										</Text>
										<Text
											color="var(--ecm-font-color)"
											fontSize="lg"
										>
											{cardItem.title}
										</Text>
									</Box>

									<Button
										{...primaryBtn}
										width="100%"
										onClick={() => navigate(cardItem.url)}
										disabled={!cardItem.url}
									>
										{__(
											'Create New',
											'easy-content-manager'
										)}{' '}
										{cardItem.title}
									</Button>
								</VStack>
							);
						})}
					</SimpleGrid>
				</Box>
			</Container>
		</>
	);
};

export default Dashboard;
