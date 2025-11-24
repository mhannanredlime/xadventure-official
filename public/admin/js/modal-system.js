/**
 * Modal System
 * A comprehensive modal system for confirmations and complex interactions
 */
class ModalSystem {
    constructor() {
        this.init();
    }

    init() {
        this.createModalContainer();
        this.addStyles();
    }

    createModalContainer() {
        if (!document.getElementById('modal-container')) {
            const container = document.createElement('div');
            container.id = 'modal-container';
            container.className = 'modal-container';
            document.body.appendChild(container);
        }
    }

    addStyles() {
        if (!document.getElementById('modal-styles')) {
            const style = document.createElement('style');
            style.id = 'modal-styles';
            style.textContent = `
                .modal-container {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    z-index: 10000;
                    display: none;
                    align-items: center;
                    justify-content: center;
                    background-color: rgba(0, 0, 0, 0.6);
                    backdrop-filter: blur(4px);
                    pointer-events: auto;
                }

                .modal-container.show {
                    display: flex;
                    animation: fadeIn 0.3s ease-out;
                }

                @keyframes fadeIn {
                    from {
                        opacity: 0;
                    }
                    to {
                        opacity: 1;
                    }
                }

                .modal-dialog {
                    background: #ffffff;
                    border-radius: 16px;
                    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
                    max-width: 500px;
                    width: 90%;
                    max-height: 90vh;
                    overflow: hidden;
                    animation: slideIn 0.3s ease-out;
                    pointer-events: auto;
                    position: relative;
                    z-index: 10001;
                    border: 1px solid #e5e7eb;
                }

                @keyframes slideIn {
                    from {
                        transform: translateY(-30px) scale(0.95);
                        opacity: 0;
                    }
                    to {
                        transform: translateY(0) scale(1);
                        opacity: 1;
                    }
                }

                .modal-header {
                    padding: 28px 36px 0;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    border-bottom: 1px solid #f3f4f6;
                }

                .confirmation-modal .modal-header {
                    padding: 32px 36px 0;
                }

                .modal-title {
                    font-size: 20px;
                    font-weight: 700;
                    color: #1f2937;
                    margin: 0;
                    font-family: 'Inter', sans-serif;
                }

                .modal-close {
                    background: none;
                    border: none;
                    font-size: 24px;
                    cursor: pointer;
                    color: #9ca3af;
                    padding: 8px;
                    width: 36px;
                    height: 36px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    border-radius: 50%;
                    transition: all 0.2s ease;
                }

                .modal-close:hover {
                    background-color: #f3f4f6;
                    color: #6b7280;
                    transform: scale(1.1);
                }

                .modal-body {
                    padding: 24px 28px;
                    color: #4b5563;
                    font-size: 15px;
                    line-height: 1.6;
                    max-height: 60vh;
                    overflow-y: auto;
                    font-family: 'Inter', sans-serif;
                }

                .modal-footer {
                    padding: 0 28px 24px;
                    display: flex;
                    gap: 12px;
                    justify-content: flex-end;
                    border-top: 1px solid #f3f4f6;
                    margin-top: 8px;
                    padding-top: 20px;
                }

                .modal-btn {
                    padding: 12px 24px;
                    border: none;
                    border-radius: 8px;
                    font-size: 14px;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.2s ease;
                    min-width: 90px;
                    font-family: 'Inter', sans-serif;
                    text-transform: none;
                    letter-spacing: 0.025em;
                }

                .modal-btn-primary {
                    background-color: #ff6600;
                    color: white;
                    box-shadow: 0 2px 4px rgba(255, 102, 0, 0.2);
                }

                .modal-btn-primary:hover {
                    background-color: #e55a00;
                    transform: translateY(-1px);
                    box-shadow: 0 4px 8px rgba(255, 102, 0, 0.3);
                }

                .modal-btn-primary:active {
                    transform: translateY(0);
                    box-shadow: 0 2px 4px rgba(255, 102, 0, 0.2);
                }

                .modal-btn-secondary {
                    background-color: #f8f9fa;
                    color: #6b7280;
                    border: 2px solid #e5e7eb;
                }

                .modal-btn-secondary:hover {
                    background-color: #f3f4f6;
                    color: #4b5563;
                    border-color: #d1d5db;
                    transform: translateY(-1px);
                }

                .modal-btn-secondary:active {
                    transform: translateY(0);
                }

                .modal-btn-danger {
                    background-color: #dc3545;
                    color: white;
                    box-shadow: 0 2px 4px rgba(220, 53, 69, 0.2);
                }

                .modal-btn-danger:hover {
                    background-color: #c82333;
                    transform: translateY(-1px);
                    box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
                }

                /* Confirmation modal button styling */
                .confirmation-modal .modal-footer {
                    justify-content: center;
                    gap: 20px;
                    padding: 24px 36px 32px;
                }

                .confirmation-modal .modal-btn {
                    min-width: 130px;
                    padding: 16px 32px;
                    font-size: 15px;
                    font-weight: 600;
                    cursor: pointer;
                    pointer-events: auto;
                    z-index: 1000;
                    position: relative;
                }

                .modal-btn-success {
                    background-color: #28a745;
                    color: white;
                    box-shadow: 0 2px 4px rgba(40, 167, 69, 0.2);
                }

                .modal-btn-success:hover {
                    background-color: #1e7e34;
                    transform: translateY(-1px);
                    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
                }

                .modal-btn:disabled {
                    opacity: 0.6;
                    cursor: not-allowed;
                    transform: none !important;
                }

                .modal-btn:focus {
                    outline: 2px solid #ff6600;
                    outline-offset: 2px;
                }

                .modal-body input[type="text"] {
                    width: 100%;
                    padding: 12px 16px;
                    border: 2px solid #e5e7eb;
                    border-radius: 8px;
                    font-size: 14px;
                    margin-top: 12px;
                    font-family: 'Inter', sans-serif;
                    transition: all 0.2s ease;
                }

                .modal-body input[type="text"]:focus {
                    outline: none;
                    border-color: #ff6600;
                    box-shadow: 0 0 0 3px rgba(255, 102, 0, 0.1);
                }

                /* Confirmation modal specific styles */
                .confirmation-modal .modal-body {
                    text-align: center;
                    padding: 40px 36px;
                }

                .confirmation-icon {
                    font-size: 64px;
                    margin-bottom: 28px;
                    font-weight: 300;
                    display: block;
                }

                .confirmation-icon.warning {
                    color: #f59e0b;
                }

                .confirmation-icon.danger {
                    color: #ef4444;
                }

                .confirmation-icon.success {
                    color: #10b981;
                }

                .confirmation-icon.info {
                    color: #3b82f6;
                }

                .confirmation-icon.confirmation {
                    color: #ff6600;
                    font-size: 72px;
                    margin-bottom: 24px;
                }

                .confirmation-modal .modal-body > div {
                    font-size: 16px;
                    line-height: 1.6;
                    color: #374151;
                    font-weight: 500;
                    margin: 0 16px;
                }

                /* Responsive */
                @media (max-width: 768px) {
                    .modal-dialog {
                        width: 95%;
                        margin: 20px;
                        border-radius: 12px;
                    }

                    .modal-header {
                        padding: 24px 28px 0;
                    }

                    .confirmation-modal .modal-header {
                        padding: 28px 28px 0;
                    }

                    .modal-body {
                        padding: 24px 28px;
                    }

                    .confirmation-modal .modal-body {
                        padding: 32px 28px;
                    }

                    .modal-footer {
                        padding: 0 28px 24px;
                        flex-direction: column;
                        gap: 12px;
                    }

                    .confirmation-modal .modal-footer {
                        padding: 20px 28px 28px;
                    }

                    .modal-btn {
                        width: 100%;
                        padding: 16px 24px;
                    }

                    .confirmation-modal .modal-btn {
                        padding: 18px 24px;
                    }

                    .modal-title {
                        font-size: 18px;
                    }

                    .confirmation-modal .modal-body > div {
                        margin: 0 8px;
                    }
                }
            `;
            document.head.appendChild(style);
        }
    }

