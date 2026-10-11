import { __ } from '@wordpress/i18n';

export const addonShortcodes = {
  "bookmark": [
    {
      title: __('Bookmark Button', 'easy-content-manager'),
      docUrl: 'https://easycontentmanager.pro/docs/bookmark-button-shortcode/',
      items: [
        {
          code: "[ecm_bookmark_button]",
          description: 'Adds a button for users to save the current content to their bookmarks.',
          docUrl: ''
        },
        {
          code: "[ecm_bookmark_button id='123']",
          description: 'Adds a bookmark button for a specific post or item ID.',
          docUrl: ''
        },
        {
          code: "[ecm_bookmark_button bookmark_text='Save' bookmarked_text='Saved']",
          description: 'Adds a button with customized labels for both the bookmark and bookmarked states.',
          docUrl: ''
        },
        {
          code: "[ecm_bookmark_button id='123' bookmark_text='Add to Favorites' bookmarked_text='Remove from Favorites']",
          description: 'Full configuration for a specific item with custom action labels.',
          docUrl: ''
        }
      ]
    },
    {
      title: __('Bookmark Badge', 'easy-content-manager'),
      docUrl: 'https://easycontentmanager.pro/docs/bookmark-badge-shortcode/',
      items: [
        {
          code: "[ecm_bookmark_badge]",
          description: 'Displays a badge indicating the bookmark status of the current item.',
          docUrl: ''
        },
        {
          code: "[ecm_bookmark_badge id='456']",
          description: 'Displays the bookmark status badge for a specific post or item ID.',
          docUrl: ''
        }
      ]
    },
    {
      title: __('Bookmark List', 'easy-content-manager'),
      docUrl: 'https://easycontentmanager.pro/docs/docs-bookmark-list-shortcode/',
      items: [
        {
          code: "[ecm_bookmark_list]",
          description: 'Shows a personalized list of all items the current logged-in user has bookmarked.',
          docUrl: ''
        },
        {
          code: "[ecm_bookmark_list user_id='123']",
          description: 'Displays the bookmark list for a specific user ID (requires authentication to view).',
          docUrl: ''
        },
        {
          code: "[ecm_bookmark_list not_found_text='No bookmark found']",
          description: 'Displays the user’s bookmarks with a custom message if the list is empty.',
          docUrl: ''
        }
      ]
    },
    {
      title: __('Bookmark Count', 'easy-content-manager'),
      docUrl: 'https://easycontentmanager.pro/docs/bookmark-count-shortcode/',
      items: [
        {
          code: "[ecm_bookmark_count]",
          description: 'Displays the total number of items bookmarked by the current user.',
          docUrl: ''
        },
        {
          code: "[ecm_bookmark_count user_id='5']",
          description: 'Displays the total bookmark count for a specific user ID.',
          docUrl: ''
        },
        {
          code: "[ecm_bookmark_count show_label='true']",
          description: 'Displays the count with a default label (e.g., "3 bookmarks").',
          docUrl: ''
        },
        {
          code: "[ecm_bookmark_count show_icon='true']",
          description: 'Displays the bookmark count accompanied by a visual icon.',
          docUrl: ''
        },
        {
          code: "[ecm_bookmark_count show_label='true' show_icon='true']",
          description: 'Displays both the icon and the label alongside the count.',
          docUrl: ''
        },
        {
          code: "[ecm_bookmark_count show_label='true' label_single='saved item' label_plural='saved items']",
          description: 'Displays the count with customized singular and plural labels.',
          docUrl: ''
        },
        {
          code: "[ecm_bookmark_count wrapper_class='my-custom-class']",
          description: 'Wraps the count in a custom CSS class for specialized styling.',
          docUrl: ''
        },
        {
          code: "[ecm_bookmark_count user_id='5' show_label='true' show_icon='true' label_single='favorite' label_plural='favorites' wrapper_class='favorite-counter']",
          description: 'A fully customized count display for a specific user including icons, custom labels, and CSS classes.',
          docUrl: ''
        }
      ]
    }
  ],
  "claim": [
    {
      title: __('Claim Button', 'easy-content-manager'),
      docUrl: 'https://easycontentmanager.pro/docs/claim-button-shortcode/',
      items: [
        {
          code: "[ecm_claim_button]",
          description: 'Displays a button for users to submit ownership claims for the current listing.',
          docUrl: ''
        },
        {
          code: "[ecm_claim_button post_id='123']",
          description: 'Displays a claim button for a specific listing ID.',
          docUrl: ''
        },
        {
          code: "[ecm_claim_button text='Claim This Listing']",
          description: 'Displays a claim button with customized button text.',
          docUrl: ''
        },
        {
          code: "[ecm_claim_button post_id='123' text='Claim This Business']",
          description: 'Full configuration for a specific listing with a custom claim label.',
          docUrl: ''
        }
      ]
    },
    {
      title: __('Claim Badge', 'easy-content-manager'),
      docUrl: 'https://easycontentmanager.pro/docs/claim-badge-shortcode/',
      items: [
        {
          code: "[ecm_claim_badge]",
          description: 'Displays a verification badge for the current listing if it has been claimed.',
          docUrl: ''
        },
        {
          code: "[ecm_claim_badge post_id='456']",
          description: 'Displays the claim/verification badge for a specific listing ID.',
          docUrl: ''
        },
        {
          code: "[ecm_claim_badge text='Verified']",
          description: 'Displays the claim badge with a customized text label.',
          docUrl: ''
        }
      ]
    },
    {
      title: __('Claim List', 'easy-content-manager'),
      docUrl: 'https://easycontentmanager.pro/docs/claim-list-shortcode/',
      items: [
        {
          code: "[ecm_claim_list]",
          description: 'Shows a list of all approved claims belonging to the current user.',
          docUrl: ''
        },
        {
          code: "[ecm_claim_list user_id='5']",
          description: 'Displays the list of claims for a specific user ID.',
          docUrl: ''
        },
        {
          code: "[ecm_claim_list status='pending']",
          description: 'Filters the claim list to only show claims currently awaiting approval.',
          docUrl: ''
        },
        {
          code: "[ecm_claim_list not_found_text='You haven\'t claimed any listings yet.']",
          description: 'Displays a custom message if the user has no claims in the list.',
          docUrl: ''
        },
        {
          code: "[ecm_claim_list user_id='5' status='approved' not_found_text='This user has no claims.']",
          description: 'Advanced list configuration filtering by user and status with fallback text.',
          docUrl: ''
        }
      ]
    },
    {
      title: __('Claim Count', 'easy-content-manager'),
      docUrl: 'https://easycontentmanager.pro/docs/claim-count-shortcode/',
      items: [
        {
          code: "[ecm_claim_count]",
          description: 'Displays the total number of approved claims for the current user.',
          docUrl: ''
        },
        {
          code: "[ecm_claim_count user_id='5']",
          description: 'Displays the claim count for a specific user ID.',
          docUrl: ''
        },
        {
          code: "[ecm_claim_count status='pending']",
          description: 'Displays the count of claims that are currently in pending status.',
          docUrl: ''
        },
        {
          code: "[ecm_claim_count show_label='true']",
          description: 'Displays the number with a descriptive label (e.g., "3 claims").',
          docUrl: ''
        },
        {
          code: "[ecm_claim_count show_icon='true']",
          description: 'Displays the claim count accompanied by a visual icon.',
          docUrl: ''
        },
        {
          code: "[ecm_claim_count show_label='true' show_icon='true']",
          description: 'Displays both the icon and the label alongside the numeric count.',
          docUrl: ''
        },
        {
          code: "[ecm_claim_count show_label='true' label_single='listing' label_plural='listings']",
          description: 'Displays the count with customized singular and plural terminology.',
          docUrl: ''
        },
        {
          code: "[ecm_claim_count wrapper_class='my-custom-class']",
          description: 'Wraps the count output in a custom CSS class for styling purposes.',
          docUrl: ''
        },
        {
          code: "[ecm_claim_count user_id='5' status='approved' show_label='true' show_icon='true' label_single='verified claim' label_plural='verified claims' wrapper_class='claim-counter']",
          description: 'Complete claim counter setup with specific user filtering, status, and full UI customization.',
          docUrl: ''
        }
      ]
    }
  ],
  "reactions": [
    {
      title: __('Reaction Button', 'easy-content-manager'),
      docUrl: 'https://easycontentmanager.pro/docs/reaction-shortcode-guide/',
      items: [
        {
          code: "[ecm_reaction]",
          description: 'Adds interactive reaction icons for users to engage with the current content.',
          docUrl: ''
        }
      ]
    },
    {
      title: __('Reaction Count', 'easy-content-manager'),
      docUrl: 'https://easycontentmanager.pro/docs/reaction-count-shortcode/',
      items: [
        {
          code: "[ecm_reaction_count]",
          description: 'Displays the total number of reactions for the current post.',
          docUrl: ''
        },
        {
          code: "[ecm_reaction_count post_id='123']",
          description: 'Displays the total reaction count for a specific post ID.',
          docUrl: ''
        },
        {
          code: "[ecm_reaction_count show_label='true']",
          description: 'Displays the reaction count with a default label (e.g., "3 reactions").',
          docUrl: ''
        },
        {
          code: "[ecm_reaction_count show_icon='true']",
          description: 'Displays the reaction count accompanied by a visual icon.',
          docUrl: ''
        },
        {
          code: "[ecm_reaction_count show_label='true' show_icon='true']",
          description: 'Displays both the icon and the label alongside the reaction count.',
          docUrl: ''
        },
        {
          code: "[ecm_reaction_count show_label='true' label_single='response' label_plural='responses']",
          description: 'Displays the count with customized singular and plural labels.',
          docUrl: ''
        },
        {
          code: "[ecm_reaction_count wrapper_class='my-custom-class']",
          description: 'Wraps the reaction count in a custom CSS class for specialized styling.',
          docUrl: ''
        },
        {
          code: "[ecm_reaction_count post_id='123' show_label='true' show_icon='true' label_single='emoji' label_plural='emojis' wrapper_class='reaction-counter']",
          description: 'A fully customized reaction counter for a specific post including icons, custom labels, and CSS classes.',
          docUrl: ''
        }
      ]
    },
    {
      title: __('Reaction List', 'easy-content-manager'),
      docUrl: 'https://easycontentmanager.pro/docs/reaction-list-shortcode/',
      items: [
        {
          code: "[ecm_reaction_list]",
          description: 'Shows a list of all posts the current logged-in user has reacted to.',
          docUrl: ''
        },
        {
          code: "[ecm_reaction_list user_id='5']",
          description: 'Displays the list of reacted posts for a specific user ID.',
          docUrl: ''
        },
        {
          code: "[ecm_reaction_list not_found_text='You haven\'t reacted to any posts yet.']",
          description: 'Displays the list with a custom message if no reactions are found.',
          docUrl: ''
        },
        {
          code: "[ecm_reaction_list user_id='5' not_found_text='This user has no reacted posts.']",
          description: 'Displays a specific user\'s reaction history with custom fallback text.',
          docUrl: ''
        }
      ]
    }
  ],
  "review_ratings": [
    {
      title: __('Review Form', 'easy-content-manager'),
      docUrl: 'https://easycontentmanager.pro/docs/review-form-shortcode/',
      items: [
        {
          code: "[ecm_review_form]",
          description: 'Embeds a submission form for users to write and submit reviews.',
          docUrl: ''
        },
        {
          code: '[ecm_review_form rating_layout="dropdown"]',
          description: 'Embeds a submission form for users to write and submit reviews with a dropdown rating layout.',
          docUrl: ''
        },
        {
          code: '[ecm_review_form rating_layout="star-basis"]',
          description: 'Embeds a submission form for users to write and submit reviews with a star rating layout.',
          docUrl: ''
        },
        {
          code: '[ecm_review_form rating_layout="star-with-title"]',
          description: 'Embeds a submission form for users to write and submit reviews with a star rating with review title.',
          docUrl: ''
        },
      ]
    },
    {
      title: __('Review List', 'easy-content-manager'),
      docUrl: 'https://easycontentmanager.pro/docs/review-list-shortcode/',
      items: [
        {
          code: "[ecm_review_list]",
          description: 'Displays a list of existing user reviews for a specific item.',
          docUrl: ''
        },
        {
          code: '[ecm_review_list rating_layout="minimal-breakdown"]',
          description: 'Displays a list of existing user reviews for a specific item with a minimal breakdown list layout.',
          docUrl: ''
        },
        {
          code: '[ecm_review_list rating_layout="carded-overview"]',
          description: 'Displays a list of existing user reviews for a specific item with a carded overview list layout.',
          docUrl: ''
        },
        {
          code: '[ecm_review_list rating_layout="classic-overview"]',
          description: 'Displays a list of existing user reviews for a specific item with a classic overview list layout.',
          docUrl: ''
        },
      ]
    }
  ],
  "view_counter": [
    {
      title: __('View Counter', 'easy-content-manager'),
      docUrl: 'https://easycontentmanager.pro/docs/view-counter-shortcode/',
      items: [
        {
          code: "[ecm_view_counter]",
          description: 'Shows the total number of times a page or post has been viewed.',
          docUrl: ''
        }
      ]
    }
  ],
  "upvote": [
    {
      title: __('Upvote Button', 'easy-content-manager'),
      docUrl: 'https://easycontentmanager.pro/docs/upvote-shortcode-settings/',
      items: [
        {
          code: "[ecm_upvote]",
          description: 'Adds an upvote/voting system to allow users to endorse the current content.',
          docUrl: ''
        },
        {
          code: "[ecm_upvote text='Vote Please']",
          description: 'Adds an upvote system with customized button text.',
          docUrl: ''
        }
      ]
    },
    {
      title: __('Upvote Count', 'easy-content-manager'),
      docUrl: 'https://easycontentmanager.pro/docs/upvote-count-shortcode/',
      items: [
        {
          code: "[ecm_upvote_count]",
          description: 'Displays the total number of upvotes for the current post.',
          docUrl: ''
        },
        {
          code: "[ecm_upvote_count post_id='123']",
          description: 'Displays the total upvote count for a specific post ID.',
          docUrl: ''
        },
        {
          code: "[ecm_upvote_count show_label='true']",
          description: 'Displays the count with a default label (e.g., "3 votes").',
          docUrl: ''
        },
        {
          code: "[ecm_upvote_count show_icon='true']",
          description: 'Displays the upvote count accompanied by a visual icon.',
          docUrl: ''
        },
        {
          code: "[ecm_upvote_count show_label='true' show_icon='true']",
          description: 'Displays both the icon and the label alongside the count.',
          docUrl: ''
        },
        {
          code: "[ecm_upvote_count show_label='true' label_single='vote' label_plural='votes']",
          description: 'Displays the count with customized singular and plural labels.',
          docUrl: ''
        },
        {
          code: "[ecm_upvote_count wrapper_class='my-custom-class']",
          description: 'Wraps the count display in a custom CSS class for specialized styling.',
          docUrl: ''
        },
        {
          code: "[ecm_upvote_count post_id='123' show_label='true' show_icon='true' label_single='like' label_plural='likes' wrapper_class='upvote-counter']",
          description: 'A fully customized counter for a specific post including icons, custom labels, and CSS classes.',
          docUrl: ''
        }
      ]
    },
    {
      title: __('Upvote List', 'easy-content-manager'),
      docUrl: 'https://easycontentmanager.pro/docs/upvote-list-shortcode/',
      items: [
        {
          code: "[ecm_upvote_list]",
          description: 'Shows a list of all posts the current logged-in user has upvoted.',
          docUrl: ''
        },
        {
          code: "[ecm_upvote_list user_id='5']",
          description: 'Displays the list of upvoted posts for a specific user ID.',
          docUrl: ''
        },
        {
          code: "[ecm_upvote_list not_found_text='You haven\'t upvoted any posts yet.']",
          description: 'Displays the list with a custom message if no upvoted posts are found.',
          docUrl: ''
        },
        {
          code: "[ecm_upvote_list user_id='5' not_found_text='This user has no upvoted posts.']",
          description: 'Displays a specific user\'s upvotes with custom fallback text.',
          docUrl: ''
        }
      ]
    }
  ],
  "frontend_submission": [
    {
      title: __('Frontend Submission', 'easy-content-manager'),
      docUrl: 'https://easycontentmanager.pro/docs/frontend-submission-shortcode/',
      items: [
        {
          code: "[ecm_frontend_submission post_type='post']",
          description: 'Inherit the ECM submission UI for a specific post type.',
          docUrl: ''
        },
        {
          code: "[ecm_frontend_submission post_type='post' show_list='true' show_form='false']",
          description: 'Inherit the ECM submission UI for a specific post type with list and form controls.',
          docUrl: ''
        },
      ]
    }
  ],
  "user_reviews": [
    {
      "title": "User Reviews",
      "docUrl": "https://easycontentmanager.pro/docs/user-reviews-shortcode/",
      "items": [
        {
          "code": "[ecm_user_reviews]",
          "description": "Show the current logged-in user's reviews.",
          "docUrl": ""
        },
        {
          "code": "[ecm_user_reviews user_id='5']",
          "description": "Show reviews for a specific user ID.",
          "docUrl": ""
        },
        {
          "code": "[ecm_user_reviews status='all']",
          "description": "Show all reviews including pending and approved.",
          "docUrl": ""
        },
        {
          "code": "[ecm_user_reviews limit='10']",
          "description": "Display reviews in a specific limit.",
          "docUrl": ""
        },
        {
          "code": "[ecm_user_reviews orderby='comment_date' order='ASC']",
          "description": "Display reviews with custom ordering.",
          "docUrl": ""
        },
        {
          "code": "[ecm_frontend_submission post_type='post']",
          "description": "Inherit the ECM submission UI for a specific post type.",
          "docUrl": ""
        },
        {
          "code": "[ecm_frontend_submission post_type='post' show_list='true' show_form='false']",
          "description": "Inherit the ECM submission UI for a specific post type with list and form controls.",
          "docUrl": ""
        }
      ]
    }
  ]
};
