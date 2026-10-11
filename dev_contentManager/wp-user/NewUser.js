import React, { useEffect, useState } from 'react';
import { __ } from '@wordpress/i18n';
import { Flex } from '@chakra-ui/react';
import { getCustomDataForUser, makeRequest } from '@ECMUtils/helper';
import { SequentialFieldRenderer } from '@ECMComponents/RenderField/helper';
import { useWpUserFormValues } from '@ECMHooks/useWPUserFormValues';

export default function NewUser() {
  const [existingValues, setExistingValues] = useState(null);
  const [formValues, setWpFieldValue] = useWpUserFormValues(existingValues);
  const customFieldsData = getCustomDataForUser();

  const saveValueHandler = (name, value) => {
    setWpFieldValue(name, value, true);
  };

  useEffect(() => {
    if (Boolean(Number(formValues.user_id))) {
      makeRequest('get_user_meta', {
        user_id: formValues.user_id
      })
        .then((response) => {
          setExistingValues(response.data.data.meta);
        })
    }
  }, [formValues.user_id]);

  return (
    <Flex direction="column" gap={4} maxW="700px" width="100%">
      <SequentialFieldRenderer
        customData={customFieldsData}
        values={formValues.meta}
        setFieldValue={saveValueHandler}
      />
    </Flex>
  );
};
