import { createAsyncThunk, createSlice } from '@reduxjs/toolkit';
import { makeRequest } from '@ECMUtils/helper';
import { showNotification } from '../notificationSlice/notificationSlice';
import { __ } from '@wordpress/i18n';

export const saveAddonStatus = createAsyncThunk(
    'contentManager/saveAddonStatus',
    (formData, thunkAPI) => {
        return makeRequest('saved_addon_status', { addons: formData })
            .then((res) => {
                return res.data.data;
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

export const getAllAddons = createAsyncThunk(
    'contentManager/getAllAddons',
    (formData, thunkAPI) => {
        return makeRequest('get_all_addons', formData)
            .then((res) => {
                return res.data.data;
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

const addonSlice = createSlice({
    name: 'addons',
    initialState: {
        data: null,
    },
    reducers: {
    },
    extraReducers: (builder) => {
        builder
            .addCase(saveAddonStatus.fulfilled, (state, action) => {
                state.data = action.payload;
            })
            .addCase(getAllAddons.fulfilled, (state, action) => {
                state.data = action.payload;
            })
    },
});

export default addonSlice.reducer;
