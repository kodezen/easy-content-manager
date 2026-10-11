import { useState, useEffect } from 'react';
import { API, plugin_root_url } from '@ECMUtils/helper';
import { __ } from '@wordpress/i18n';

import './styles/productImage.scss';

const ProductImage = ({ singleFeaturedMedia, title }) => {
	const [imageUrl, setImageUrl] = useState('');

	useEffect(() => {
		if (singleFeaturedMedia) {
			API.get(`wp/v2/media/${singleFeaturedMedia}`)
				.then((res) => {
					setImageUrl(res.data.source_url);
				})
				.catch((error) => {
					// eslint-disable-next-line no-console
					console.error(error);
				});
		}
	}, [singleFeaturedMedia]);

	return imageUrl ? (
		<img
			src={imageUrl}
			alt={title}
			className="content-manager-product-image"
		/>
	) : (
		<img
			src={plugin_root_url + 'assets/images/imagePlaceHolder.svg'}
			alt={__('empty-image', 'easy-content-manager')}
		/>
	);
};

export default ProductImage;
