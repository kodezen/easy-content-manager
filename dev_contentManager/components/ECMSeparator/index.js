import React from 'react';
import PropTypes from 'prop-types';

import './styles.scss';

const propTypes = {
	isTop: PropTypes.bool,
	isBottom: PropTypes.bool,
};

const ECMSeparator = ({ isTop = false, isBottom = false, suffix = '' }) => {
	const classNames = ['ecm-separator'];

	if (isTop && isBottom) {
		classNames.push('ecm-separator--both');
	} else {
		if (isTop) classNames.push('ecm-separator--top');
		if (isBottom) classNames.push('ecm-separator--bottom');
		if (suffix) classNames.push(`ecm-separator--${suffix}`);
	}

	return <div className={classNames.join(' ')}></div>;
};

export default ECMSeparator;
ECMSeparator.propTypes = propTypes;
