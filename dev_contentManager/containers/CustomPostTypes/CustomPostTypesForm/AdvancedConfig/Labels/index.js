import React from 'react';
import { __ } from '@wordpress/i18n';
import { getLabelsFieldData } from './helper';
import { Field, useFormikContext } from 'formik';
import { Flex, Button, Box } from '@chakra-ui/react';
import ECMTooltip from '@ECMComponents/ECMTooltip';

import './styles.scss';

const Labels = () => {
	const { setFieldValue } = useFormikContext();

	return (
		<Box padding="12px 12px" maxHeight="372px" overflowY="scroll">
			<Flex alignItems="center" gap="2" marginBottom="24px">
				<Button
					color="var(--ecm-font-color)"
					borderColor="var(--ecm-border-color)"
					borderWidth="1px"
					bg="transparent"
					_hover={{
						bg: 'var(--ecm-body-background)',
					}}
				>
					<span className="easy-content-manager-icon easy-content-manager-icon--delete" />
					{__('Clear', 'easy-content-manager')}
				</Button>

				<ECMTooltip
					type="info"
					position="right"
					width="150px"
					noWrap={false}
					content={__(
						'Regenerate all labels using the Singular and Plural labels',
						'easy-content-manager'
					)}
				/>
			</Flex>

			<Flex flexDirection="column" gap="16px">
				{getLabelsFieldData.map((item) => (
					<Flex
						flexDirection="column"
						gap={2}
						className="ecm--advance-config--label"
					>
						<p className="content-manager-title">{item.label}</p>

						<div className="ecm--advance-config--label-input">
							<Field
								name={item.name}
								type="text"
								placeholder={item.placeholder}
								className="content-manager-input content-manager-p-02"
								onBlur={(e) =>
									setFieldValue(item.name, e.target.value)
								}
							/>
							<div className="ecm--advance-config--label-input-tooltip">
								<ECMTooltip
									content={item.subtitle}
									type="info"
									width="150px"
									noWrap={false}
									position="top-right"
								/>
							</div>
						</div>
					</Flex>
				))}
			</Flex>
		</Box>
	);
};

export default Labels;
