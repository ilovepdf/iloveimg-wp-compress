import { _x } from '@wordpress/i18n';

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
                    <button id="iloveimg-compress-dialog-aceptted" class="button-primary">${buttonActionText}</button>
                    <button id="iloveimg-compress-dialog-close" class="button-secondary">${_x(
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
 * Builds the notice using DOM APIs so the message is never injected as raw HTML
 * (FR-C8 XSS hardening). The type parameter is validated against an allowlist
 * before it reaches the class string.
 *
 * @param {string} message the message to show.
 * @param {string} type    the type of message (success|error|warning|info). Default: success.
 */
export const showAdminNotice = (message, type = 'success') => {
    // FR-C8: validate type against allowlist before it reaches the class string.
    var allowedTypes = ['success', 'error', 'warning', 'info'];
    var safeType = allowedTypes.indexOf(type) !== -1 ? type : 'success';

    // Build notice via DOM APIs — message is set as textContent, never innerHTML.
    var notice = document.createElement('div');
    notice.className = 'ipdf-notice ilovepdf-base__layout-flex notice notice-' + safeType + ' is-dismissible';

    var figure = document.createElement('figure');
    figure.className = 'ipdf-logo ilovepdf-base__layout-flex ilovepdf-base__layout-items--center';
    var img = document.createElement('img');
    img.src = (window.iloveimgCompress && iloveimgCompress.logoUrl) || '';
    img.alt = 'logo iloveimg'; // FR-C6: corrected alt text.
    figure.appendChild(img);

    var p = document.createElement('p');
    p.textContent = message; // FR-C8: textContent ensures markup in message renders as text.

    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'notice-dismiss';
    var span = document.createElement('span');
    span.className = 'screen-reader-text';
    span.textContent = 'Dismiss this notice.';
    btn.appendChild(span);

    notice.appendChild(figure);
    notice.appendChild(p);
    notice.appendChild(btn);

    const container = document.querySelector(
        '#wpwrap #wpcontent #wpbody #wpbody-content > h1, #wpwrap #wpcontent #wpbody #wpbody-content > h2, #wpwrap #wpcontent #wpbody #wpbody-content'
    );

    if (container) {
        container.insertAdjacentElement('beforebegin', notice);

        setTimeout(() => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }, 500);

        const btnsCloseNotice = document.querySelectorAll('.is-dismissible .notice-dismiss');

        if (btnsCloseNotice) {
            btnsCloseNotice.forEach((dismissBtn) => {
                dismissBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.currentTarget.parentNode.remove();
                });
            });
        }
    }
};
