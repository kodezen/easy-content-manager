import React, { useEffect } from 'react';
import { __ } from '@wordpress/i18n';
import TopBar from '@ECMComponents/TopBar';
import ECMLabel from '@ECMComponents/Labels/ECMLabel';
import { capitalizeFirstLetter, route_path, useQuery } from '@ECMUtils/helper';
import { useNavigate } from 'react-router-dom';
import { Box, Flex, Span, Text } from '@chakra-ui/react';
import { isParentActive, tabsArray } from './helper';
import Shortcode from './Shortcode';
import ExportImport from './ExportImport';
import SubTopBar from '@ECMComponents/SubTopBar';

export default function Tools() {
  const [activeIndex, setActiveIndex] = React.useState(0);

  const navigate = useNavigate();
  const location = useQuery();
  const currentTab = location.get('path');

  useEffect(() => {
    if (!currentTab)
      navigate(
        `${route_path}admin.php?page=easy-content-manager-tools&path=export-import`
      );
  }, [currentTab]);

  let parentTab = null;

  if (currentTab?.includes('/')) parentTab = currentTab.split('/')[0];
  else parentTab = currentTab;

  const subPath = currentTab?.includes('/') ? currentTab.split('/')[1] : null;

  return (
    <>
      <TopBar
        leftContent={() => (
          <>
            <span className="ecm-topbar-logo ecm-primary easy-content-manager-icon easy-content-manager-icon--ecm-primary" />

            <span className="easy-content-manager-icon easy-content-manager-icon--angle-right" />

            <ECMLabel
              type="subtitle"
              fontWeight="medium"
              label={__('Tools', 'easy-content-manager')}
              href={`${route_path}admin.php?page=easy-content-manager-tools`}
            />

            {parentTab && (
              <>
                <span className="easy-content-manager-icon easy-content-manager-icon--angle-right" />
                <ECMLabel
                  as="h2"
                  type="subtitle"
                  margin="0"
                  fontWeight="medium"
                  color="var(--ecm-primary)"
                  href={subPath ? `${route_path}admin.php?page=easy-content-manager-tools&path=${parentTab}` : null}
                  label={__(
                    capitalizeFirstLetter(parentTab),
                    'easy-content-manager'
                  )}
                />
              </>
            )}
          </>
        )}
      />

      <div className='content-manager-page-content'>
        <SubTopBar
          heading={__('Tools', 'easy-content-manager')}
        />
        <Flex gap={6} width="100%">
          <Flex
            direction="column"
            gap={3}
            width={'260px'}
            height="fit-content"
            padding={'16px 8px'}
            border={'none'}
            borderRadius="4px"
            background="var(--ecm-background)"
            boxShadow={
              '0 0 1px 0 rgba(20, 26, 36, 0.20), 0 1px 2px 0 rgba(20, 26, 36, 0.10);'
            }
          >
            {tabsArray.map((tabItem, index) => {
              const isDisabled = tabItem.isDisabled;
              const isActive = isParentActive(currentTab, tabItem.value);
              const isHovered = activeIndex === index;
              const isManagePro = tabItem.value === 'manage_pro';

              const background = isManagePro
                ? is_pro
                  ? isActive
                    ? 'var(--ecm-secondary)'
                    : 'transparent'
                  : 'var(--ecm-primary)'
                : isActive
                  ? 'var(--ecm-secondary)'
                  : 'transparent';

              const color = isManagePro
                ? is_pro
                  ? isActive
                    ? 'var(--ecm-secondary)'
                    : 'var(--ecm-font-color)'
                  : '#fff'
                : isActive
                  ? 'var(--ecm-primary)'
                  : 'var(--ecm-font-color)';

              return (
                <>
                  <Flex
                    onClick={() => {
                      if (isDisabled) return;
                      navigate(
                        `${route_path}admin.php?page=easy-content-manager-tools&path=${tabItem.value}`
                      );
                    }}
                    padding={'4px 10px'}
                    gap={2}
                    cursor="pointer"
                    borderRadius="4px"
                    alignItems={'start'}
                    _disabled={tabItem.isDisabled}
                    background={background}
                    _hover={{
                      backgroundColor:
                        isManagePro && !is_pro
                          ? ''
                          : 'var(--ecm-secondary)',
                    }}
                    onMouseEnter={() => setActiveIndex(index)}
                    onMouseLeave={() => setActiveIndex(false)}
                  >
                    <Span
                      paddingTop="6px"
                      _before={{
                        color: isActive ? "var(--ecm-primary)" : "var(--ecm-font-color)",
                      }}
                      className={`${tabItem.icon}`}
                    />
                    <Flex
                      direction={'column'}
                      gap={1}
                      position="relative"
                    >
                      <Text
                        fontSize="14px"
                        fontWeight="500"
                        margin="0"
                        color={color}
                      >
                        {tabItem.label}
                      </Text>
                      <Text
                        fontSize="12px"
                        fontWeight="400"
                        margin="0"
                        color="var(--ecm-font-color)"
                      >
                        {tabItem.subtitle}
                      </Text>
                      {tabItem.tag && !is_pro && (
                        <Box
                          bg="orange.400"
                          color="white"
                          px="6px"
                          py="3px"
                          borderRadius="0.5rem"
                          fontWeight="500"
                          fontSize="0.875rem"
                          position="absolute"
                          right="-92px"
                          top="7%"
                        >
                          {tabItem.tag}
                        </Box>
                      )}
                    </Flex>
                  </Flex>
                </>
              );
            })}
          </Flex>
          <Box width="calc(100% - 272px)">
            {currentTab === 'shortcodes' && <Shortcode />}
            {currentTab === 'export-import' && <ExportImport />}
          </Box>
        </Flex>
      </div>
    </>
  );
};
