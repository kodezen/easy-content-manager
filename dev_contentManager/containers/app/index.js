import { useQuery, ecm_app_slug } from '@ECMUtils/helper';
import Notification from '@ECMComponents/Notification';
import { ChakraProvider } from '@chakra-ui/react';
import { theme } from '../../../assets/scss/chakra/theme';
import Taxonomy from '@ECMContainers/Texanomy';
import RenderSettings from '@ECMContainers/Settings/RenderSettings';
import { useDispatch } from 'react-redux';
import { useEffect } from 'react';
import { getBaseSettings } from '@ECMRedux/Slices/settingsSlice/settingsSlice';
import CreateTexanomy from '@ECMContainers/Texanomy/CreateTexanomy';
import DynamicPostForm from '@ECMComponents/DynamicPosts/DynamicPostForm';
import DynamicPosts from '@ECMComponents/DynamicPosts';
import UpvoteDetails from '@ECMComponents/DynamicPosts/Upvote/UpvoteDetails';
import ReactionUserDetails from '@ECMComponents/DynamicPosts/Reaction/ReactionUserDetails';

const AppBackendDashboard = ({ settingsBuilder }) => {
	const query = useQuery();
	const dispatch = useDispatch();

	useEffect(() => {
		dispatch(getBaseSettings());
	}, []);

	const renderSwitch = (page, id, action, path) => {
		const APP_PREFIX = ecm_app_slug + '-';

		const settingsType = settingsBuilder
			? page.slice(APP_PREFIX.length)
			: '';
		const postType =
			!settingsBuilder && page.startsWith(APP_PREFIX)
				? page.slice(APP_PREFIX.length)
				: '';

		const SETTINGS_CASE = settingsBuilder && settingsType && !postType;
		const POST_CASE = !settingsBuilder && postType && !settingsType;

		switch (true) {
			case SETTINGS_CASE:
				return (
					<RenderSettings
						settingsName={settingsType}
						appName={ecm_app_slug}
					/>
				);

			case POST_CASE:
				if (path) {
					if (action && path) {
						return (
							<CreateTexanomy
								post_type={postType}
								appName={ecm_app_slug}
							/>
						);
					}
					return (
						<Taxonomy
							type={path}
							post_type={postType}
							appName={ecm_app_slug}
						/>
					);
				}
				if (action || id) {
					if (action === 'upvote') {
						return (
							<UpvoteDetails
								post_type={postType}
								id={id}
								appName={ecm_app_slug}
							/>
						);
					}
					if (action === 'reaction') {
						return (
							<ReactionUserDetails
								post_type={postType}
								id={id}
								appName={ecm_app_slug}
							/>
						);
					}
					return (
						<DynamicPostForm
							post_type={postType}
							id={id}
							appName={ecm_app_slug}
						/>
					);
				}
				return (
					<DynamicPosts
						post_type={postType}
						appName={ecm_app_slug}
					/>
				);

			default:
				return 'Not Found Route';
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

export default AppBackendDashboard;
