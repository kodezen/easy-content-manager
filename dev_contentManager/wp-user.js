import { createRoot } from '@wordpress/element';
import WpUser from '@WpUser/index';

document.addEventListener('DOMContentLoaded', () => {
    const container = document.querySelector('.ecm__user-fields');

    if (container) {
        createRoot(container).render(<WpUser />);
    }
});