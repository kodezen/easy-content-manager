import { useEffect, useState } from 'react';
import { __, sprintf } from '@wordpress/i18n';
import { useNavigate } from 'react-router-dom';
import { useDispatch, useSelector } from 'react-redux';
import { Formik } from 'formik';
import { capitalizeFirstLetter, route_path } from '@ECMUtils/helper';
import { getPostInitialValues } from './helper';
import {
  getPostType,
  updateCollection,
  createCollection,
} from '@ECMRedux/Slices/collectionDataSlice/collectionDataSlice';
import { fetchUpdatedMenu } from '@ECMRedux/Slices/menuSlice/menuSlice';
import { showNotification } from '@ECMRedux/Slices/notificationSlice/notificationSlice';
import TopBar from '@ECMComponents/TopBar';
import Preloader from '@ECMComponents/Loader/Preloader';
import FormInner from './FormInner';
import { Button } from '@chakra-ui/react';
import NavigationBlocker from '@ECMComponents/NavigationBlocker';
import ECMLabel from '@ECMComponents/Labels/ECMLabel';
import {
  createPostTypeLocalize,
  updatePostTypeLocalize,
} from '@ECMRedux/Slices/localizeSlice/localizeSlice';
import { primaryBtn } from '../../../../assets/scss/chakra/recipe';
import SubTopBar from '@ECMComponents/SubTopBar';

const requiredFields = [
  {
    key: 'plural_name',
    message: __('Please enter a Plural label', 'easy-content-manager'),
  },
  {
    key: 'singular_name',
    message: __('Please enter a Singular label', 'easy-content-manager'),
  },
  {
    key: 'post_slug',
    message: __('Please enter a Post Type Key', 'easy-content-manager'),
  },
];

export default function CustomPostTypesForm({ post_type, id }) {
  const { totalPosts } = useSelector((state) => state.post_types);
  const filteredData = totalPosts?.find(
    (item) => parseInt(item.ID) === parseInt(id)
  );
  const [loading, setLoading] = useState(false);
  const [isError, setIsError] = useState(false);

  const dispatch = useDispatch();
  const navigate = useNavigate();
  const ID = parseInt(id);

  useEffect(() => {
    if (!filteredData && ID) {
      setLoading(true);
      dispatch(getPostType({ post_id: ID })).then(({ payload }) => {
        setLoading(false);
      });
    }
  }, [ID]);

  const onSubmitHandler = async (values, actions) => {
    const { duplicate_field_names, ...rest } = values;
    actions.setSubmitting(true);

    if (
      values.advanced_config_data.taxonomies.length > 0 &&
      !values.advanced_configuration
    ) {
      dispatch(
        showNotification({
          message: __(
            'You must enable advanced configuration to use taxonomies.',
            'easy-content-manager'
          ),
          isShow: true,
          type: 'error',
        })
      );
      actions.setSubmitting(false);
      return;
    }

    if (values.duplicate_field_names) {
      dispatch(
        showNotification({
          message:
            'You have duplicate field names. Please check your fields.',
          isShow: true,
          type: 'error',
        })
      );
      actions.setSubmitting(false);
      return;
    }
    if (isError) {
      dispatch(
        showNotification({
          message: 'Something went wrong! please check your fields.',
          isShow: true,
          type: 'error',
        })
      );
      actions.setSubmitting(false);
      return;
    }

    for (const field of requiredFields) {
      if (!rest?.[field.key]) {
        dispatch(
          showNotification({
            message: field.message,
            isShow: true,
            type: 'error',
          })
        );
        actions.setSubmitting(false);
        return;
      }
    }

    if (rest?.post_id) {
      dispatch(updateCollection(rest))
        .then(({ payload }) => {
          dispatch(fetchUpdatedMenu());
          const updatedLocalizeData = {
            custom_fields: payload?.post_content.custom_fields,
            rest_base: payload?.post_content.rest_base,
            rest_namespace: payload?.post_content.rest_namespace,
            taxonomy: payload?.post_content.taxonomies,
            title: payload?.post_title,
            supports: payload?.post_content.supports,
            postTypeSlug: payload?.post_content.post_slug,
          };
          dispatch(updatePostTypeLocalize(updatedLocalizeData));
        })
        .finally(() => actions.setSubmitting(false));
    } else {
      dispatch(createCollection(rest))
        .then(({ payload: data }) => {
          if (data?.ID) {
            dispatch(fetchUpdatedMenu());

            navigate(
              `${route_path}/admin.php?page=easy-content-manager-post-types&path=edit-post&action=edit&id=${data?.ID}`
            );

            const updatedLocalizeData = {
              custom_fields: data?.post_content?.custom_fields,
              rest_base: data?.post_content?.rest_base,
              rest_namespace: data?.post_content?.rest_namespace,
              taxonomy: data?.post_content?.taxonomies,
              title: data?.post_title,
              supports: data?.post_content?.supports,
              postTypeSlug: data?.post_slug,
            };
            dispatch(createPostTypeLocalize(updatedLocalizeData));
          }
        })
        .finally(() => actions.setSubmitting(false));
    }
  };

  return (
    <>
      {loading ? (
        <Preloader />
      ) : (
        <Formik
          enableReinitialize={true}
          initialValues={getPostInitialValues(ID, totalPosts)}
          onSubmit={onSubmitHandler}
        >
          {({ handleSubmit, values, dirty, isSubmitting }) => {
            const allFieldsFilled = Object.values(values).every(
              (value) =>
                value?.singular_name !== null &&
                value?.post_slug !== null &&
                value?.singular_name !== '' &&
                value?.post_slug !== ''
            );

            return (
              <>
                <NavigationBlocker when={dirty} />
                <TopBar
                  leftContent={() => (
                    <>
                      <span className="ecm-topbar-logo ecm-primary easy-content-manager-icon easy-content-manager-icon--ecm-primary" />

                      <span className="easy-content-manager-icon easy-content-manager-icon--angle-right" />

                      <ECMLabel
                        type="subtitle"
                        fontWeight="medium"
                        label={__(
                          'Post Types',
                          'easy-content-manager'
                        )}
                        href={`${route_path}/admin.php?page=easy-content-manager-post-types`}
                      />

                      {values?.plural_name ? (
                        <>
                          <span className="easy-content-manager-icon easy-content-manager-icon--angle-right" />
                          <ECMLabel
                            as="h2"
                            type="subtitle"
                            margin="0"
                            fontWeight="medium"
                            color="var(--ecm-primary)"
                            label={__(
                              capitalizeFirstLetter(
                                values?.plural_name
                              ),
                              'easy-content-manager'
                            )}
                          />
                        </>
                      ) : (
                        ''
                      )}
                    </>
                  )}
                  rightContent={() => (
                    <>
                      <Button
                        {...primaryBtn}
                        disabled={
                          !allFieldsFilled || !dirty
                        }
                        loading={isSubmitting}
                        onClick={handleSubmit}
                      >
                        {ID
                          ? __('Update Post Type', 'easy-content-manager')
                          : __('Add Post Type', 'easy-content-manager')}
                      </Button>
                    </>
                  )}
                />

                <div className="content-manager-page-content">
                  <SubTopBar
                    heading={sprintf(
                      __('%s Post Type', 'easy-content-manager'),
                      ID ? 'Update' : 'Create'
                    )}
                  />

                  <FormInner
                    isError={isError}
                    setIsError={setIsError}
                  />
                </div>
              </>
            );
          }}
        </Formik>
      )}
    </>
  );
};
