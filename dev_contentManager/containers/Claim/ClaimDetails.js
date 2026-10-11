import React from 'react';
import { __ } from '@wordpress/i18n';
import ReactModal from '@ECMComponents/Modal/ReactModal';
import { Text, Flex, RatingGroup, Badge, Image } from '@chakra-ui/react';
import { getClaimStatus } from './helper';
import { capitalizeFirstLetter } from '@ECMUtils/helper';

export default function ClaimDetails({ isOpen, onClose, claim }) {
    const { name, email, phone, proof_content, status, created_at, avatar } = claim;
    const { claimStatus, bgColor, textColor } = getClaimStatus(status)
    return (
        <ReactModal
            isOpen={isOpen}
            onRequestClose={onClose}
            isEnabledResizer={false}
            size="medium"
            title={__('Claim Details', 'easy-content-manager')}
            suffix="claim-details-modal"
        >
            <Flex
                direction="column"
                borderWidth="1px"
                borderRadius="12px"
                bg="#FFF"
            >
                <Flex direction="column" p={4} align="start" bg="gray.50" borderBottomWidth="1px">
                    <Image
                        src={avatar}
                        alt="User Image"
                        boxSize="78px"
                        borderRadius="full"
                        mr={4}
                        shadow="md"
                    />
                    <Text fontSize="1rem" fontWeight="600" color="gray.800" m={0} >
                        {capitalizeFirstLetter(name)}
                    </Text>
                    {Boolean(phone) && (
                        <Text fontSize="0.875rem" color="gray.500" m={0} >{phone}</Text>
                    )}
                    <Text fontSize="0.875rem" color="gray.500" m={0} >{email}</Text>
                </Flex>

                <Flex direction="column" gap={6} alignItems="start" >
                    <Text
                        color={textColor}
                        padding="8px 16px"
                        borderRadius="full"
                        bg={bgColor}
                        fontWeight={600}
                        ml={4}
                        mt={4}
                    >
                        {claimStatus}
                    </Text>
                    <Flex direction="column" gap={2} px={4} pb={4}>
                        <Text fontSize="sm" color="gray.600" m={0}>{created_at}</Text>
                        <Text fontSize="md" color="gray.800" m={0}>
                            {proof_content}
                        </Text>
                    </Flex>
                </Flex>
            </Flex>
        </ReactModal>
    );
};