    show(options) {
        const {
            title = 'Modal',
            message = '',
            type = 'info',
            showCancel = true,
            confirmText = 'OK',
            cancelText = 'Cancel',
            onConfirm = null,
            onCancel = null,
            onClose = null,
            closeable = true,
            html = null
        } = options;

        // Close any existing modal first
        this.close();

        const modal = this.createModal({
            title,
            message,
            type,
            showCancel,
            confirmText,
            cancelText,
            onConfirm,
            onCancel,
            onClose,
            closeable,
            html
        });

        const container = document.getElementById('modal-container');
        container.innerHTML = '';
        container.appendChild(modal);
        container.classList.add('show');

        // Add event listeners to buttons
        this.addEventListeners(modal);

        // Focus on first button
        setTimeout(() => {
            const firstBtn = modal.querySelector('.modal-btn');
            if (firstBtn) firstBtn.focus();
        }, 100);

        return modal;
    }

    createModal(options) {
        const {
            title,
            message,
            type,
            showCancel,
            confirmText,
            cancelText,
            onConfirm,
            onCancel,
            onClose,
            closeable,
            html
        } = options;

        const modal = document.createElement('div');
        modal.className = 'modal-dialog';

        const icon = this.getIcon(type);
        const closeButton = closeable ? this.createCloseButton(onClose) : '';

        modal.innerHTML = `
            <div class="modal-header">
                <h3 class="modal-title">${title}</h3>
                ${closeButton}
            </div>
            <div class="modal-body">
                ${html || this.createBodyContent(message, type, icon)}
            </div>
            <div class="modal-footer">
                ${this.createFooterButtons(showCancel, confirmText, cancelText, onConfirm, onCancel, type)}
            </div>
        `;

        return modal;
    }

