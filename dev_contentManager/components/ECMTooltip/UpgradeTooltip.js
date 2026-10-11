import { Box, Button, Flex, Text } from '@chakra-ui/react';
import { primaryBtn } from '../../../assets/scss/chakra/recipe';

const UpgradeTooltip = ({
  title,
  featureTag = null,
  description,
  buttonText,
  onUpgrade,
  direction = 'top'
}) => {
  const bgColor = 'var(--ecm-background)';
  const offset = '15px';

  const placementMap = {
    top: {
      container: { bottom: `calc(100% + ${offset})`, left: '50%', transform: 'translateX(-50%)' },
      arrow: { bottom: `-8px`, left: '46%', rotate: '45deg', zIndex: -1 }
    },
    bottom: {
      container: { top: `calc(100% + ${offset})`, left: '50%', transform: 'translateX(-50%)' },
      arrow: { top: `-8px`, left: '46%', rotate: '45deg', zIndex: -1}
    },
    left: {
      container: { top: '50%', right: `calc(100% + ${offset})`, transform: 'translateY(-50%)' },
      arrow: { top: '42%', right: `-10px`, rotate: '45deg', zIndex: -1}
    },
    right: {
      container: { top: '50%', left: `calc(100% + ${offset})`, transform: 'translateY(-50%)' },
      arrow: { top: '42%', left: `-10px`, rotate: '45deg', zIndex: -1 }
    }
  };

  const current = placementMap[direction] || placementMap.top;

  return (
    <Box
      position="absolute"
      zIndex={9999}
      pointerEvents="auto"
      width="max-content"
      maxWidth="280px"
      {...current.container}
    >
      <Flex
        bg={bgColor}
        p={4}
        borderRadius={3}
        boxShadow="var(--ecm-shadow)"
        direction="column"
        gap={3}
        position="relative"
        textAlign="center"
      >
        <Flex justifyContent="center" gap={3} alignItems="center">
          <Text m={0} fontSize="1rem" fontWeight={600} color="var(--ecm-font-color)">
            {title}
          </Text>
          {featureTag}
        </Flex>

        <Text m={0} fontSize="0.875rem" color="var(--ecm-text-muted)" lineHeight="1.4">
          {description}
        </Text>

        <Button {...primaryBtn} onClick={onUpgrade} type="button">
          {buttonText}
        </Button>

        <Box
          position="absolute"
          width="0"
          height="0"
          bg={bgColor}
          p={3}
          border="1px solid var(--ecm-border-color)"
          {...current.arrow}
        />
      </Flex>
    </Box>
  );
};

export default UpgradeTooltip;