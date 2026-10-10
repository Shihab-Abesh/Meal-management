import '../css/app.css';

import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import AppLayout from '@/layouts/app-layout';
import AuthLayout from '@/layouts/auth-layout';

const pages = import.meta.glob('./pages/**/*.jsx');

createInertiaApp({
    title: (title, page) => {
        const appName = page?.props?.app?.name ?? import.meta.env.VITE_APP_NAME;

        return title ? `${title} · ${appName}` : appName;
    },
    resolve: (name) => {
        const page = pages[`./pages/${name}.jsx`];

        if (!page) {
            throw new Error(`Page not found: ${name}`);
        }

        return page();
    },
    // Guest pages (auth/*) use the split auth screen and the error page stands
    // alone (the visitor may not be signed in); everything else gets the app shell.
    layout: (name) => {
        if (name === 'error') {
            return null;
        }

        return name.startsWith('auth/') ? AuthLayout : AppLayout;
    },
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);
    },
    progress: {
        color: '#059669',
        delay: 150,
    },
});
