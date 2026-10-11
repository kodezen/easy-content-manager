import { useCallback } from 'react';

export const useReactNavigate = () => {
    return useCallback((to, options = {}) => {
        const { replace = false, state = null } = options;

        if (replace) {
            window.history.replaceState(state, '', to);
        } else {
            window.history.pushState(state, '', to);
        }

        window.dispatchEvent(new PopStateEvent('popstate', { state }));
    }, []);
};
