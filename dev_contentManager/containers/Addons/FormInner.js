import React, { useState } from 'react';
import { __, sprintf } from '@wordpress/i18n';
import {
  Box,
  Text,
  Span,
  Switch,
  VStack,
  Flex,
  Heading,
  Grid,
  Link
} from '@chakra-ui/react';
import { is_pro } from '@ECMUtils/helper';
import { getAddonsData } from './helper';
import { useFormikContext } from 'formik';
import { useNavigate } from 'react-router-dom';
import UpgradeTooltip from '@ECMComponents/ECMTooltip/UpgradeTooltip';
import ECMTooltip from '@ECMComponents/ECMTooltip';

export default function FormInner() {
  const { values, setFieldValue, handleSubmit } = useFormikContext();

  const navigate = useNavigate();

  return (
    <Grid
      templateColumns="repeat(3, 1fr)"
      gap={5}
      width="100%"
      padding="24px"
      bg="var(--ecm-background)"
      className="ecm-addons"
      borderRadius="4px"
    >
      {getAddonsData.map((item, index) => (
        <Box
          key={item.id}
          borderWidth="1px"
          borderColor="var(--ecm-border-color)"
          borderRadius="md"
          p={4}
          bg="var(--ecm-background)"
          boxShadow="0.875rem"
          width="100%"
          className="ecm-addons--card"
        >
          <VStack align="start" spacing={3}>
            <Flex
              justifyContent="space-between"
              width="100%"
              alignItems="center"
            >
              <Span className={`${item.icon} ecm-addons--icon`} />
              <Flex alignItems="center" gap={3} position="relative">
                {item.settingsUrl && is_pro && values[index]?.status && (
                  <Span
                    className={`easy-content-manager-icon easy-content-manager-icon--build`}
                    onClick={() => navigate(item.settingsUrl)}
                    cursor="pointer"
                    _before={{
                      fontSize: "20px"
                    }}
                  />
                )}
                {is_pro ? (

                  <Switch.Root
                    disabled={item.disabled}
                    checked={values[index].status}
                    onCheckedChange={(e) => {
                      setFieldValue(`${index}.status`, e.checked);
                      handleSubmit();
                    }}
                    className="ecm-addons--switch"
                    onMouseEnter={() => !is_pro && setActiveTooltipIndex(index)}
                    onMouseLeave={() => setActiveTooltipIndex(null)}
                  >
                    <Switch.HiddenInput />
                    <Switch.Control className="ecm-addons--switch-control">
                      <Switch.Thumb />
                    </Switch.Control>
                    <Switch.Label />
                  </Switch.Root>

                ) : !item.marketing_tag && (
                  <ECMTooltip
                    noWrap={true}
                    content={__(
                      'Available in pro',
                      'easy-content-manager'
                    )}
                  >
                    <Span
                      className={`easy-content-manager-icon easy-content-manager-icon--lock`}
                      _before={{
                        color: 'var(--ecm-text-muted)'
                      }}
                    />
                  </ECMTooltip>
                )}
              </Flex>
            </Flex>
            <Flex gap={2} alignItems="center">
              <Heading
                as="h2"
                fontSize="0.875rem"
                lineHeight="1.25rem"
                fontWeight="600"
                color="var(--ecm-font-color)"
                m="0"
              >
                {item.title}
              </Heading>
              {!item.disabled && (
                <Span
                  _before={{
                    color: "var(--ecm-primary)",
                    fontSize: "0.85rem"
                  }}
                  cursor="pointer"
                  className="easy-content-manager-icon easy-content-manager-icon--go-on"
                  onClick={() => window.open('_blank', item.link)}
                />
              )}
            </Flex>
            <Text
              fontSize="0.875rem"
              lineHeight="1rem"
              fontWeight="400"
              color="var(--ecm-text-muted)"
              m="0 0 16px 0"
            >
              {item.description}
            </Text>

            {item.marketing_tag && (
              <Text
                fontSize="0.875rem"
                margin={0}
                fontWeight="600"
                borderBottom="1px solid var(--ecm-border-color)"
                pb="8px"
                color="#FFF"
                bg="var(--ecm-primary)"
                padding="3px 7px"
                borderRadius="4px"
              >
                {sprintf(
                  __('%s', 'easy-content-manager'),
                  item.marketing_tag
                )}
              </Text>
            )}
          </VStack>
        </Box>
      ))}
    </Grid>
  );
};
