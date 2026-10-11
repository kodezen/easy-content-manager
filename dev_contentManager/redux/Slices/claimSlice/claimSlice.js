import { createAsyncThunk, createSlice } from '@reduxjs/toolkit';
import { API, namespace } from '@ECMUtils/helper';
import { showNotification } from '../notificationSlice/notificationSlice';
import { __ } from '@wordpress/i18n';

export const getAllClaims = createAsyncThunk(
    'contentManager/getAllClaims',
    async ({ page = 1, per_page = 10, status = 'all', search = '' }, thunkAPI) => {
        const params = {
            page,
            per_page,
            status,
        };

        if (search) params.search = search;
        return await API.get(`${namespace}claims`, {
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

export const updateClaimStatus = createAsyncThunk(
    'contentManager/updateClaimStatus',
    async ({id, status}, thunkAPI) => {
        return await API.put(`${namespace}claims/${id}?status=${status}`)
            .then((res) => {
                return {id, status};
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

export const deleteClaim = createAsyncThunk(
    'contentManager/deleteClaim',
    async (id, thunkAPI) => {
        return await API.delete(`${namespace}claims/${id}`, {
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

const claimSlice = createSlice({
    name: 'claims',
    initialState: {
        data: [],
        totalItems: 0,
        currentPage: 1,
        rowsPerPage: 10,
        status: 'all'
    },
    reducers: {
        updateClaimItemPage: (state, action) => {
            state.currentPage = action.payload
        },
        updateClaimItemPerPage: (state, action) => {
            state.rowsPerPage = action.payload
        }
    },
    extraReducers: (builder) => {
        builder
            .addCase(getAllClaims.fulfilled, (state, action) => {
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
            .addCase(updateClaimStatus.fulfilled, (state, action) => {
                state.data = state.data.map((item) => {
                    if (Number(item.id) === Number(action.payload.id)) {
                        return { ...item, status: action.payload.status };
                    }
                    return item;
                })
            })
            .addCase(deleteClaim.fulfilled, (state, action) => {
                state.data = state.data.filter((item) => Number(item.id) !== Number(action.payload));
            })
    },
});

export const { updateClaimItemPage, updateClaimItemPerPage } = claimSlice.actions

export default claimSlice.reducer;