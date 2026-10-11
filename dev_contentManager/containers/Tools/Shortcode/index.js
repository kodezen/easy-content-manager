import React from 'react';
import { __ } from '@wordpress/i18n';
import ECMSeparator from '@ECMComponents/ECMSeparator';
import { Flex } from '@chakra-ui/react';
import { useDispatch } from 'react-redux';
import ShortcodeRow from '@ECMComponents/ShortcodeRow';
import { copyShortcode } from '@ECMUtils/helper';
import { addonShortcodes } from '@ECMUtils/shortcodes';

export default function Shortcode() {
    const dispatch = useDispatch();

    const handleCopyShortcode = (shortcode) => copyShortcode(shortcode, dispatch);

    const shortcodes = Object.values(addonShortcodes).flat();

    return (
        <div className="ecm-content-wrap">
            <div className="content-manager-title-subtitle">
                <h1 className="content-manager-title">
                    {__('Display Shortcodes', 'easy-content-manager')}
                </h1>
                <p className="content-manager-sub-title">
                    {__("Quickly copy and paste shortcodes to display interactive features anywhere on your site.", 'easy-content-manager')}
                </p>
            </div>

            <ECMSeparator isTop={true} isBottom={true} />

            <Flex direction="column" gap={4} width="100%">
                {shortcodes.map((section, idx) => (
                    <ShortcodeRow
                        key={idx}
                        docUrl={section.docUrl}
                        title={section.title}
                        items={section.items}
                        onCopy={handleCopyShortcode}
                    />
                ))}
            </Flex>
        </div>
    );
}