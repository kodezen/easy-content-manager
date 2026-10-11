import { API, namespace } from "@ECMUtils/helper";
import { createAsyncThunk } from "@reduxjs/toolkit";
import { showNotification } from "../notificationSlice/notificationSlice";
import { __ } from '@wordpress/i18n';

export const getPostsForPriority = createAsyncThunk(
    'ecm/getPostsForPriority',
    ({per_page = 100, page = 1, post_type, search = ''}, thunkAPI) => {
        const params = {
            per_page,
            page,
            post_type,
        };

        if (search) params.search = search;
        return API.get(`${namespace}posts-priority`, {
            params,
        })
            .then((res) => {
                return {
                    data: res.data,
                    totalItems: Number(res.headers['x-wp-total']),
                    page,
                    per_page,
                };
            })
            .catch((error) => {
                thunkAPI.dispatch(
                    showNotification({
                        message:
                            error?.response?.data?.message ?? error.message,
                        isShow: true,
                        type: 'error',
                    })
                );
                return thunkAPI.rejectWithValue(
                    error?.response?.data?.message ?? error.message
                );
            });
    }
);

/**
 * Moves a single post to a target priority rank among every post of its
 * type. The server shifts the rest to keep priorities unique, including
 * posts not currently loaded in the browser (e.g. hidden behind a search
 * filter or on another page).
 */
export const movePostPriority = createAsyncThunk(
    'ecm/movePostPriority',
    ({ postId, priority }, thunkAPI) => {
        return API.post(`${namespace}posts-priority`, {
            move: { post_id: postId, priority },
        })
            .then((res) => {
                return res.data;
            })
            .catch((error) => {
                thunkAPI.dispatch(
                    showNotification({
                        message:
                            error?.response?.data?.message ?? error.message,
                        isShow: true,
                        type: 'error',
                    })
                );
                return thunkAPI.rejectWithValue(
                    error?.response?.data?.message ?? error.message
                );
            });
    }
);

export const updatePostPriority = createAsyncThunk(
    'ecm/updatePostPriority',
    (formData, thunkAPI) => {
        return API.post(`${namespace}posts-priority`, formData)
            .then((res) => {
                return res.data;
            })
            .catch((error) => {
                thunkAPI.dispatch(
                    showNotification({
                        message:
                            error?.response?.data?.message ?? error.message,
                        isShow: true,
                        type: 'error',
                    })
                );
                return thunkAPI.rejectWithValue(
                    error?.response?.data?.message ?? error.message
                );
            });
    }
);
