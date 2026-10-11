import {
  upvote_settings,
  current_user_id,
  removeNodes,
} from '@ECMUtils/helper';
import '../assets/scss/frontend.scss';
import FrontendModal from '@ECMContainers/FrontendModal';
import { createRoot } from 'react-dom/client';
import { ChakraProvider } from '@chakra-ui/react';
import { theme } from '../assets/scss/chakra/theme';
import { manageReviewSelection, manageReviewStarsUI } from '@ECMContainers/frontend/review';
import { handleBookmarkToggle, manageBookmarkList } from '@ECMContainers/frontend/bookmark';
import { manageReaction } from '@ECMContainers/frontend/reaction';
import { handleUpvoteToggle } from '@ECMContainers/frontend/upvote';
import { Provider } from 'react-redux';
import store from '@ECMRedux/store';
import { BrowserRouter as Router } from 'react-router-dom';
import Frontend from '@ECMContainers/frontend';

document.addEventListener('DOMContentLoaded', () => {
  // review and ratings operations
  const reviewFormWrapper = document.querySelector('.ecm-shortcode--review');
  const reviewListWrapper = document.querySelector('.ecm-shortcode--review__list-wrapper');
  const reviewSubmissionBox = document.querySelector('.ecm-shortcode--review__submission-message-box');
  const reviewDropdown = document.querySelector('.ecm-shortcode--review__dropdown');
  const ratingStars = document.querySelectorAll('.ecm-shortcode--review__star');
  const userReviews = document.querySelector('.ecm-shortcode--user-reviews');

  if (userReviews) {
    removeNodes(userReviews);
  }

  if (ratingStars) {
    manageReviewStarsUI(ratingStars);
  }

  if (reviewDropdown) {
    manageReviewSelection(reviewDropdown);
  }

  if (reviewSubmissionBox) {
    removeNodes(reviewSubmissionBox);
  }

  if (reviewFormWrapper) {
    removeNodes(reviewFormWrapper);
  }

  if (reviewListWrapper) {
    removeNodes(reviewListWrapper);
  }

  // claim operations
  const claimWrapper = document.querySelector('.ecm-shortcode--claim');
  const claimMessageTooltip = document.querySelector('.ecm-shortcode--claim__message-tooltip');

  if (claimMessageTooltip) {
    removeNodes(claimMessageTooltip);
  }

  if (claimWrapper) {
    removeNodes(claimWrapper);
    const frontendReactWrapper = document.getElementById('ecm-frontend-react-wrap');
    if (frontendReactWrapper) {
      const root = createRoot(frontendReactWrapper);
      root.render(
        <ChakraProvider value={theme}>
          <FrontendModal />
        </ChakraProvider>
      );
    }
  }

  //frontend submission operations
  const submissionWrapper = document.querySelector('.ecm-shortcode--frontend-submission');

  if (submissionWrapper) {
    removeNodes(submissionWrapper);
    const dataConfig = JSON.parse(submissionWrapper.getAttribute('data-config'));
    const root = createRoot(submissionWrapper);
    root.render(
      <Provider store={store}>
        <Router>
          <Frontend dataConfig={dataConfig} />
        </Router>
      </Provider>
    );
  }

  // bookmark operations
  const bookmarkWrappers = document.querySelectorAll('.ecm-shortcode--bookmark');
  const bookmarkListItems = document.querySelectorAll('.ecm-shortcode--bookmark__list-item');
  const bookmarkBadgeWrappers = document.querySelectorAll('.ecm-shortcode--badge');

  if (bookmarkBadgeWrappers.length > 0) {
    bookmarkBadgeWrappers.forEach(wrapper => {
      removeNodes(wrapper)
    })
  }

  if (bookmarkListItems.length > 0) {
    bookmarkListItems.forEach(function (item) {
      manageBookmarkList(item)
    })
  }

  bookmarkWrappers.forEach((wrapper) => {
    const bookmarkBtn = wrapper.querySelector('.ecm-shortcode--bookmark__btn');

    if (bookmarkBtn) {
      handleBookmarkToggle(wrapper, bookmarkBtn);
    }
  });

  // reaction operations
  const reactionWrapper = document.querySelector('.ecm-shortcode--reaction');
  if (reactionWrapper) {
    manageReaction(reactionWrapper);
  }

  // upvote operations
  const upvoteWrapper = document.querySelector('.ecm-shortcode--upvote');
  const isEnabledAnonymousVote = upvote_settings?.enable_anonymous_upvote;

  if (upvoteWrapper) removeNodes(upvoteWrapper);

  if (upvoteWrapper && (Boolean(Number(current_user_id)) || isEnabledAnonymousVote)) {
    handleUpvoteToggle(upvoteWrapper);
  }
});
