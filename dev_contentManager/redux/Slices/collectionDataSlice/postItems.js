import { API, is_admin, makeRequest } from "@ECMUtils/helper";
import { createAsyncThunk } from "@reduxjs/toolkit";
import { showNotification } from "../notificationSlice/notificationSlice";
import { __ } from '@wordpress/i18n';

export const fetchPostItems = createAsyncThunk(
    'ecm/fetchPostItems',
    ({ action, params, keyName }, thunkAPI) => {
        const defaultParams = {
            per_page: 10,
            page: 1,
            status: 'any',
            search: '',
            search_columns: ['post_title'],
            context: Boolean(is_admin) ? 'edit' : 'view',
            order: 'desc',
            orderby: 'date'
        };
        const finalParams = { ...defaultParams, ...params };

        return API.get(action, {
            params: finalParams,
        })
            .then((res) => {
                return {
                    data: res.data,
                    keyName,
                    totalItems: parseInt(res.headers['x-wp-total']),
                    itemPerPage: finalParams?.per_page,
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

export const fetchSinglePostItems = createAsyncThunk(
    'ecm/fetchSinglePostItems',
    ({ action, params, keyName }, thunkAPI) => {
        return API.get(action, {
            params,
        })
            .then((res) => {
                return {
                    data: res.data,
                    keyName,
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

export const getUserPosts = createAsyncThunk(
    'ecm/getUserPosts',
    ({ action, params, keyName }, thunkAPI) => {
        return makeRequest(action, params)
            .then((res) => {
                return {
                    data: res.data.data.posts,
                    keyName,
                    totalItems: res.data.data.pagination?.total,
                    itemPerPage: res.data.data.pagination?.per_page,
                    currentPage: res.data.data.pagination.current_page
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

export const saveUserPosts = createAsyncThunk(
    'ecm/saveUserPosts',
    ({ action, params, keyName }, thunkAPI) => {
        return makeRequest(action, params)
            .then((res) => {
                return {
                    data: res.data.data,
                    keyName,
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

export const createPostItem = createAsyncThunk(
    'ecm/createPostItem',
    ({ action, values, keyName }, thunkAPI) => {
        return API.post(action, {
            ...values,
        })
            .then((res) => {
                if (res.status === 201) {
                    thunkAPI.dispatch(
                        showNotification({
                            message: __(
                                'Successfully created item!',
                                'easy-content-manager'
                            ),
                            isShow: true,
                            type: 'success',
                        })
                    );
                    return {
                        data: res.data,
                        keyName,
                    };
                }
                thunkAPI.dispatch(
                    showNotification({
                        message: __(
                            'Failed to create item!',
                            'easy-content-manager'
                        ),
                        isShow: true,
                        type: 'error',
                    })
                );
                return thunkAPI.rejectWithValue(res.data);
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

export const duplicatePostItem = createAsyncThunk(
    'ecm/duplicatePostItem',
    ({ id, meta, keyName, duplicatePostItemData }, thunkAPI) => {
        return makeRequest('duplicate_post_item', {
            post_item_id: id,
            meta,
        })
            .then((res) => {
                if (res.status === 200) {
                    thunkAPI.dispatch(
                        showNotification({
                            message: __(
                                'Successfully duplicated item!',
                                'easy-content-manager'
                            ),
                            isShow: true,
                            type: 'success',
                        })
                    );
                    return {
                        data: res.data.data,
                        keyName,
                        duplicatePostItemData,
                    };
                }
                thunkAPI.dispatch(
                    showNotification({
                        message: __(
                            'Failed to duplicate item!',
                            'easy-content-manager'
                        ),
                        isShow: true,
                        type: 'error',
                    })
                );
                return thunkAPI.rejectWithValue(res.data);
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

export const updatePostItem = createAsyncThunk(
    'ecm/updatePostItem',
    ({ action, values, keyName }, thunkAPI) => {
        return API.post(action, values)
            .then((res) => {
                if (res.status === 200 || res.status === 201) {
                    thunkAPI.dispatch(
                        showNotification({
                            message: __(
                                'Item updated successfully!',
                                'easy-content-manager'
                            ),
                            isShow: true,
                            type: 'success',
                        })
                    );
                    return {
                        data: res.data,
                        keyName,
                    };
                }

                thunkAPI.dispatch(
                    showNotification({
                        message: __(
                            'Failed to update item!',
                            'easy-content-manager'
                        ),
                        isShow: true,
                        type: 'error',
                    })
                );
                return thunkAPI.rejectWithValue(res.data);
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

export const deletePostItem = createAsyncThunk(
    'ecm/deletePostItem',
    ({ action, keyName }, thunkAPI) => {
        return API.post(
            action,
            { force: true },
            {
                headers: {
                    'X-HTTP-Method-Override': 'DELETE',
                },
            }
        )
            .then((res) => {
                if (res.status === 200) {
                    thunkAPI.dispatch(
                        showNotification({
                            message: __(
                                'Item deleted successfully!',
                                'easy-content-manager'
                            ),
                            isShow: true,
                            type: 'success',
                        })
                    );
                    return {
                        data: res.data.previous,
                        keyName,
                    };
                }

                thunkAPI.dispatch(
                    showNotification({
                        message: __(
                            'Failed to delete item!',
                            'easy-content-manager'
                        ),
                        isShow: true,
                        type: 'error',
                    })
                );
                return thunkAPI.rejectWithValue(res.data);
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

export const trashPostItem = createAsyncThunk(
    'ecm/trashPostItem',
    ({ action, keyName }, thunkAPI) => {
        return API.post(
            action,
            { force: false },
            {
                headers: {
                    'X-HTTP-Method-Override': 'DELETE',
                },
            }
        )
            .then((res) => {
                if (res.status === 200) {
                    thunkAPI.dispatch(
                        showNotification({
                            message: __(
                                'Item status updated successfully!',
                                'easy-content-manager'
                            ),
                            isShow: true,
                            type: 'success',
                        })
                    );
                    return {
                        data: res.data,
                        keyName,
                    };
                }

                thunkAPI.dispatch(
                    showNotification({
                        message: __(
                            'Failed to update item status!',
                            'easy-content-manager'
                        ),
                        isShow: true,
                        type: 'error',
                    })
                );
                return thunkAPI.rejectWithValue(res.data);
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

export const getPostItemCount = createAsyncThunk(
    'ecm/getPostItemCount',
    (slug, thunkAPI) => {
        return makeRequest('get_post_item_count', {
            post_slug: slug,
        })
            .then((res) => {
                if (res.data.success) {
                    return {
                        data: res.data.data,
                        keyName: slug,
                    };
                }
                return thunkAPI.rejectWithValue(res.data);
            })
            .catch((error) => thunkAPI.rejectWithValue(error.message));
    }
);
