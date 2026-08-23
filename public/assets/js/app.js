(function() {
    'use strict';

    const App = {
        init() {
            this.initTheme();
            this.initUserMenu();
            this.initForms();
            this.initFlashMessages();
            this.initTooltips();
            this.initCopyButtons();
        },

        initTheme() {
            const toggle = document.getElementById('theme-toggle');
            if (!toggle) return;

            const html = document.documentElement;
            const savedTheme = localStorage.getItem('theme') || 'light';
            html.setAttribute('data-theme', savedTheme);
            this.updateThemeIcon(savedTheme);

            toggle.addEventListener('click', () => {
                const currentTheme = html.getAttribute('data-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                html.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                this.updateThemeIcon(newTheme);
            });
        },

        updateThemeIcon(theme) {
            const toggle = document.getElementById('theme-toggle');
            if (!toggle) return;

            const sunIcon = toggle.querySelector('.icon-sun');
            const moonIcon = toggle.querySelector('.icon-moon');

            if (theme === 'dark') {
                sunIcon.style.display = 'block';
                moonIcon.style.display = 'none';
            } else {
                sunIcon.style.display = 'none';
                moonIcon.style.display = 'block';
            }
        },

        initUserMenu() {
            const toggle = document.querySelector('.user-menu-toggle');
            const dropdown = document.querySelector('.user-menu-dropdown');
            if (!toggle || !dropdown) return;

            toggle.addEventListener('click', (e) => {
                e.stopPropagation();
                const expanded = toggle.getAttribute('aria-expanded') === 'true';
                toggle.setAttribute('aria-expanded', !expanded);
            });

            document.addEventListener('click', (e) => {
                if (!toggle.contains(e.target) && !dropdown.contains(e.target)) {
                    toggle.setAttribute('aria-expanded', 'false');
                }
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    toggle.setAttribute('aria-expanded', 'false');
                }
            });
        },

        initForms() {
            document.querySelectorAll('form[data-ajax]').forEach(form => {
                form.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const submitBtn = form.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<svg class="icon spinner" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="30 100" stroke-linecap="round"><animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="1s" repeatCount="indefinite"/></svg> Processing...';

                    try {
                        const formData = new FormData(form);
                        const response = await fetch(form.action, {
                            method: form.method,
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || ''
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.showFlash('message', data.message || 'Success!');
                            if (data.redirect) {
                                setTimeout(() => window.location.href = data.redirect, 500);
                            }
                        } else {
                            this.showFlash('error', data.message || 'An error occurred');
                            if (data.errors) {
                                this.showFieldErrors(form, data.errors);
                            }
                        }
                    } catch (error) {
                        this.showFlash('error', 'Network error. Please try again.');
                    } finally {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                });
            });
        },

        showFieldErrors(form, errors) {
            form.querySelectorAll('.field-error').forEach(el => el.remove());
            form.querySelectorAll('.form-group input, .form-group textarea, .form-group select').forEach(input => {
                input.classList.remove('error');
            });

            Object.entries(errors).forEach(([field, messages]) => {
                const input = form.querySelector(`[name="${field}"]`);
                if (input) {
                    input.classList.add('error');
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'field-error';
                    errorDiv.style.cssText = 'color: var(--color-error); font-size: 0.75rem; margin-top: 0.25rem;';
                    errorDiv.textContent = Array.isArray(messages) ? messages[0] : messages;
                    input.parentNode.appendChild(errorDiv);
                }
            });
        },

        initFlashMessages() {
            document.querySelectorAll('.flash').forEach(flash => {
                setTimeout(() => {
                    flash.style.opacity = '0';
                    flash.style.transform = 'translateX(100%)';
                    setTimeout(() => flash.remove(), 300);
                }, 5000);
            });
        },

        showFlash(type, message) {
            const container = document.querySelector('.flash-messages') || this.createFlashContainer();
            const flash = document.createElement('div');
            flash.className = `flash flash-${type}`;
            flash.innerHTML = `
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    ${type === 'success' ? '<polyline points="20 6 9 17 4 12"/>' : '<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>'}
                </svg>
                ${message}
            `;
            container.appendChild(flash);

            setTimeout(() => {
                flash.style.opacity = '0';
                flash.style.transform = 'translateX(100%)';
                setTimeout(() => flash.remove(), 300);
            }, 5000);
        },

        createFlashContainer() {
            const container = document.createElement('div');
            container.className = 'flash-messages';
            container.style.cssText = 'position: fixed; top: calc(var(--header-height) + 1rem); right: 1.5rem; z-index: 200; display: flex; flex-direction: column; gap: 0.5rem; max-width: 400px;';
            document.body.appendChild(container);
            return container;
        },

        initTooltips() {
            let tooltip = null;

            document.addEventListener('mouseenter', (e) => {
                const target = e.target.closest('[data-tooltip]');
                if (!target) return;

                tooltip = document.createElement('div');
                tooltip.className = 'tooltip';
                tooltip.textContent = target.dataset.tooltip;
                tooltip.style.cssText = `
                    position: fixed;
                    background: var(--color-text);
                    color: var(--color-bg);
                    padding: 0.5rem 0.75rem;
                    border-radius: var(--radius);
                    font-size: 0.75rem;
                    z-index: 10000;
                    pointer-events: none;
                    white-space: nowrap;
                `;
                document.body.appendChild(tooltip);

                const rect = target.getBoundingClientRect();
                tooltip.style.left = rect.left + rect.width / 2 - tooltip.offsetWidth / 2 + 'px';
                tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';
            }, true);

            document.addEventListener('mouseleave', (e) => {
                if (tooltip) {
                    tooltip.remove();
                    tooltip = null;
                }
            }, true);
        },

        initCopyButtons() {
            document.querySelectorAll('[data-copy]').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const text = btn.dataset.copy;
                    try {
                        await navigator.clipboard.writeText(text);
                        this.showFlash('message', 'Copied to clipboard!');
                    } catch (e) {
                        this.showFlash('error', 'Failed to copy');
                    }
                });
            });
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => App.init());
    } else {
        App.init();
    }

    window.SQLDetective = App;
})();