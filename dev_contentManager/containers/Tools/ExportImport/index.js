import React, { useState } from 'react';
import { __, sprintf } from '@wordpress/i18n';
import {
  Button,
  Flex,
  Heading,
  FileUpload,
  RadioGroup,
  Text,
  CloseButton,
  Box,
  Tabs,
  Span,
} from '@chakra-ui/react';
import { primaryBtn } from '../../../../assets/scss/chakra/recipe';
import { ajaxurl, ecm_nonce, makeRequest } from '@ECMUtils/helper';
import { useDispatch } from 'react-redux';
import { showNotification } from '@ECMRedux/Slices/notificationSlice/notificationSlice';

function RadioOptions({ value, onChange, options }) {
  return (
    <RadioGroup.Root defaultValue={value} onChange={(e) => onChange(e.target.value)}>
      <Flex direction="column" gap={4}>
        {options.map(({ value, label, description }) => (
          <RadioGroup.Item key={value} value={value}>
            <RadioGroup.ItemHiddenInput />
            <RadioGroup.ItemIndicator />
            <RadioGroup.ItemText display="flex" flexDirection="column" gap="1">
              <Text className="content-manager-title">{label}</Text>
              <Text className="content-manager-sub-title">{description}</Text>
            </RadioGroup.ItemText>
          </RadioGroup.Item>
        ))}
      </Flex>
    </RadioGroup.Root>
  );
}

const OPTIONS = {
  export: [
    {
      value: 'all',
      label: __('All', 'easy-content-manager'),
      description: __('Export all post types and taxonomies in one zip file with .json format.', 'easy-content-manager'),
    },
    {
      value: 'post_types',
      label: __('Post Types', 'easy-content-manager'),
      description: __('Export all post types and their posts in one zip file with .json format.', 'easy-content-manager'),
    },
    {
      value: 'field_groups',
      label: __('Field Groups', 'easy-content-manager'),
      description: __('Export all field groups in one zip file with .json format.', 'easy-content-manager'),
    },
    {
      value: 'taxonomies',
      label: __('Taxonomies', 'easy-content-manager'),
      description: __('Export all taxonomies in one zip file with .json format.', 'easy-content-manager'),
    },
    {
      value: 'settings',
      label: __('Settings', 'easy-content-manager'),
      description: __('Export all settings in one zip file with .json format.', 'easy-content-manager'),
    },
    {
      value: 'settings_builder',
      label: __('Settings Builder', 'easy-content-manager'),
      description: __('Export all custom settings in one zip file with .json format that you built from settings builder.', 'easy-content-manager'),
    },
  ]
};

export default function ExportImport() {
  const [exportType, setExportType] = useState('all');
  const [importFile, setImportFile] = useState(null);
  const [loading, setLoading] = useState('idle');

  const dispatch = useDispatch();

  const handleExport = async () => {
    setLoading('exporting');
    try {
      const res = await makeRequest('export_data', { export_type: exportType });
      const data = res.data.data;
      const stats = data.stats || {};

      window.location.href = data.zip_url;

      dispatch(
        showNotification({
          message: sprintf(
            __('Post Types %s, Posts %s, Taxonomies %s, Settings %s are exported', 'easy-content-manager'),
            stats.post_types,
            stats.post_type_content,
            stats.taxonomies,
            stats.settings
          ),
        })
      );
    } finally {
      setLoading('idle');
    }
  };

  const handleImport = async () => {
    setLoading('importing');

    const formData = new FormData();
    formData.append('action', 'easy-content-manager/import_data');
    formData.append('security', ecm_nonce);
    formData.append('file', importFile);

    try {
      await fetch(ajaxurl, { method: 'POST', body: formData });
      window.location.reload();
    } finally {
      setLoading('idle');
    }
  };

  return (
    <Flex direction="column" width="100%" p={6} bg="var(--ecm-background)">
      <Tabs.Root lazyMount unmountOnExit defaultValue="export">
        <Tabs.List>
          <Tabs.Trigger
            value="export"
            color="var(--ecm-primary)"
            _before={{
              background: "var(--ecm-primary)"
            }}
            display="flex"
            alignItems="center"
            gap={2}
          >
            <Span
              _before={{
                color: "var(--ecm-primary)"
              }}
              className='easy-content-manager-icon easy-content-manager-icon--export'
            />
            <Span>{__('Export', 'easy-content-manager')}</Span>
          </Tabs.Trigger>
          <Tabs.Trigger
            value="import"
            color="var(--ecm-primary)"
            _before={{
              background: "var(--ecm-primary)"
            }}
            display="flex"
            alignItems="center"
            gap={2}
          >
            <Span
              _before={{
                color: "var(--ecm-primary)"
              }}
              className='easy-content-manager-icon easy-content-manager-icon--download'
            />
            <Span>{__('Import', 'easy-content-manager')}</Span>
          </Tabs.Trigger>
        </Tabs.List>
        <Tabs.Content value="export">
          <Flex direction="column" gap={3} alignItems="flex-start">
            <RadioOptions
              value={exportType}
              onChange={setExportType}
              options={OPTIONS.export}
            />

            <Button
              {...primaryBtn}
              mt={6}
              onClick={handleExport}
              loading={loading === 'exporting'}
              disabled={loading === 'exporting'}
            >
              {__('Export', 'easy-content-manager')}
            </Button>
          </Flex>
        </Tabs.Content>
        <Tabs.Content value="import">
          <Flex direction="column" gap={3} alignItems="flex-start">
            <FileUpload.Root
              maxW="xl"
              alignItems="stretch"
              maxFiles={1}
              onFileChange={(e) => setImportFile(e.acceptedFiles[0] || null)}
              onFileReject={() => setImportFile(null)}
            >
              <FileUpload.HiddenInput />
              <FileUpload.Dropzone minH="112px" cursor="pointer">
                {importFile ? (
                  <Box position="relative" width="100%">
                    <FileUpload.List />
                    <FileUpload.ClearTrigger asChild position="absolute" top="9px" right="4px">
                      <CloseButton />
                    </FileUpload.ClearTrigger>
                  </Box>
                ) : (
                  <FileUpload.DropzoneContent>
                    <Text m={0}>{__('Drag and drop files here', 'easy-content-manager')}</Text>
                    <Text color="fg.muted" m={0}>{__('.zip up to 5MB', 'easy-content-manager')}</Text>
                  </FileUpload.DropzoneContent>
                )}
              </FileUpload.Dropzone>
            </FileUpload.Root>

            <Button
              {...primaryBtn}
              onClick={handleImport}
              loading={loading === 'importing'}
              disabled={loading === 'importing' || !importFile}
            >
              {__('Import', 'easy-content-manager')}
            </Button>
          </Flex>
        </Tabs.Content>
      </Tabs.Root>
    </Flex>
  );
}
