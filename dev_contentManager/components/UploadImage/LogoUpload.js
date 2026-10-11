import React, { useEffect, useState } from 'react';
import { API, plugin_root_url, runUploader } from '@ECMUtils/helper';
import Button from '../Button';
import PropTypes from 'prop-types';
import { __ } from '@wordpress/i18n';

import './styles/logoUpload.scss';

const propTypes = {
	name: PropTypes.string,
	value: PropTypes.number,
	saveValueHandler: PropTypes.func,
	imageSize: PropTypes.string,
	supportedFile: PropTypes.string,
};

export default function LogoUpload(props) {
	const {
		value = 0,
		name = '',
		saveValueHandler = () => {},
		imageSize = '700x430 pixels',
		supportedFile = '.jpg, .jpeg, .gif, .png',
	} = props;

	const [attachment, setAttachment] = useState({});

	useEffect(() => {
		if (value) {
			API.get(`wp/v2/media/${value}`).then((res) => {
				setAttachment({ url: res.data.source_url });
			});
		} else {
			setAttachment({});
		}
	}, [value]);

	const LogoUploadHandler = (e) => {
		e.preventDefault();
		runUploader().then((res) => {
			saveValueHandler(name, res.id);
			setAttachment(res);
		});
	};

	const removeFeaturedImage = () => {
		saveValueHandler(name, 0);
		setAttachment({});
	};

	return (
		<React.Fragment>
			<div className="content-manager-upload-logo">
				<div className="content-manager-upload-logo__entry-left">
					{Object.keys(attachment).length === 0 ? (
						<div className="content-manager-upload-logo__entry-left-empty">
							<div className="content-manager-upload-empty-image-wrap">
								<img
									src={
										plugin_root_url +
										'assets/images/thumbnail-placeholder-icon.svg'
									}
									alt="upload"
								/>
							</div>
						</div>
					) : (
						<>
							{Object.keys(attachment).length > 0 && (
								<div
									className="content-manager-upload-logo-wrap"
									style={{
										backgroundImage: `url(${attachment.url})`,
										backgroundRepeat: 'no-repeat',
										backgroundSize: 'contain',
										backgroundPosition: 'center center',
									}}
								/>
							)}
						</>
					)}
				</div>

				<div className="content-manager-upload-logo__entry-right">
					<div className="content-manager-flex">
						<Button
							type="button"
							onClick={(e) => LogoUploadHandler(e)}
							preset="sky-blue"
							size="sm"
							label={
								Object.keys(attachment).length > 0
									? __('Change', 'easy-content-manager')
									: __('Upload', 'easy-content-manager')
							}
						/>
						{Object.keys(attachment).length > 0 && (
							<Button
								onClick={removeFeaturedImage}
								type="button"
								preset="transparent"
								border="gray"
								borderRadius="rounded"
								size="sm"
								label={__('Remove', 'easy-content-manager')}
							/>
						)}
					</div>

					<div className="content-manager-title-subtitle">
						<p className="content-manager-sub-title-light">
							{supportedFile}
						</p>
						<p className="content-manager-sub-title-light">
							Recommended size: {imageSize}
						</p>
					</div>
				</div>
			</div>
		</React.Fragment>
	);
}

LogoUpload.propTypes = propTypes;
