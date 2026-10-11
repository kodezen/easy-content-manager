import ReactEditor from '.';
import ReactTaxonomy from './ReactTaxonomy';
import './../../assets/scss/ecm-react-editor.scss';

// add assingnment modal
export const ReactEditorHook = () => {
	wp.hooks.addFilter(
		'ecm.react-editor.register-custom-field',
		'ecm.academy-editor-custom-field',
		(nullValue, values, setFieldValue, post_type) => {
			const props = { values, setFieldValue, post_type };
			return <ReactEditor {...props} />;
		},
		4
	);
};

// add assingnment modal
export const ReactTaxonomyHook = () => {
	wp.hooks.addFilter(
		'ecm.react-editor.register-taxonomy',
		'ecm.academy-editor-custom-taxonomy',
		(nullValue, values, setFieldValue, post_type) => {
			const props = { values, setFieldValue, post_type };
			return <ReactTaxonomy {...props} />;
		},
		4
	);
};

ReactTaxonomyHook();
ReactEditorHook();
