import { makeRequest } from "@ECMUtils/helper";
import { createAsyncThunk } from "@reduxjs/toolkit";
import { showNotification } from "../notificationSlice/notificationSlice";
import { __ } from '@wordpress/i18n';

export const getUpvoteUsers = createAsyncThunk(
    'contentManager/getUpvoteUsers',
    async ({ post_id, page = 1, per_page = 10, search = '' }, thunkAPI) => {
        const params = {
            page,
            per_page,
            post_id,
        };

        if (search) params.search = search;
        return await makeRequest('get_upvoted_users', params)
            .then((res) => {
                return {
                    data: res.data.data.data,
                    anonymousUpvotes: res.data.data.anonymous_users,
                    totalItems: res.data.data.pagination.total,
                    rowsPerPage: per_page,
                    currentPage: page,
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
                return thunkAPI.rejectWithValue(error.message);
            });
    }
);
