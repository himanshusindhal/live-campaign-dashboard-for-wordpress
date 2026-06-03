/**
 * Live Campaign Dashboard — Admin JavaScript
 *
 * Handles:
 * - Color picker initialization
 * - Cards manager (add/remove/edit)
 * - Shortcode copy to clipboard
 * - Live preview refresh
 * - Form serialization of cards JSON
 *
 * @package LiveCampaignDashboard
 */

(function () {
    'use strict';

    /* ============================================================
       Wait for DOM
       ============================================================ */
    document.addEventListener('DOMContentLoaded', function () {

        /* ============================================================
           Color Pickers
           ============================================================ */
        if (typeof jQuery !== 'undefined' && jQuery.fn.wpColorPicker) {
            jQuery('.lcd-color-picker').wpColorPicker({
                change: function () {
                    // Debounced preview update
                    clearTimeout(window._lcdColorTimeout);
                    window._lcdColorTimeout = setTimeout(updatePreview, 300);
                },
            });
        }

        /* ============================================================
           Shortcode Copy
           ============================================================ */
        const copyBtn = document.getElementById('lcd-copy-shortcode');
        if (copyBtn) {
            copyBtn.addEventListener('click', function () {
                const shortcodeText = document.getElementById('lcd-shortcode-text');
                if (!shortcodeText) return;

                const text = shortcodeText.textContent || shortcodeText.innerText;

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(function () {
                        showCopied(copyBtn);
                    });
                } else {
                    // Fallback
                    const textarea = document.createElement('textarea');
                    textarea.value = text;
                    textarea.style.position = 'fixed';
                    textarea.style.opacity = '0';
                    document.body.appendChild(textarea);
                    textarea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textarea);
                    showCopied(copyBtn);
                }
            });
        }

        function showCopied(btn) {
            const span = btn.querySelector('span');
            const originalText = span ? span.textContent : '';
            if (span) span.textContent = 'Copied!';
            btn.classList.add('lcd-copied');

            setTimeout(function () {
                if (span) span.textContent = originalText;
                btn.classList.remove('lcd-copied');
            }, 2000);
        }

        /* ============================================================
           Cards Manager
           ============================================================ */
        const cardsManager = document.getElementById('lcd-cards-manager');
        const cardsField = document.getElementById('lcd-cards-config-field');
        const addCardBtn = document.getElementById('lcd-add-card');

        let cardsData = [];

        // Load existing cards
        if (cardsField && cardsField.value) {
            try {
                cardsData = JSON.parse(cardsField.value);
            } catch (e) {
                cardsData = [];
            }
        }

        function renderCards() {
            if (!cardsManager) return;
            cardsManager.innerHTML = '';

            cardsData.forEach(function (card, index) {
                const item = document.createElement('div');
                item.className = 'lcd-card-manager-item';
                item.innerHTML =
                    '<div>' +
                    '<label>Label</label>' +
                    '<input type="text" data-field="label" data-index="' + index + '" value="' + escapeAttr(card.label || '') + '" placeholder="Revenue Generated">' +
                    '</div>' +
                    '<div>' +
                    '<label>Value</label>' +
                    '<input type="text" data-field="value" data-index="' + index + '" value="' + escapeAttr(card.value || '') + '" placeholder="2.4Cr">' +
                    '</div>' +
                    '<div>' +
                    '<label>Growth</label>' +
                    '<input type="text" data-field="growth" data-index="' + index + '" value="' + escapeAttr(card.growth || '') + '" placeholder="↑ 28% MoM">' +
                    '</div>' +
                    '<button type="button" class="lcd-remove-card-btn" data-index="' + index + '" title="Remove card">&times;</button>';
                cardsManager.appendChild(item);
            });

            // Bind input changes
            cardsManager.querySelectorAll('input').forEach(function (input) {
                input.addEventListener('input', function () {
                    const idx = parseInt(this.getAttribute('data-index'), 10);
                    const field = this.getAttribute('data-field');
                    if (cardsData[idx]) {
                        cardsData[idx][field] = this.value;
                        syncCardsField();
                    }
                });
            });

            // Bind remove buttons
            cardsManager.querySelectorAll('.lcd-remove-card-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const idx = parseInt(this.getAttribute('data-index'), 10);
                    cardsData.splice(idx, 1);
                    renderCards();
                    syncCardsField();
                });
            });
        }

        function syncCardsField() {
            if (cardsField) {
                cardsField.value = JSON.stringify(cardsData);
            }
        }

        function escapeAttr(str) {
            return str.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }

        // Add card
        if (addCardBtn) {
            addCardBtn.addEventListener('click', function () {
                cardsData.push({
                    id: 'card_' + Date.now(),
                    label: '',
                    value: '',
                    prefix: '',
                    suffix: '',
                    growth: '',
                    icon: 'revenue',
                });
                renderCards();
                syncCardsField();
            });
        }

        // Initial render
        renderCards();

        /* ============================================================
           Live Preview Update (debounced)
           ============================================================ */
        function updatePreview() {
            const previewContainer = document.getElementById('lcd-admin-preview');
            if (!previewContainer) return;

            // Re-init the dashboard instance in preview
            const wrapper = previewContainer.querySelector('.lcd-wrapper');
            if (wrapper && typeof window.LCDInitInstance === 'function') {
                // Update colors from the color picker values
                const primaryInput = document.getElementById('lcd-primary-color');
                const accentInput = document.getElementById('lcd-accent-color');

                if (primaryInput) {
                    const val = primaryInput.value || '#2563eb';
                    wrapper.style.setProperty('--lcd-primary', val);
                }
                if (accentInput) {
                    const val = accentInput.value || '#10b981';
                    wrapper.style.setProperty('--lcd-accent', val);
                }

                // Theme
                const themeSelect = document.getElementById('lcd-theme');
                if (themeSelect) {
                    wrapper.classList.remove('lcd-theme-light', 'lcd-theme-dark');
                    wrapper.classList.add('lcd-theme-' + themeSelect.value);
                }
            }
        }

        // Listen for form changes to update preview
        const form = document.getElementById('lcd-settings-form');
        if (form) {
            form.addEventListener('change', function () {
                clearTimeout(window._lcdPreviewTimeout);
                window._lcdPreviewTimeout = setTimeout(updatePreview, 300);
            });
        }
    });
})();
