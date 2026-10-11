import React from 'react';
import { __ } from '@wordpress/i18n';
import ECMTooltip from '@ECMComponents/ECMTooltip';
import { Box, Span } from '@chakra-ui/react';

export default function ECMHelp({
    href = '#',
    styles = {
        bg: "var(--ecm-gray)",
        m: 0,
        borderRadius: "full",
        padding: "8px 10px",
        cursor: "pointer",
    },
    tooltipPosition = 'top',
}) {
    return (
        <ECMTooltip
            content={__('Help', 'easy-content-manager')}
            position={tooltipPosition}
            noWrap={true}
        >
            <Box
                {...styles}
                onClick={() => {
                    window.open(href, '_blank');
                }}
            >
                <Span
                    className='easy-content-manager-icon easy-content-manager-icon--question-round'
                    _before={{
                        color: "var(--ecm-text-muted)",
                        fontSize: "1rem"
                    }}
                />
            </Box>
        </ECMTooltip>
    );
};
