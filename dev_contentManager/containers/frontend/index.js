import React from 'react';
import { ChakraProvider, Text } from '@chakra-ui/react';
import DynamicPosts from '@ECMComponents/DynamicPosts';
import DynamicPostForm from '@ECMComponents/DynamicPosts/DynamicPostForm';
import ReactionUserDetails from '@ECMComponents/DynamicPosts/Reaction/ReactionUserDetails';
import UpvoteDetails from '@ECMComponents/DynamicPosts/Upvote/UpvoteDetails';
import Notification from '@ECMComponents/Notification';
import {
	current_user_id,
	current_user_roles,
	frontend_submission_settings,
	useQuery,
	is_admin,
} from '@ECMUtils/helper';
import { theme } from '../../../assets/scss/chakra/theme';

export default function Frontend({ dataConfig }) {
	const query = useQuery();

	const renderSwitch = (page, id, action, path) => {
		const postType = dataConfig.postType;
		const allowedRolesFromSettings =
			frontend_submission_settings.shortcode_settings.user_roles;
		const isAnonymousSubmissionEnabled = frontend_submission_settings.shortcode_settings.enable_anonymous_submission
		const isUserAllowedForSubmission = Boolean(Number(is_admin)) ? true : current_user_roles.some((role) => allowedRolesFromSettings.includes(role));

		// if (!Boolean(Number(current_user_id)) && !isAnonymousSubmissionEnabled)
		if (!Boolean(Number(current_user_id)))
			return <Text>User not logged in</Text>;

		if ((action || id) && dataConfig.showForm) {
			if (action === 'upvote') {
				return (
					<UpvoteDetails
						post_type={postType}
						id={id}
						isFrontEnd={true}
					/>
				);
			}
			if (action === 'reaction') {
				return (
					<ReactionUserDetails
						post_type={postType}
						id={id}
						isFrontEnd={true}
					/>
				);
			}

			// if (isAnonymousSubmissionEnabled || (dataConfig.showForm && isUserAllowedForSubmission)) {
			if (dataConfig.showForm && isUserAllowedForSubmission) {
				return (
					<DynamicPostForm
						post_type={postType}
						id={id}
						isFrontEnd={true}
					/>
				);
			}
		}

		// if (isAnonymousSubmissionEnabled || (dataConfig.showList && isUserAllowedForSubmission)) {
		if (dataConfig.showList && isUserAllowedForSubmission) {
			return <DynamicPosts post_type={postType} isFrontEnd={true} />;
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
}
