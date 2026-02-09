import { _x, sprintf } from '@wordpress/i18n';

/**
 * Create the component for the dialog box.
 *
 * @param {string} content content of the dialog box.
 * @param {string} title title of the dialog box.
 * @param {string} buttonActionText text for the action button.
 * @returns {string} HTML component of the dialog box.
 */
export const createDialogComponent = (content, title, buttonActionText) => {
    const dialogComponent = `<dialog id="iloveimg-compress-restore-dialog" class="iloveimg-restore-dialog"><h2 class="iloveimg-title-dialog">${title}</h2>
                <p class="iloveimg-content-dialog">${content}</p>
                <div class="iloveimg-btn-groups">
                    <button id="iloveimg-compress-dialog-aceptted" class="ipdf-btn ipdf-btn--primary">${buttonActionText}</button>
                    <button id="iloveimg-compress-dialog-close" class="ipdf-btn ipdf-btn--secondary">${_x(
        'Cancel',
        'button dialog box',
        'iloveimg'
    )}</button>
                </div>
            </dialog>`;

    return dialogComponent;
};

/**
 * Show admin notice on dashboard.
 *
 * @param {string} message the message to show.
 * @param {string} type the type of message. Default: success
 * @returns {string} HTML component of the admin notice.
 */
export const showAdminNotice = (message, type = 'success') => {
    const notice = sprintf(
        '<div class="ipdf-notice ilovepdf-base__layout-flex notice notice-%s is-dismissible"><figure class="ipdf-logo ilovepdf-base__layout-flex ilovepdf-base__layout-items--center"><img src="%s" alt="logo ilovepdf" /></figure><p>%s</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>',
        type,
        IlovePdfData.logoUrl,
        message
    );

    const container = document.querySelector(
        '#wpwrap #wpcontent #wpbody #wpbody-content > h1, #wpwrap #wpcontent #wpbody #wpbody-content > h2, #wpwrap #wpcontent #wpbody #wpbody-content'
    );
    container?.insertAdjacentHTML('beforebegin', notice);

    setTimeout(() => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }, 500);

    if (container) {
        const btnsCloseNotice = document.querySelectorAll('.is-dismissible .notice-dismiss');

        if (btnsCloseNotice) {
            btnsCloseNotice.forEach((btn) => {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.currentTarget.parentNode.remove();
                });
            });
        }
    }
};
