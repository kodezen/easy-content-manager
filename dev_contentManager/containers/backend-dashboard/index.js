import { useQuery } from '@ECMUtils/helper';
import Notification from '@ECMComponents/Notification';
import Dashboard from '../Dashboard';
import Settings from '../Settings';
import FieldGroups from '../FieldGroups';
import { ChakraProvider } from '@chakra-ui/react';
import { theme } from '../../../assets/scss/chakra/theme';
import Texanomy from '@ECMContainers/Texanomy';
import AddTaxonomiesForm from '@ECMContainers/Taxonomies';
import TaxonomiesTable from '@ECMContainers/Taxonomies/TaxonomiesTable/TaxonomiesTable';
import { getBaseSettings } from '@ECMRedux/Slices/settingsSlice/settingsSlice';
import { useEffect } from 'react';
import { useDispatch } from 'react-redux';
import CreateTexanomy from '@ECMContainers/Texanomy/CreateTexanomy';
import { getAllAddons } from '@ECMRedux/Slices/addonSlice/addonSlice';
import ReviewAndRatings from '@ECMContainers/ReviewAndRatings';
import Claim from '@ECMContainers/Claim';
import CustomPostTypes from '@ECMContainers/CustomPostTypes';
import CustomPostTypesForm from '@ECMContainers/CustomPostTypes/CustomPostTypesForm';
import DynamicPosts from '@ECMComponents/DynamicPosts';
import DynamicPostForm from '@ECMComponents/DynamicPosts/DynamicPostForm';
import UpvoteDetails from '@ECMComponents/DynamicPosts/Upvote/UpvoteDetails';
import ReactionUserDetails from '@ECMComponents/DynamicPosts/Reaction/ReactionUserDetails';
import Addons from '@ECMContainers/Addons';
import Tools from '@ECMContainers/Tools';

const BackendDashboard = () => {
	const query = useQuery();
	const dispatch = useDispatch();

	useEffect(() => {
		dispatch(getBaseSettings());
		dispatch(getAllAddons());
	}, []);

	const renderSwitch = (page, id, action, path) => {
		const post_type = page.replace('easy-content-manager-', '');

		switch (page) {
			case 'easy-content-manager':
				return <Dashboard />;

			case 'easy-content-manager-post-types':
				if (action && path) {
					return <CustomPostTypesForm post_type={post_type} id={id} />;
				}
				return <CustomPostTypes appName="easy-content-manager" />;

			case 'easy-content-manager-settings':
				return <Settings />;

			case 'easy-content-manager-addons':
				return <Addons />;

			case 'easy-content-manager-tools':
				return <Tools />;

			case 'easy-content-manager-taxonomy':
				if (action || id) {
					return (
						<AddTaxonomiesForm
							action={action}
							id={id}
							appName="easy-content-manager"
						/>
					);
				}
				return <TaxonomiesTable />;

			case 'easy-content-manager-field-groups':
				if (action || id) {
					return <FieldGroups action={action} ID={id} />;
				}
				return <FieldGroups />;

			case 'easy-content-manager-review-ratings':
				return <ReviewAndRatings appName="easy-content-manager" />;

			case 'easy-content-manager-claims':
				return <Claim appName="easy-content-manager" />;

			case `easy-content-manager-${post_type}`:
				if (action && path) {
					return (
						<CreateTexanomy
							post_type={post_type}
							appName="easy-content-manager"
						/>
					);
				}
				if (path) {
					return (
						<Texanomy
							type={path}
							post_type={post_type}
							appName="easy-content-manager"
						/>
					);
				}
				if (action || id) {
					if (action === 'upvote') {
						return (
							<UpvoteDetails
								post_type={post_type}
								id={id}
								appName="easy-content-manager"
							/>
						);
					}
					if (action === 'reaction') {
						return (
							<ReactionUserDetails
								post_type={post_type}
								id={id}
								appName="easy-content-manager"
							/>
						);
					}
					return (
						<DynamicPostForm
							post_type={post_type}
							id={id}
							appName="easy-content-manager"
						/>
					);
				}
				return (
					<DynamicPosts
						post_type={post_type}
						appName="easy-content-manager"
					/>
				);

			default:
				break;
		}
	};

	return (
		<ChakraProvider value={theme}>
			<Notification />
			{renderSwitch(
				query.get('page'),
				query.get('id'),
				query.get('action'),
				query.get('path'),
				query.get('post_type')
			)}
		</ChakraProvider>
	);
};

export default BackendDashboard;
