import React from 'react';
import { __ } from '@wordpress/i18n';
import NewUser from './NewUser';
import { ChakraProvider } from '@chakra-ui/react';
import { theme } from '../../assets/scss/chakra/theme';

export default function WpUser() {
  return (
    <ChakraProvider value={theme}>
      <NewUser/>
    </ChakraProvider>
  );
};
