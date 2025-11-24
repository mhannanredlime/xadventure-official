/**
 * Toast Notification System
 * A comprehensive toast notification system to replace all window alerts
 */
class ToastNotifications {
    constructor() {
        this.init();
    }

    init() {
        // Create toast container if it doesn't exist
        this.createToastContainer();
        
        // Add CSS styles
        this.addStyles();
    }

    createToastContainer() {
        if (!document.getElementById('toast-container')) {
            const container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container';
            document.body.appendChild(container);
        }
    }

    addStyles() {
        if (!document.getElementById('toast-styles')) {
            const style = document.createElement('style');
            style.id = 'toast-styles';
            style.textContent = `
                .toast-container {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    z-index: 10000;
                    max-width: 400px;
                }

                .toast-notification {
                    background: white;
                    border-radius: 8px;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                    margin-bottom: 10px;
                    overflow: hidden;
                    animation: slideInRight 0.3s ease-out;
                    max-width: 400px;
                    min-width: 300px;
                }

                @keyframes slideInRight {
                    from {
                        transform: translateX(100%);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }

                @keyframes slideOutRight {
                    from {
                        transform: translateX(0);
                        opacity: 1;
                    }
                    to {
                        transform: translateX(100%);
                        opacity: 0;
                    }
                }

                .toast-notification.removing {
                    animation: slideOutRight 0.3s ease-in;
                }

                .toast-header {
                    display: flex;
                    align-items: center;
                    padding: 12px 16px;
                    border-bottom: 1px solid #e9ecef;
                }

                .toast-icon {
                    width: 20px;
                    height: 20px;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin-right: 12px;
                    font-size: 12px;
                    font-weight: bold;
                }

                .toast-icon.success {
                    background-color: #d4edda;
                    color: #155724;
                }

                .toast-icon.error {
                    background-color: #f8d7da;
                    color: #721c24;
                }

                .toast-icon.warning {
                    background-color: #fff3cd;
                    color: #856404;
                }

                .toast-icon.info {
                    background-color: #d1ecf1;
                    color: #0c5460;
                }

                .toast-title {
                    font-weight: 600;
                    font-size: 14px;
                    color: #333;
                    flex: 1;
                }

                .toast-close {
                    background: none;
                    border: none;
                    font-size: 18px;
                    cursor: pointer;
                    color: #999;
                    padding: 0;
                    width: 20px;
                    height: 20px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    border-radius: 50%;
                    transition: all 0.2s;
                }

                .toast-close:hover {
                    background-color: #f8f9fa;
                    color: #666;
                }

                .toast-body {
                    padding: 12px 16px;
                    color: #666;
                    font-size: 14px;
                    line-height: 1.4;
                }

                .toast-progress {
                    height: 3px;
                    background-color: #e9ecef;
                    position: relative;
                    overflow: hidden;
                }

                .toast-progress-bar {
                    height: 100%;
                    background-color: #007bff;
                    transition: width linear;
                }

                .toast-progress-bar.success {
                    background-color: #28a745;
                }

                .toast-progress-bar.error {
                    background-color: #dc3545;
                }

                .toast-progress-bar.warning {
                    background-color: #ffc107;
                }

                .toast-progress-bar.info {
                    background-color: #17a2b8;
                }

                /* Responsive */
                @media (max-width: 768px) {
                    .toast-container {
                        top: 10px;
                        right: 10px;
                        left: 10px;
                        max-width: none;
                    }

                    .toast-notification {
                        max-width: none;
                        min-width: auto;
                    }
                }
            `;
            document.head.appendChild(style);
        }
    }

    show(message, type = 'info', options = {}) {
        const {
            title = this.getDefaultTitle(type),
            duration = 5000,
            showProgress = true,
            closeable = true,
            onClose = null
        } = options;

        const toast = this.createToast(message, type, title, closeable, onClose);
        const container = document.getElementById('toast-container');
        container.appendChild(toast);

        // Auto remove after duration
        if (duration > 0) {
            setTimeout(() => {
                this.remove(toast);
            }, duration);
        }

        // Progress bar
        if (showProgress && duration > 0) {
            this.startProgressBar(toast, duration, type);
        }

        return toast;
    }

    createToast(message, type, title, closeable, onClose) {
        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.setAttribute('data-type', type);

        const icon = this.getIcon(type);
        const closeButton = closeable ? this.createCloseButton(toast, onClose) : '';

        toast.innerHTML = `
            <div class="toast-header">
                <div class="toast-icon ${type}">${icon}</div>
                <div class="toast-title">${title}</div>
                ${closeButton}
            </div>
            <div class="toast-body">${message}</div>
            <div class="toast-progress">
                <div class="toast-progress-bar ${type}"></div>
            </div>
        `;

        return toast;
    }

    createCloseButton(toast, onClose) {
        return `<button class="toast-close" onclick="toastNotifications.remove(this.closest('.toast-notification'), ${onClose ? 'true' : 'false'})">&times;</button>`;
    }

    getIcon(type) {
        const icons = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };
        return icons[type] || icons.info;
    }

    getDefaultTitle(type) {
        const titles = {
            success: 'Success',
            error: 'Error',
            warning: 'Warning',
            info: 'Information'
        };
        return titles[type] || titles.info;
    }

    startProgressBar(toast, duration, type) {
        const progressBar = toast.querySelector('.toast-progress-bar');
        if (progressBar) {
            progressBar.style.width = '100%';
            progressBar.style.transition = `width ${duration}ms linear`;
            
            // Start the progress animation
            setTimeout(() => {
                progressBar.style.width = '0%';
            }, 100);
        }
    }

    remove(toast, callOnClose = false) {
        if (!toast) return;

        toast.classList.add('removing');
        
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }

    // Convenience methods
    success(message, options = {}) {
        return this.show(message, 'success', options);
    }

    error(message, options = {}) {
        return this.show(message, 'error', options);
    }

    warning(message, options = {}) {
        return this.show(message, 'warning', options);
    }

    info(message, options = {}) {
        return this.show(message, 'info', options);
    }

    // Clear all toasts
    clearAll() {
        const container = document.getElementById('toast-container');
        if (container) {
            container.innerHTML = '';
        }
    }
}

// Initialize toast notifications globally
const toastNotifications = new ToastNotifications();

// Global function for backward compatibility
function showToast(message, type = 'info', options = {}) {
    return toastNotifications.show(message, type, options);
}

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ToastNotifications;
}