    createBodyContent(message, type, icon) {
        if (type === 'confirmation') {
            return `
                <div class="confirmation-modal">
                    <div class="confirmation-icon ${type}">${icon}</div>
                    <div>${message}</div>
                </div>
            `;
        }
        return `<div>${message}</div>`;
    }

    createCloseButton(onClose) {
        return `<button class="modal-close" data-action="close" data-call-on-close="${onClose ? 'true' : 'false'}">&times;</button>`;
    }

    createFooterButtons(showCancel, confirmText, cancelText, onConfirm, onCancel, type = 'info') {
        let buttons = '';
        
        if (showCancel) {
            buttons += `<button class="modal-btn modal-btn-secondary" data-action="cancel" data-call-on-cancel="${onCancel ? 'true' : 'false'}">${cancelText}</button>`;
        }
        
        // Use different button styles based on type
        let buttonClass = 'modal-btn-primary';
        if (type === 'danger' || type === 'confirmation') {
            buttonClass = 'modal-btn-danger';
        } else if (type === 'success') {
            buttonClass = 'modal-btn-success';
        } else if (type === 'warning') {
            buttonClass = 'modal-btn-primary';
        }
        
        buttons += `<button class="modal-btn ${buttonClass}" data-action="confirm" data-call-on-confirm="${onConfirm ? 'true' : 'false'}">${confirmText}</button>`;
        
        return buttons;
    }

    getIcon(type) {
        const icons = {
            warning: '⚠',
            danger: '✕',
            success: '✓',
            info: 'ℹ',
            confirmation: '?'
        };
        return icons[type] || icons.info;
    }

