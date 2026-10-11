import { useEffect, useState } from 'react';
import { __ } from '@wordpress/i18n';
import { useDispatch } from 'react-redux';
import FormInner from './FormInner';
import { Formik } from 'formik';
import TopBar from '@ECMComponents/TopBar';
import { useNavigate } from 'react-router-dom';
import { getTaxonomiesInitialValues } from './helper';
import { Button } from '@chakra-ui/react';
import { primaryBtn } from '../../../assets/scss/chakra/recipe';
import {
	createCustomTaxonomy,
	getSingleCustomTaxonomy,
	updateCustomTaxonomy,
} from '@ECMRedux/Slices/taxonomySlice/taxonomySlice';
import { useSelector } from 'react-redux';
import { route_path } from '@ECMUtils/helper';
import Preloader from '@ECMComponents/Loader/Preloader';
import ECMLabel from '@ECMComponents/Labels/ECMLabel';
import { showNotification } from '@ECMRedux/Slices/notificationSlice/notificationSlice';
import { updateTaxonomyLocalize } from '@ECMRedux/Slices/localizeSlice/localizeSlice';

import './styles.scss';

const AddTaxonomiesForm = ({ action, id, appName }) => {
	const taxonomies = useSelector((state) => state.taxonomies);
	const [loading, setLoading] = useState(false);

	const post_id = Number(id);

	const dispatch = useDispatch();
	const navigate = useNavigate();

	useEffect(() => {
		if ((!taxonomies.data || !taxonomies?.data?.length) && post_id) {
			setLoading(true);
			dispatch(getSingleCustomTaxonomy(post_id)).finally(() => {
				setLoading(false);
			});
		}
	}, []);

	const onSubmitHandler = (values, actions) => {
		if (values?.object_type && !values?.object_type?.length) {
			dispatch(
				showNotification({
					message: __(
						'Please select at least one Post Type',
						'easy-content-manager'
					),
					type: 'error',
					isShow: true,
				})
			);
			actions.setSubmitting(false);
			return;
		}
		if (post_id) {
			dispatch(updateCustomTaxonomy(values))
				.then(({ payload }) => {
					dispatch(
						updateTaxonomyLocalize({
							taxonomySlug: payload.post_content.taxonomy_slug,
							custom_fields: payload.post_content.custom_fields,
							title: payload.post_content.labels.singular_name,
							rest_base: payload.post_content.rest_base,
							rest_namespace: payload.post_content.rest_namespace,
						})
					);
				})
				.finally(() => {
					actions.setSubmitting(false);
				});
		} else {
			dispatch(createCustomTaxonomy(values))
				.then(({ payload }) => {
					dispatch(
						updateTaxonomyLocalize({
							taxonomySlug: payload.post_content.taxonomy_slug,
							custom_fields: payload.post_content.custom_fields,
							title: payload.post_content.labels.singular_name,
							rest_base: payload.post_content.rest_base,
							rest_namespace: payload.post_content.rest_namespace,
						})
					);
					if (payload?.ID) {
						navigate(
							`${route_path}/admin.php?page=easy-content-manager-taxonomy&action=edit&id=${payload?.ID}`
						);
					}
				})
				.finally(() => {
					actions.setSubmitting(false);
				});
		}
	};

	return (
		<>
			{loading ? (
				<Preloader />
			) : (
				<Formik
					enableReinitialize={true}
					initialValues={getTaxonomiesInitialValues(
						post_id,
						taxonomies.data
					)}
					onSubmit={onSubmitHandler}
				>
					{({ handleSubmit, dirty, isSubmitting, values }) => {
						return (
							<>
								<TopBar
									leftContent={() => (
										<>
											<span className="ecm-topbar-logo ecm-primary easy-content-manager-icon easy-content-manager-icon--ecm-primary" />

											<span className="easy-content-manager-icon easy-content-manager-icon--angle-right" />

											<ECMLabel
												type="subtitle"
												fontWeight="medium"
												label={__(
													'Taxonomies',
													'easy-content-manager'
												)}
												href={`${route_path}admin.php?page=${appName}-taxonomy`}
											/>

											<span className="easy-content-manager-icon easy-content-manager-icon--angle-right" />

											<ECMLabel
												as="h2"
												type="subtitle"
												margin="0"
												fontWeight="medium"
												color="var(--ecm-primary)"
												label={__(
													`${values?.singular_name ? values?.singular_name : 'New Item'}`,
													'easy-content-manager'
												)}
											/>
										</>
									)}
									rightContent={() => (
										<Button
											{...primaryBtn}
											disabled={!dirty}
											loading={isSubmitting}
											onClick={handleSubmit}
										>
											{post_id
												? __(
														'Update Taxonomy',
														'easy-content-manager'
													)
												: __(
														`Add Taxonomy`,
														'easy-content-manager'
													)}
										</Button>
									)}
								/>
								<FormInner />
							</>
						);
					}}
				</Formik>
			)}
		</>
	);
};

export default AddTaxonomiesForm;
