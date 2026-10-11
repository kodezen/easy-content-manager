const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const path = require('path');
const CONTENT_MANAGER_VERSION = require('./package.json').version;

const config = {
	...defaultConfig,
	entry: {
		backend: path.resolve(__dirname, 'dev_contentManager/backend.js'),
		frontend: path.resolve(__dirname, 'dev_contentManager/frontend.js'),
		'block-editor': path.resolve(
			__dirname,
			'dev_contentManager/block-editor.js'
		),
		'classic-editor': path.resolve(
			__dirname,
			'dev_contentManager/classic-editor.js'
		),
		app: path.resolve(__dirname, 'dev_contentManager/app.js'),
		'ecm-react-editor': path.resolve(
			__dirname,
			'dev_contentManager/ReactEditor/ReactEditorHook.js'
		),
		'wp-user': path.resolve(
			__dirname,
			'dev_contentManager/wp-user.js'
		),
	},
	output: {
		filename: `[name].${CONTENT_MANAGER_VERSION}.js`,
		path: path.resolve(__dirname, 'assets/build'),
	},
	plugins: [...defaultConfig.plugins, new CleanWebpackPlugin()],
	resolve: {
		alias: {
			...defaultConfig.resolve.alias,
			'@ECMComponents': path.resolve(
				__dirname,
				'dev_contentManager/components/'
			),
			'@ECMContainers': path.resolve(
				__dirname,
				'dev_contentManager/containers/'
			),
			'@Global': path.resolve(__dirname, 'dev_contentManager/global/'),
			'@BlockEditor': path.resolve(
				__dirname,
				'dev_contentManager/block-editor/'
			),
			'@ClassicEditor': path.resolve(
				__dirname,
				'dev_contentManager/classic-editor/'
			),
			'@WpUser': path.resolve(
				__dirname,
				'dev_contentManager/wp-user/'
			),
			'@ECMRedux': path.resolve(__dirname, 'dev_contentManager/redux/'),
			'@ECMUtils': path.resolve(__dirname, 'dev_contentManager/utils/'),
			'@ECMHooks': path.resolve(__dirname, 'dev_contentManager/hooks/'),
		},
	},
};

module.exports = config;
