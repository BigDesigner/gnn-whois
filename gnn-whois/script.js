(function () {
    'use strict';

    /**
     * Copy text to clipboard using Clipboard API with a legacy fallback.
     *
     * @param {string} text
     * @returns {Promise<void>}
     */
    function copyText(text) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text);
        }

        return new Promise(function (resolve, reject) {
            var textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.setAttribute('readonly', '');
            textarea.style.position = 'fixed';
            textarea.style.left = '-9999px';
            document.body.appendChild(textarea);
            textarea.select();

            try {
                var ok = document.execCommand('copy');
                document.body.removeChild(textarea);
                if (ok) {
                    resolve();
                } else {
                    reject(new Error('Copy command failed'));
                }
            } catch (err) {
                document.body.removeChild(textarea);
                reject(err);
            }
        });
    }

    document.addEventListener('click', function (event) {
        var btn = event.target.closest('.gnn-whois-copy-btn');
        if (!btn) {
            return;
        }

        var targetId = btn.getAttribute('data-target');
        if (!targetId) {
            return;
        }

        var resultEl = document.getElementById(targetId);
        if (!resultEl) {
            return;
        }

        var container = btn.closest('.gnn-whois-result-container');
        var statusEl = container ? container.querySelector('.gnn-whois-copy-status') : null;
        var strings = window.gnnWhoisL10n || {};
        var copiedLabel = strings.copied || 'Copied!';
        var failedLabel = strings.copyFailed || 'Copy failed';
        var originalLabel = btn.getAttribute('data-original-label') || btn.textContent;

        btn.setAttribute('data-original-label', originalLabel);

        copyText(resultEl.textContent || '')
            .then(function () {
                btn.textContent = copiedLabel;
                btn.classList.add('copied');
                if (statusEl) {
                    statusEl.textContent = copiedLabel;
                }
                window.setTimeout(function () {
                    btn.textContent = originalLabel;
                    btn.classList.remove('copied');
                    if (statusEl) {
                        statusEl.textContent = '';
                    }
                }, 2000);
            })
            .catch(function () {
                if (statusEl) {
                    statusEl.textContent = failedLabel;
                }
            });
    });
})();
