import { createAsyncThunk, createSlice } from '@reduxjs/toolkit';
import { API, namespace } from '@ECMUtils/helper';
import { showNotification } from '../notificationSlice/notificationSlice';
import { __ } from '@wordpress/i18n';

export const getAllReviews = createAsyncThunk(
    'contentManager/getAllReviews',
    async ({ page = 1, per_page = 10, status = 'all', search = '' }, thunkAPI) => {
        const params = {
            page,
            per_page,
            status,
        };

        if (search) params.search = search;
        return await API.get(`${namespace}reviews`, {
            params
        })
            .then((res) => {
                return {
                    data: res.data,
                    totalItems: Number(res.headers['x-wp-total']),
                    rowsPerPage: per_page,
                    currentPage: page,
                    status
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

export const approveReview = createAsyncThunk(
    'contentManager/approveReview',
    async (id, thunkAPI) => {
        return await API.post(`${namespace}reviews/${id}/approve`)
            .then((res) => {
                return id;
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

export const pendingReview = createAsyncThunk(
    'contentManager/pendingReview',
    async (formData, thunkAPI) => {
        return await API.put(`${namespace}reviews/${formData.id}`, {...formData})
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
                return thunkAPI.rejectWithValue(error.message);
            });
    }
);

export const moveReviewToTrash = createAsyncThunk(
    'contentManager/moveReviewToTrash',
    async (id, thunkAPI) => {
        return await API.delete(`${namespace}reviews/${id}`)
            .then((res) => {
                return id;
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

export const deleteReview = createAsyncThunk(
    'contentManager/deleteReview',
    async (id, thunkAPI) => {
        return await API.delete(`${namespace}reviews/${id}`, {
            headers: {
                'X-HTTP-Method-Override': 'DELETE',  
            },
            params: { force: true }
        })
            .then((res) => {
                return id;
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

const reviewSlice = createSlice({
    name: 'reviews',
    initialState: {
        data: [],
        totalItems: 0,
        currentPage: 1,
        rowsPerPage: 10,
        status: 'all'
    },
    reducers: {
        updateReviewItemPage: (state, action) => {
            state.currentPage = action.payload
        },
        updateReviewItemPerPage: (state, action) => {
            state.rowsPerPage = action.payload
        }
    },
    extraReducers: (builder) => {
        builder
            .addCase(getAllReviews.fulfilled, (state, action) => {
                if (action.payload.status === 'trash') {
                    state.data = action.payload.data.map((item) => ({
                        ...item,
                        status: 'trash'
                    }))
                } else {
                    state.data = action.payload.data;
                }

                state.totalItems = action.payload.totalItems;
                state.rowsPerPage = action.payload.rowsPerPage;
                state.currentPage = action.payload.currentPage;
                state.status = action.payload.status
            })
            .addCase(approveReview.fulfilled, (state, action) => {
                state.data = state.data.map((item) => {
                    if (Number(item.id) === Number(action.payload)) {
                        return { ...item, status: 1 };
                    }
                    return item;
                })
            })
            .addCase(pendingReview.fulfilled, (state, action) => {
                state.data = state.data.map((item) => {
                    if (Number(item.id) === Number(action.payload.id)) {
                        return { ...item, status: 0 };
                    }
                    return item;
                })
            })
            .addCase(moveReviewToTrash.fulfilled, (state, action) => {
                state.data = state.data.map((item) => {
                    if (Number(item.id) === Number(action.payload)) {
                        return { ...item, status: 'trash' };
                    }
                    return item;
                })
            })
            .addCase(deleteReview.fulfilled, (state, action) => {
                state.data = state.data.filter((item) => Number(item.id) !== Number(action.payload));
            })
    },
});

export const { updateReviewItemPage, updateReviewItemPerPage } = reviewSlice.actions

export default reviewSlice.reducer;
