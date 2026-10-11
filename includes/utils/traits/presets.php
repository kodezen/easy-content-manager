<?php

namespace EasyContentManager\Utils\Traits;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

trait Presets {


	public static function get_preset_args( $preset_name, $field ) {
		$preset['portfolio'] = [
			'text-field' => [
				'general' => [
					'field_label' => 'Client Name',
					'field_name' => 'client_name',
				],
				'post_meta_args' => [
					'label' => 'Client Name',
				]
			],
			'image-field' => [
				'general' => [
					'field_label' => 'Image',
					'field_name' => 'image',
				],
				'post_meta_args' => [
					'label' => 'Image',
				],
				'validation' => [
					'image_type' => 'single-image',
					'image_file_format' => 'any'
				]
			],
			'date-field' => [
				'general' => [
					'field_label' => 'Year',
					'field_name' => 'year',
				],
				'post_meta_args' => [
					'label' => 'Year',
				],
				'validation' => [
					'date_format' => 'yyyy-mm-dd',
				],
			],
			'url-field' => [
				'general' => [
					'field_label' => 'Link',
					'field_name' => 'link',
				],
				'post_meta_args' => [
					'label' => 'Link',
				],
			],
			'url-field-1' => [
				'general' => [
					'field_label' => 'Github',
					'field_name' => 'github',
				],
				'post_meta_args' => [
					'label' => 'Github',
				],
			],
			'switch-field' => [
				'general' => [
					'field_label' => 'Featured',
					'field_name' => 'featured',
				],
				'post_meta_args' => [
					'label' => 'Featured',
				],
			],
		];
		$preset['projects'] = [
			'text-field' => [
				'general' => [
					'field_label' => 'Client Name',
					'field_name' => 'client_name',
				],
				'post_meta_args' => [
					'label' => 'Client Name',
				]
			],
			'image-field' => [
				'general' => [
					'field_label' => 'Client Logo',
					'field_name' => 'client_logo',
				],
				'post_meta_args' => [
					'label' => 'Client Logo',
				],
				'validation' => [
					'image_type' => 'single-image',
					'image_file_format' => 'any'
				]
			],
			'image-field-1' => [
				'general' => [
					'field_label' => 'Image',
					'field_name' => 'image',
				],
				'post_meta_args' => [
					'label' => 'Image',
				],
				'validation' => [
					'image_type' => 'single-image',
					'image_file_format' => 'any'
				]
			],
			'date-field' => [
				'general' => [
					'field_label' => 'Year',
					'field_name' => 'year',
				],
				'post_meta_args' => [
					'label' => 'Year',
				],
				'validation' => [
					'date_format' => 'yyyy-mm-dd',
				],
			],
			'url-field' => [
				'general' => [
					'field_label' => 'Link',
					'field_name' => 'link',
				],
				'post_meta_args' => [
					'label' => 'Link',
				],
			],
			'switch-field' => [
				'general' => [
					'field_label' => 'Featured',
					'field_name' => 'featured',
					'field_default_value' => true,
				],
				'post_meta_args' => [
					'label' => 'Featured',
				],
			],
		];
		$preset['team-members'] = [
			'email-field' => [
				'general' => [
					'field_label' => 'Email',
					'field_name' => 'email',
				],
				'post_meta_args' => [
					'label' => 'Email',
				],
			],
			'text-field' => [
				'general' => [
					'field_label' => 'Phone',
					'field_name' => 'phone',
				],
				'post_meta_args' => [
					'label' => 'Phone',
				]
			],
			'image-field' => [
				'general' => [
					'field_label' => 'Image',
					'field_name' => 'image',
				],
				'post_meta_args' => [
					'label' => 'Image',
				],
				'validation' => [
					'image_type' => 'single-image',
					'image_file_format' => 'any'
				]
			],
			'url-field' => [
				'general' => [
					'field_label' => 'Website',
					'field_name' => 'website',
				],
				'post_meta_args' => [
					'label' => 'Website',
				],
			],
			'url-field-1' => [
				'general' => [
					'field_label' => 'LinkedIn',
					'field_name' => 'linkedin',
				],
				'post_meta_args' => [
					'label' => 'LinkedIn',
				],
			],
			'url-field-2' => [
				'general' => [
					'field_label' => 'Facebook',
					'field_name' => 'facebook',
				],
				'post_meta_args' => [
					'label' => 'Facebook',
				],
			],
		];
		$preset['recipes'] = [
			'rich-text-field' => [
				'general' => [
					'field_label' => 'Cooking',
					'field_name' => 'cooking',
				],
				'post_meta_args' => [
					'label' => 'Cooking',
				],
			],
			'image-field' => [
				'general' => [
					'field_label' => 'Image',
					'field_name' => 'image',
				],
				'post_meta_args' => [
					'label' => 'Image',
				],
				'validation' => [
					'image_type' => 'single-image',
					'image_file_format' => 'any'
				]
			],
			'time-field' => [
				'general' => [
					'field_label' => 'Preparation Time',
					'field_name' => 'preparation_time',
				],
				'post_meta_args' => [
					'label' => 'Preparation Time',
				],
			],
			'image-field-1' => [
				'general' => [
					'field_label' => 'Thumbnail Image',
					'field_name' => 'thumbnail_image',
				],
				'post_meta_args' => [
					'label' => 'Thumbnail Image',
				],
				'validation' => [
					'image_type' => 'single-image',
					'image_file_format' => 'any'
				]
			],
			'switch-field' => [
				'general' => [
					'field_label' => 'Featured',
					'field_name' => 'featured',
					'field_default_value' => true,
				],
				'post_meta_args' => [
					'label' => 'Featured',
				],
			],
		];
		$preset['jobs'] = [
			'text-field' => [
				'general' => [
					'field_label' => 'Location',
					'field_name' => 'location',
				],
				'post_meta_args' => [
					'label' => 'Location',
				],
			],
			'number-field' => [
				'general' => [
					'field_label' => 'Salary',
					'field_name' => 'salary',
				],
				'post_meta_args' => [
					'label' => 'Salary',
				],
				'validation' => [
					'number_format' => 'any',
				],
			],
			'text-field-2' => [
				'general' => [
					'field_label' => 'Work Hours',
					'field_name' => 'work_hours',
				],
				'post_meta_args' => [
					'label' => 'Work Hours',
				],
			],
			'rich-text-field-1' => [
				'general' => [
					'field_label' => 'Requirements',
					'field_name' => 'requirements',
				],
				'post_meta_args' => [
					'label' => 'Requirements',
				],
			],
			'number-field-1' => [
				'general' => [
					'field_label' => 'Opening',
					'field_name' => 'opening',
				],
				'post_meta_args' => [
					'label' => 'Opening',
				],
				'validation' => [
					'number_format' => 'any',
				],
			],
			'date-field' => [
				'general' => [
					'field_label' => 'Application Deadline',
					'field_name' => 'application_deadline',
				],
				'post_meta_args' => [
					'label' => 'Application Deadline',
				],
				'validation' => [
					'date_format' => 'yyyy-mm-dd',
				],
			],
		];
		$preset['clients'] = [
			'text-field' => [
				'general' => [
					'field_label' => 'Product Name',
					'field_name' => 'product_name',
				],
				'post_meta_args' => [
					'label' => 'Product Name',
				],
			],
			'email-field' => [
				'general' => [
					'field_label' => 'Email',
					'field_name' => 'email',
				],
				'post_meta_args' => [
					'label' => 'Email',
				],
			],
			'text-field-2' => [
				'general' => [
					'field_label' => 'Phone',
					'field_name' => 'phone',
				],
				'post_meta_args' => [
					'label' => 'Phone',
				],
			],
			'date-field' => [
				'general' => [
					'field_label' => 'Purchase Date/Product Delivered',
					'field_name' => 'purchase_date/product_delivered',
				],
				'post_meta_args' => [
					'label' => 'Purchase Date/Product Delivered',
				],
				'validation' => [
					'date_format' => 'yyyy-mm-dd',
				],
			],
			'image-field' => [
				'general' => [
					'field_label' => 'Image',
					'field_name' => 'image',
				],
				'post_meta_args' => [
					'label' => 'Image',
				],
				'validation' => [
					'image_type' => 'single-image',
					'image_file_format' => 'any'
				]
			],
			'image-field-1' => [
				'general' => [
					'field_label' => 'Product Image/Logo',
					'field_name' => 'product_image/logo',
				],
				'post_meta_args' => [
					'label' => 'Product Image/Logo',
				],
				'validation' => [
					'image_type' => 'single-image',
					'image_file_format' => 'any'
				]
			],
		];
		$preset['listings'] = [
			'select-field' => [
				'general' => [
					'field_label' => 'Listing Type',
					'field_name' => 'listing_type',
				],
				'post_meta_args' => [
					'label' => 'Listing Type',
				],
			],
			'text-field' => [
				'general' => [
					'field_label' => 'Rent or Sale Price',
					'field_name' => 'rent_or_sale_price',
				],
				'post_meta_args' => [
					'label' => 'Rent or Sale Price',
				],
			],
			'number-field' => [
				'general' => [
					'field_label' => 'No of Rooms',
					'field_name' => 'no_of_rooms',
				],
				'post_meta_args' => [
					'label' => 'No of Rooms',
				],
				'validation' => [
					'number_format' => 'any',
				],
			],
			'number-field-1' => [
				'general' => [
					'field_label' => 'No of Baths',
					'field_name' => 'no_of_baths',
				],
				'post_meta_args' => [
					'label' => 'No of Baths',
				],
				'validation' => [
					'number_format' => 'any',
				],
			],
			'text-field-2' => [
				'general' => [
					'field_label' => 'Square Feet',
					'field_name' => 'square_feet',
				],
				'post_meta_args' => [
					'label' => 'Square Feet',
				],
			],
			'switch-field' => [
				'general' => [
					'field_label' => 'Availablity',
					'field_name' => 'availablity',
					'field_default_value' => true,
				],
				'post_meta_args' => [
					'label' => 'Availablity',
				],
			],
			'image-field' => [
				'general' => [
					'field_label' => 'Property Image',
					'field_name' => 'property_image',
				],
				'post_meta_args' => [
					'label' => 'Property Image',
				],
				'validation' => [
					'image_type' => 'single-image',
					'image_file_format' => 'any'
				]
			],
			'date-field' => [
				'general' => [
					'field_label' => 'Listing Created Date',
					'field_name' => 'listing_created_date',
				],
				'post_meta_args' => [
					'label' => 'Listing Created Date',
				],
				'validation' => [
					'date_format' => 'yyyy-mm-dd',
				],
			],
			'text-field-3' => [
				'general' => [
					'field_label' => 'Address',
					'field_name' => 'address',
				],
				'post_meta_args' => [
					'label' => 'Address',
				],
			],
			'text-field-4' => [
				'general' => [
					'field_label' => 'Agent Contact Info',
					'field_name' => 'agent_contact_info',
				],
				'post_meta_args' => [
					'label' => 'Agent Contact Info',
				],
			],
		];

		return $preset[ $preset_name ][ $field ];
	}
}
