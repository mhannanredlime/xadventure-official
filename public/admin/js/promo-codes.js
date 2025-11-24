/**
 * Promo Codes Management JavaScript
 * Handles dynamic functionality for promo codes management
 */

class PromoCodesManager {
    constructor() {
        this.currentPromoId = null;
        this.debounceTimer = null;
        this.init();
    }

    init() {
        this.bindEvents();
        this.initializeValidation();
        this.setupKeyboardShortcuts();
    }

    bindEvents() {
        // Filter events
        const filters = ['packageFilter', 'vehicleFilter', 'statusFilter'];
        filters.forEach(filterId => {
            const element = document.getElementById(filterId);
            if (element) {
                element.addEventListener('change', () => this.filterPromoCodes());
            }
        });

        // Form events - only for clearing errors on input
        const form = document.getElementById('promoForm');
        if (form) {
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.addEventListener('input', () => this.clearFieldError(input));
            });
            
            // Add event listeners for radio buttons
            const radioButtons = form.querySelectorAll('input[type="radio"]');
            radioButtons.forEach(radio => {
                radio.addEventListener('change', () => this.clearFieldError(radio));
            });
        }

        // Real-time promo code validation
        const promoCodeInput = document.getElementById('promoCode');
        if (promoCodeInput) {
            promoCodeInput.addEventListener('input', (e) => {
                this.debounce(() => this.validatePromoCode(e.target.value), 500);
            });
        }
    }

    initializeValidation() {
        // Handle applies to change
        const appliesTo = document.getElementById('appliesTo');
        if (appliesTo) {
            appliesTo.addEventListener('change', () => this.handleAppliesToChange());
        }

        // Handle discount type change
        const discountTypeRadios = document.querySelectorAll('input[name="discount_type"]');
        discountTypeRadios.forEach(radio => {
            radio.addEventListener('change', () => this.handleDiscountTypeChange());
        });
    }

    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Escape to close modal
            if (e.key === 'Escape') {
                this.closePromoModal();
            }
            
            // Ctrl+N to open new promo modal
            if (e.ctrlKey && e.key === 'n') {
                e.preventDefault();
                this.openPromoModal();
            }
            
            // Ctrl+S to save (when modal is open)
            if (e.ctrlKey && e.key === 's' && document.getElementById('promoModal').style.display === 'flex') {
                e.preventDefault();
                this.savePromoCode();
            }
        });
    }

    filterPromoCodes() {
        const packageValue = document.getElementById('packageFilter')?.value || '';
        const vehicleValue = document.getElementById('vehicleFilter')?.value || '';
        const statusValue = document.getElementById('statusFilter')?.value || '';
        
        const rows = document.querySelectorAll('#promoCodesTable tbody tr');
        let visibleCount = 0;

        rows.forEach(row => {
            const packageId = row.getAttribute('data-package');
            const vehicleId = row.getAttribute('data-vehicle');
            const status = row.getAttribute('data-status');
            
            const packageMatch = !packageValue || packageId === packageValue;
            const vehicleMatch = !vehicleValue || vehicleId === vehicleValue;
            const statusMatch = !statusValue || status === statusValue;
            
            if (packageMatch && vehicleMatch && statusMatch) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const showingCount = document.getElementById('showingCount');
        if (showingCount) {
            showingCount.textContent = visibleCount;
        }
    }

    openPromoModal(promoId = null) {
        this.currentPromoId = promoId;
        const modal = document.getElementById('promoModal');
        const modalTitle = document.getElementById('modalTitle');
        const formMethod = document.getElementById('formMethod');

        if (promoId) {
            modalTitle.textContent = 'Edit Promo Code';
            formMethod.value = 'PUT';
            this.loadPromoData(promoId);
        } else {
            modalTitle.textContent = 'Add Promo Code';
            formMethod.value = 'POST';
            this.resetForm();
        }

        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        // Ensure form is properly initialized
        this.initializeForm();
        
        // Focus on first input
        setTimeout(() => {
            const firstInput = modal.querySelector('input, select');
            if (firstInput) firstInput.focus();
        }, 100);
    }

    closePromoModal() {
        const modal = document.getElementById('promoModal');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        this.resetForm();
    }

    resetForm() {
        const form = document.getElementById('promoForm');
        if (form) {
            form.reset();
            document.getElementById('promoId').value = '';
            document.getElementById('appliesTo').value = 'all';
            document.getElementById('packageSelectGroup').style.display = 'none';
            document.getElementById('vehicleSelectGroup').style.display = 'none';
            document.getElementById('usageLimitPerUser').value = '1';
            
            // Ensure radio buttons are properly set
            const activeRadio = document.querySelector('input[name="status"][value="active"]');
            if (activeRadio) activeRadio.checked = true;
            
            const percentageRadio = document.querySelector('input[name="discount_type"][value="percentage"]');
            if (percentageRadio) percentageRadio.checked = true;
            
            this.clearValidationErrors();
        }
    }

    initializeForm() {
        // Ensure usage limit per user has a default value
        const usageLimitPerUser = document.getElementById('usageLimitPerUser');
        if (usageLimitPerUser && !usageLimitPerUser.value) {
            usageLimitPerUser.value = '1';
        }
        
        // Handle applies to change to show/hide conditional fields
        this.handleAppliesToChange();
        
        // Clear any existing validation errors
        this.clearValidationErrors();
    }

    async loadPromoData(promoId) {
        try {
            const response = await fetch(`/admin/promo-codes/${promoId}/edit`);
            const data = await response.json();
            
            if (data.success) {
                const promo = data.promo;
                this.populateForm(promo);
            } else {
                this.showAlert(data.message || 'Error loading promo code data', 'error');
            }
        } catch (error) {
            this.showAlert('Error loading promo code data', 'error');
        }
    }

    populateForm(promo) {
        const fields = {
            'promoId': promo.id,
            'appliesTo': promo.applies_to,
            'promoCode': promo.code,
            'discountValue': promo.discount_value,
            'maxDiscount': promo.max_discount || '',
            'minSpend': promo.min_spend || '',
            'usageLimitTotal': promo.usage_limit_total || '',
            'usageLimitPerUser': promo.usage_limit_per_user,
            'startDate': promo.starts_at ? promo.starts_at.split('T')[0] : '',
            'expireDate': promo.ends_at ? promo.ends_at.split('T')[0] : '',
            'remarks': promo.remarks || ''
        };

        // Set all field values
        Object.entries(fields).forEach(([fieldId, value]) => {
            const element = document.getElementById(fieldId);
            if (element) {
                element.value = value;
            }
        });

        // Set radio buttons
        const discountTypeRadio = document.querySelector(`input[name="discount_type"][value="${promo.discount_type}"]`);
        const statusRadio = document.querySelector(`input[name="status"][value="${promo.status}"]`);
        
        if (discountTypeRadio) {
            discountTypeRadio.checked = true;
        }
        if (statusRadio) {
            statusRadio.checked = true;
        }

        // Handle applies to selection and show/hide conditional fields
        this.handleAppliesToChange();
        
        // Set conditional field values
        if (promo.applies_to === 'package' && promo.package_id) {
            const packageSelect = document.getElementById('packageName');
            if (packageSelect) {
                packageSelect.value = promo.package_id;
            }
        } else if (promo.applies_to === 'vehicle_type' && promo.vehicle_type_id) {
            const vehicleSelect = document.getElementById('vehicleType');
            if (vehicleSelect) {
                vehicleSelect.value = promo.vehicle_type_id;
            }
        }
    }

    async savePromoCode() {
        // Get the form element
        const form = document.getElementById('promoForm');
        if (!form) {
            return;
        }
        
        // Set the correct action URL
        if (this.currentPromoId) {
            form.action = `/admin/promo-codes/${this.currentPromoId}`;
            document.getElementById('formMethod').value = 'PUT';
        } else {
            form.action = '/admin/promo-codes';
            document.getElementById('formMethod').value = 'POST';
        }
        
        // Get form data
        const formData = new FormData(form);
        
        // Submit using fetch
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                
                if (data.success) {
                    this.showAlert(data.message, 'success');
                    this.closePromoModal();
                    location.reload();
                } else {
                    this.showAlert(data.message || 'Error saving promo code', 'error');
                    if (data.errors) {
                        this.displayValidationErrors(data.errors);
                    }
                }
            } else {
                const errorData = await response.json();
                this.showAlert(errorData.message || 'Server error occurred', 'error');
                if (errorData.errors) {
                    this.displayValidationErrors(errorData.errors);
                }
            }
        } catch (error) {
            this.showAlert('Network error occurred', 'error');
        }
    }

    ensureFormValues() {
        // Ensure applies_to has a value
        const appliesTo = document.getElementById('appliesTo');
        if (appliesTo && !appliesTo.value) {
            appliesTo.value = 'all';
        }
        
        // Ensure discount type is selected
        const discountTypeChecked = document.querySelector('input[name="discount_type"]:checked');
        if (!discountTypeChecked) {
            const percentageRadio = document.querySelector('input[name="discount_type"][value="percentage"]');
            if (percentageRadio) {
                percentageRadio.checked = true;
            }
        }
        
        // Ensure status is selected
        const statusChecked = document.querySelector('input[name="status"]:checked');
        if (!statusChecked) {
            const activeRadio = document.querySelector('input[name="status"][value="active"]');
            if (activeRadio) {
                activeRadio.checked = true;
            }
        }
        
        // Ensure usage limit per user has a value
        const usageLimitPerUser = document.getElementById('usageLimitPerUser');
        if (usageLimitPerUser && !usageLimitPerUser.value) {
            usageLimitPerUser.value = '1';
        }
    }

    async deletePromoCode(promoId) {
        if (!confirm('Are you sure you want to delete this promo code?')) {
            return;
        }

        try {
            const response = await fetch(`/admin/promo-codes/${promoId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await response.json();
            
            if (data.success) {
                this.showAlert(data.message, 'success');
                location.reload();
            } else {
                this.showAlert(data.message || 'Error deleting promo code', 'error');
            }
        } catch (error) {
            this.showAlert('Error deleting promo code', 'error');
        }
    }

    async togglePromoStatus(promoId) {
        try {
            const response = await fetch(`/admin/promo-codes/${promoId}/toggle`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await response.json();
            
            if (data.success) {
                this.showAlert(data.message, 'success');
                location.reload();
            } else {
                this.showAlert(data.message || 'Error toggling promo code status', 'error');
            }
        } catch (error) {
            this.showAlert('Error toggling promo code status', 'error');
        }
    }

    async validatePromoCode(code) {
        if (!code || code.length < 3) return;

        try {
            const response = await fetch('/admin/promo-codes/validate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    code: code,
                    exclude_id: this.currentPromoId
                })
            });

            const data = await response.json();
            
            if (data.success) {
                const input = document.getElementById('promoCode');
                if (data.available) {
                    this.clearFieldError(input);
                    input.classList.add('is-valid');
                } else {
                    this.showFieldError(input, 'Promo code already exists');
                    input.classList.remove('is-valid');
                }
            }
        } catch (error) {
            // Error validating promo code
        }
    }

    handleAppliesToChange() {
        const appliesTo = document.getElementById('appliesTo').value;
        const packageGroup = document.getElementById('packageSelectGroup');
        const vehicleGroup = document.getElementById('vehicleSelectGroup');

        if (packageGroup) packageGroup.style.display = appliesTo === 'package' ? 'block' : 'none';
        if (vehicleGroup) vehicleGroup.style.display = appliesTo === 'vehicle_type' ? 'block' : 'none';

        // Clear values when hiding
        if (appliesTo !== 'package') {
            const packageSelect = document.getElementById('packageName');
            if (packageSelect) packageSelect.value = '';
        }
        if (appliesTo !== 'vehicle_type') {
            const vehicleSelect = document.getElementById('vehicleType');
            if (vehicleSelect) vehicleSelect.value = '';
        }
    }

    handleDiscountTypeChange() {
        const discountType = document.querySelector('input[name="discount_type"]:checked')?.value;
        const discountValue = document.getElementById('discountValue');
        
        if (discountType === 'percentage') {
            discountValue.max = '100';
            discountValue.placeholder = 'Enter percentage (0-100)';
        } else {
            discountValue.removeAttribute('max');
            discountValue.placeholder = 'Enter amount in ৳';
        }
    }

    showFieldError(field, message) {
        this.clearFieldError(field);
        
        // Handle radio buttons - add error to all radios in the group
        if (field.type === 'radio') {
            const radioGroup = document.querySelectorAll(`input[name="${field.name}"]`);
            radioGroup.forEach(radio => {
                radio.classList.add('is-invalid');
            });
            // Add error message to the parent container
            const parentContainer = field.closest('.mb-3');
            if (parentContainer) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback';
                errorDiv.textContent = message;
                parentContainer.appendChild(errorDiv);
            }
            return;
        }
        
        field.classList.add('is-invalid');
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = message;
        field.parentNode.appendChild(errorDiv);
    }

    clearFieldError(field) {
        // Handle radio buttons - clear error from all radios in the group
        if (field.type === 'radio') {
            const radioGroup = document.querySelectorAll(`input[name="${field.name}"]`);
            radioGroup.forEach(radio => {
                radio.classList.remove('is-invalid');
            });
            // Remove error message from the parent container
            const parentContainer = field.closest('.mb-3');
            if (parentContainer) {
                const errorDiv = parentContainer.querySelector('.invalid-feedback');
                if (errorDiv) {
                    errorDiv.remove();
                }
            }
            return;
        }
        
        field.classList.remove('is-invalid');
        const errorDiv = field.parentNode.querySelector('.invalid-feedback');
        if (errorDiv) {
            errorDiv.remove();
        }
    }

    clearValidationErrors() {
        const errorDivs = document.querySelectorAll('.invalid-feedback');
        errorDivs.forEach(div => div.remove());
        
        const invalidFields = document.querySelectorAll('.is-invalid');
        invalidFields.forEach(field => field.classList.remove('is-invalid'));
    }

    displayValidationErrors(errors) {
        Object.entries(errors).forEach(([field, messages]) => {
            const fieldElement = document.getElementById(field) || document.querySelector(`[name="${field}"]`);
            if (fieldElement && messages.length > 0) {
                this.showFieldError(fieldElement, messages[0]);
            }
        });
    }

    showAlert(message, type) {
        if (typeof toastNotifications !== 'undefined') {
            const toastType = type === 'success' ? 'success' : 'error';
            toastNotifications[toastType](message);
        } else {
            // Fallback to console if toast system is not available
            console[type === 'success' ? 'log' : 'error'](message);
        }
    }

    debounce(func, wait) {
        clearTimeout(this.debounceTimer);
        this.debounceTimer = setTimeout(func, wait);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.promoCodesManager = new PromoCodesManager();
});

// Global functions for backward compatibility
function openPromoModal(promoId = null) {
    if (window.promoCodesManager) {
        window.promoCodesManager.openPromoModal(promoId);
    }
}

function closePromoModal() {
    if (window.promoCodesManager) {
        window.promoCodesManager.closePromoModal();
    }
}

function savePromoCode() {
    if (window.promoCodesManager) {
        window.promoCodesManager.savePromoCode();
    }
}

function editPromoCode(promoId) {
    if (window.promoCodesManager) {
        window.promoCodesManager.openPromoModal(promoId);
    }
}

function deletePromoCode(promoId) {
    if (window.promoCodesManager) {
        window.promoCodesManager.deletePromoCode(promoId);
    }
}

function togglePromoStatus(promoId) {
    if (window.promoCodesManager) {
        window.promoCodesManager.togglePromoStatus(promoId);
    }
}

function filterPromoCodes() {
    if (window.promoCodesManager) {
        window.promoCodesManager.filterPromoCodes();
    }
}

// Test function for debugging
function testPromoForm() {
    // Check if form exists
    const form = document.getElementById('promoForm');
    if (!form) {
        return;
    }
    
    // Check form validity
    form.checkValidity();
    
    // Check all required fields
    const requiredFields = [
        'appliesTo',
        'promoCode', 
        'discountValue',
        'usageLimitPerUser'
    ];
    
    requiredFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.checkValidity();
        }
    });
    
    // Check radio buttons
    const discountTypeChecked = document.querySelector('input[name="discount_type"]:checked');
    const statusChecked = document.querySelector('input[name="status"]:checked');
}

// Simple test function to submit form directly
function testSubmit() {
    const form = document.getElementById('promoForm');
    if (form) {
        form.submit();
    }
}
