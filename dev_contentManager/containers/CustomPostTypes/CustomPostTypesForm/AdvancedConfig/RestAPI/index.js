import React from 'react';
import { __ } from '@wordpress/i18n';
import Checkbox from '@ECMComponents/Formik/Checkbox';
import { useFormikContext } from 'formik';
import { Flex } from '@chakra-ui/react';

import './styles.scss';

const RestAPI = () => {
	const { setFieldValue } = useFormikContext();

	return (
		<Flex
			flexDirection="column"
			gap="6"
			padding="12px 12px"
			maxHeight="372px"
			minHeight="372px"
		>
			<h1 className="content-manager-title">
				{__(
					'This feature not available at this moment.',
					'easy-content-manager'
				)}
			</h1>

			{/* <Checkbox
                name='advanced_config.rest_api.show_in_rest_api'
                title={__('Show In REST API', 'easy-content-manager')}
                subtitle={__('Exposes this post type in the REST API. Required to use the block editor.', 'easy-content-manager')}
            />

            <div className='content-manager-column-items content-manager-advanced-config-rest-api__content'>
                <div className='content-manager-column-items content-manager-advanced-config-rest-api__content-item'>
                    <div className='content-manager-advanced-config-rest-api__content-item-header'>
                        <p className='content-manager-title'>{__('Base URL', 'easy-content-manager')}</p>
                        <span className='content-manager-sub-title'>{__('The base URL for the post type REST API URLs.', 'easy-content-manager')}</span>
                    </div>
                    <Field
                        name='advanced_config.rest_api.base_url'
                        className='content-manager-input content-manager-border content-manager-p-02'
                        onBlur={(e) => setFieldValue('advanced_config.rest_api.base_url', e.target.value)}
                    />
                </div>
                <div className='content-manager-column-items content-manager-advanced-config-rest-api__content-item'>
                    <div className='content-manager-advanced-config-rest-api__content-item-header'>
                        <p className='content-manager-title'>{__('Namespace Route', 'easy-content-manager')}</p>
                        <span className='content-manager-sub-title'>{__('The namespace part of the REST API URL.', 'easy-content-manager')}</span>
                    </div>
                    <Field
                        name='advanced_config.rest_api.namespace_route'
                        className='content-manager-input content-manager-border content-manager-p-02'
                        onBlur={(e) => setFieldValue('advanced_config.rest_api.namespace_route', e.target.value)}
                    />
                </div>
                <div className='content-manager-column-items content-manager-advanced-config-rest-api__content-item'>
                    <div className='content-manager-advanced-config-rest-api__content-item-header'>
                        <p className='content-manager-title'>{__('Controller Class', 'easy-content-manager')}</p>
                        <span className='content-manager-sub-title'>{__("Optional custom controller to use instead of `WP_REST_Posts_Controller`.", 'easy-content-manager')}</span>
                    </div>
                    <Field
                        name='advanced_config.rest_api.controller_class'
                        className='content-manager-input content-manager-border content-manager-p-02'
                        onBlur={(e) => setFieldValue('advanced_config.rest_api.controller_class', e.target.value)}
                    />
                </div>
            </div> */}
		</Flex>
	);
};

export default RestAPI;