    addEventListeners(modal) {
        // Close button
        const closeBtn = modal.querySelector('.modal-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                const callOnClose = closeBtn.getAttribute('data-call-on-close') === 'true';
                this.closeAction(callOnClose);
            });
        }

        // Confirm button
        const confirmBtn = modal.querySelector('[data-action="confirm"]');
        if (confirmBtn) {
            console.log('Adding click listener to confirm button:', confirmBtn);
            console.log('Button HTML:', confirmBtn.outerHTML);
            
            // Add onclick as backup
            confirmBtn.onclick = (e) => {
                console.log('Confirm button onclick triggered!', e);
                e.preventDefault();
                e.stopPropagation();
                const callOnConfirm = confirmBtn.getAttribute('data-call-on-confirm') === 'true';
                console.log('callOnConfirm attribute:', callOnConfirm);
                this.confirmAction(callOnConfirm);
            };
            
            confirmBtn.addEventListener('click', (e) => {
                console.log('Confirm button clicked!', e);
                e.preventDefault();
                e.stopPropagation();
                const callOnConfirm = confirmBtn.getAttribute('data-call-on-confirm') === 'true';
                console.log('callOnConfirm attribute:', callOnConfirm);
                this.confirmAction(callOnConfirm);
            });
            
            // Also try mousedown event as backup
            confirmBtn.addEventListener('mousedown', (e) => {
                console.log('Confirm button mousedown!', e);
            });
        } else {
            console.log('Confirm button not found!');
            console.log('Modal HTML:', modal.innerHTML);
        }

        // Cancel button
        const cancelBtn = modal.querySelector('[data-action="cancel"]');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', (e) => {
                e.preventDefault();
                const callOnCancel = cancelBtn.getAttribute('data-call-on-cancel') === 'true';
                this.cancelAction(callOnCancel);
            });
        }

        // Close modal when clicking outside
        const container = document.getElementById('modal-container');
        container.addEventListener('click', (e) => {
            if (e.target === container) {
                this.closeAction();
            }
        });

        // Keyboard support
        const handleKeyDown = (e) => {
            if (e.key === 'Escape') {
                this.closeAction();
            } else if (e.key === 'Enter') {
                const confirmBtn = modal.querySelector('[data-action="confirm"]');
                if (confirmBtn && document.activeElement === confirmBtn) {
                    const callOnConfirm = confirmBtn.getAttribute('data-call-on-confirm') === 'true';
                    this.confirmAction(callOnConfirm);
                }
            }
        };

        document.addEventListener('keydown', handleKeyDown);
        
        // Store the event listener for cleanup
        modal._keydownListener = handleKeyDown;
    }

    closeAction(callOnClose = false) {
        const container = document.getElementById('modal-container');
        const modal = container.querySelector('.modal-dialog');
        
        // Clean up event listeners
        if (modal && modal._keydownListener) {
            document.removeEventListener('keydown', modal._keydownListener);
            delete modal._keydownListener;
        }
        
        container.classList.remove('show');
        
        if (callOnClose && this.currentOnClose) {
            this.currentOnClose();
        }
    }

    close() {
        this.closeAction();
    }

    confirmAction(callOnConfirm = false) {
        console.log('confirmAction called with callOnConfirm:', callOnConfirm, 'currentOnConfirm:', this.currentOnConfirm);
        this.close();
        
        if (callOnConfirm && this.currentOnConfirm) {
            console.log('Calling onConfirm callback');
            this.currentOnConfirm();
        } else {
            console.log('Not calling callback - callOnConfirm:', callOnConfirm, 'hasCallback:', !!this.currentOnConfirm);
        }
    }

    cancelAction(callOnCancel = false) {
        this.close();
        
        if (callOnCancel && this.currentOnCancel) {
            this.currentOnCancel();
        }
    }

    cancel() {
        this.cancelAction();
    }

    // Convenience methods
    confirm(message, onConfirm, onCancel = null) {
        console.log('ModalSystem.confirm called with:', { message, onConfirm, onCancel });
        this.currentOnConfirm = onConfirm;
        this.currentOnCancel = onCancel;
        
        return this.show({
            title: 'Confirm Action',
            message,
            type: 'confirmation',
            confirmText: 'Yes',
            cancelText: 'No',
            onConfirm: true,
            onCancel: true
        });
    }

    alert(message, onClose = null) {
        this.currentOnClose = onClose;
        
        return this.show({
            title: 'Information',
            message,
            type: 'info',
            showCancel: false,
            confirmText: 'OK',
            onConfirm: true
        });
    }

    warning(message, onConfirm = null) {
        this.currentOnConfirm = onConfirm;
        
        return this.show({
            title: 'Warning',
            message,
            type: 'warning',
            confirmText: 'OK',
            onConfirm: true
        });
    }

    error(message, onConfirm = null) {
        this.currentOnConfirm = onConfirm;
        
        return this.show({
            title: 'Error',
            message,
            type: 'danger',
            confirmText: 'OK',
            onConfirm: true
        });
    }

    success(message, onConfirm = null) {
        this.currentOnConfirm = onConfirm;
        
        return this.show({
            title: 'Success',
            message,
            type: 'success',
            confirmText: 'OK',
            onConfirm: true
        });
    }

    prompt(message, defaultValue = '', onConfirm = null, onCancel = null) {
        this.currentOnConfirm = () => {
            const input = document.querySelector('#prompt-input');
            const value = input ? input.value : defaultValue;
            if (onConfirm) onConfirm(value);
        };
        this.currentOnCancel = onCancel;
        
        const inputHtml = `
            <div>
                <p>${message}</p>
                <input type="text" class="form-control" id="prompt-input" value="${defaultValue}" placeholder="Enter your response...">
            </div>
        `;
        
        const modal = this.show({
            title: 'Input Required',
            html: inputHtml,
            confirmText: 'OK',
            cancelText: 'Cancel',
            onConfirm: true,
            onCancel: true
        });

        // Focus on input
        setTimeout(() => {
            const input = modal.querySelector('#prompt-input');
            if (input) {
                input.focus();
                input.select();
            }
        }, 100);

        return modal;
    }
}

// Initialize modal system globally
const modalSystem = new ModalSystem();

// Global functions for backward compatibility
function showModal(options) {
    return modalSystem.show(options);
}

function showConfirm(message, onConfirm, onCancel = null) {
    return modalSystem.confirm(message, onConfirm, onCancel);
}

function showAlert(message, onClose = null) {
    return modalSystem.alert(message, onClose);
}

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ModalSystem;
}
