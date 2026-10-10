/**
 * Show a toast from the browser side (server-side toasts arrive as flash
 * data). The Toaster in the app layout listens for this event.
 *
 * showToast({ type: 'info', title: 'New order', message: '22 meals…', href: '/notifications/…/open' })
 */
export function showToast(toast) {
    window.dispatchEvent(new CustomEvent('app:toast', { detail: toast }));
}
