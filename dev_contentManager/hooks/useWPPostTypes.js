import React, { useEffect, useState } from 'react';
import { __ } from '@wordpress/i18n';
import { useDispatch } from 'react-redux';
import { getExistingPostType } from '@ECMRedux/Slices/fieldGroupsSlice/fieldGroupsSlice';
import { capitalizeFirstLetter } from '@ECMUtils/helper';

export default function useWPPostTypes() {
    const [postTypesOptions, setPostTypesOptions] = useState([]);

    const dispatch = useDispatch();

    useEffect(() => {
        dispatch(getExistingPostType()).then((response) => {
            const options = Object.entries(response.payload).map(
                ([key, value]) => ({
                    value: key,
                    label: capitalizeFirstLetter(value),
                })
            );
            setPostTypesOptions(options);
        });
    }, []);

    return postTypesOptions;
};
