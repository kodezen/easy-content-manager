import { __ } from '@wordpress/i18n';

export function getClaimStatus (status){
        let claimStatus = 'Pending';
        let bgColor = '';
        let textColor = '';
    
        if (status === 'pending') {
            claimStatus = 'Pending';
            bgColor = 'rgba(236, 152, 20, 0.12)';
            textColor = '#FDB022';
        } else if (status === 'approved') {
            claimStatus = 'Approved';
            bgColor = 'rgba(36, 161, 72, 0.1)';
            textColor = '#00AD6B';
        } else if (status === 'rejected') {
            claimStatus = 'Rejected';
            bgColor = 'rgba(244, 72, 63, 0.12)';
            textColor = '#FF4D4D';
        }
    return {claimStatus, bgColor, textColor};
}

export const statusArray = [
    {
        label: __('All', 'easy-content-manager'),
        value: 'all',
    },
    {
        label: __('Pending', 'easy-content-manager'),
        value: 'pending',
    },
    {
        label: __('Approved', 'easy-content-manager'),
        value: 'approved',
    },
    {
        label: __('Rejected', 'easy-content-manager'),
        value: 'rejected',
    },
];

