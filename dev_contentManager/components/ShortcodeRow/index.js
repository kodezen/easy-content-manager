import { Flex, Span, Text } from "@chakra-ui/react";
import ECMHelp from "@ECMComponents/Help";
import { __, sprintf } from '@wordpress/i18n';

const customStyles = {
    shortcode: {
        m: 0,
        display: "flex",
        flexWrap: "wrap",     
        alignItems: "flex-start", 
        padding: "12px",
        width: "100%", 
        maxWidth: "fit-content", 
        border: "1px solid var(--ecm-border-color)",
        borderRadius: "4px",
        gap: "8px",
        cursor: "pointer",
        fontSize: "0.875rem",
        fontWeight: "400",
    },
    shortcodeHelp: {
        mb: '-2px',
        cursor: "pointer",
    }
};

const ShortcodeRow = ({ title, docUrl, items, onCopy }) => (
    <Flex alignItems="start" width="100%">
        <Flex alignItems="end" gap={1} width="calc(100% - 80%)">
            <Text m={0} className='content-manager-title'>
                {sprintf(
                    __('%s', 'easy-content-manager'),
                    title
                )}
            </Text>
            <ECMHelp href={docUrl} styles={customStyles.shortcodeHelp} />
        </Flex>

        <Flex direction="column" gap={4}  width="calc(100% - 24%)">
            {items.map((item, index) => (
                <Flex key={index} direction="column" gap={1}>
                    <Flex alignItems="center" gap={3}>
                        <Text
                            {...customStyles.shortcode}
                            className='content-manager-title'
                            onClick={() => onCopy(item.code)}
                            cursor="pointer"
                        >
                            <Span fontSize="0.875rem" flex="1" textAlign="left">{item.code}</Span>
                            <Span flexShrink={0} className='easy-content-manager-icon easy-content-manager-icon--copy-two' />
                        </Text>
                    </Flex>
                    <Text className='content-manager-sub-title' color="var(--ecm-text-muted)">
                        {sprintf(
                            __('%s', 'easy-content-manager'),
                            item.description
                        )}
                    </Text>
                </Flex>
            ))}
        </Flex>
    </Flex>
);

export default ShortcodeRow;