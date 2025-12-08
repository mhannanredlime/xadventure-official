
document.addEventListener('alpine:init', () => {
    Alpine.data('promoManager', () => ({
        // Filter State
        filters: {
            vehicle: '',
            status: '',
            package: '' // Added if package filter exists in DOM
        },

        // Modal State
        showModal: false,
        formTitle: 'Add Promo Code',
        formMethod: 'POST',
        formAction: '/admin/promo-codes',

        // Form Data
        form: {
            id: '',
            applies_to: 'all',
            package_id: '',
            vehicle_type_id: '',
            code: '',
            discount_type: 'percentage',
            discount_value: '',
            max_discount: '',
            min_spend: '',
            usage_limit_total: '',
            usage_limit_per_user: 1,
            starts_at: '',
            ends_at: '',
            remarks: '',
            status: 'active'
        },

        // Validation State
        errors: {},
        codeValid: null, // true, false, or null (unchecked)

        init() {
            // Initialize filtering watchers
            this.$watch('filters', () => this.applyFilters(), { deep: true });
        },

        // =======================
        // Filtering
        // =======================
        applyFilters() {
            const rows = document.querySelectorAll('#promoCodesTable tbody tr');
            let visibleCount = 0;

            rows.forEach(row => {
                const pkg = row.dataset.package || '';
                const vehicle = row.dataset.vehicle || '';
                const status = row.dataset.status || '';

                // Check matches
                // Note: The original script used specific IDs for filters. 
                // We assume filters.package maps to package_id, etc.
                const packageMatch = !this.filters.package || pkg === this.filters.package;
                const vehicleMatch = !this.filters.vehicle || vehicle === this.filters.vehicle;
                const statusMatch = !this.filters.status || status === this.filters.status;

                if (packageMatch && vehicleMatch && statusMatch) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Updates counts if elements exist
            const showingCount = document.getElementById('showingCount');
            if (showingCount) showingCount.textContent = visibleCount;
        },

        // =======================
        // Modal & Form
        // =======================
        openModal(id = null) {
            this.errors = {};
            this.codeValid = null;

            if (id) {
                // Edit Mode
                this.formTitle = 'Edit Promo Code';
                this.formMethod = 'PUT';
                this.formAction = `/admin/promo-codes/${id}`;
                this.loadPromoData(id);
            } else {
                // Create Mode
                this.formTitle = 'Add Promo Code';
                this.formMethod = 'POST';
                this.formAction = '/admin/promo-codes';
                this.resetForm();
                this.showModal = true;
            }
        },

        closeModal() {
            this.showModal = false;
        },

        resetForm() {
            this.form = {
                id: '',
                applies_to: 'all',
                package_id: '',
                vehicle_type_id: '',
                code: '',
                discount_type: 'percentage',
                discount_value: '',
                max_discount: '',
                min_spend: '',
                usage_limit_total: '',
                usage_limit_per_user: 1,
                starts_at: '',
                ends_at: '',
                remarks: '',
                status: 'active'
            };
        },

        async loadPromoData(id) {
            try {
                const response = await fetch(`/admin/promo-codes/${id}/edit`);
                const data = await response.json();
                if (data.success && data.promo) {
                    const p = data.promo;
                    this.form = {
                        id: p.id,
                        applies_to: p.applies_to,
                        package_id: p.package_id || '',
                        vehicle_type_id: p.vehicle_type_id || '',
                        code: p.code,
                        discount_type: p.discount_type,
                        discount_value: p.discount_value,
                        max_discount: p.max_discount || '',
                        min_spend: p.min_spend || '',
                        usage_limit_total: p.usage_limit_total || '',
                        usage_limit_per_user: p.usage_limit_per_user,
                        starts_at: p.starts_at ? p.starts_at.split('T')[0] : '', // YYYY-MM-DD
                        ends_at: p.ends_at ? p.ends_at.split('T')[0] : '',
                        remarks: p.remarks || '',
                        status: p.status
                    };
                    this.showModal = true;
                } else {
                    this.showAlert(data.message || 'Failed to load data', 'error');
                }
            } catch (e) {
                this.showAlert('Error loading data', 'error');
            }
        },

        // =======================
        // Validation
        // =======================
        // Debounce wrapper
        debounceValidate() {
            clearTimeout(this._timer);
            this._timer = setTimeout(() => {
                this.validateCode();
            }, 500);
        },

        async validateCode() {
            if (!this.form.code || this.form.code.length < 3) return;

            try {
                const response = await fetch('/admin/promo-codes/validate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        code: this.form.code,
                        exclude_id: this.form.id
                    })
                });
                const data = await response.json();
                if (data.success) {
                    this.codeValid = data.available;
                    if (!data.available) {
                        this.errors.code = ['Promo code already exists'];
                    } else {
                        delete this.errors.code;
                    }
                }
            } catch (e) {
                console.error(e);
            }
        },

        // =======================
        // Actions
        // =======================
        async submitForm() {
            const formData = new FormData();
            // Append all form fields
            Object.keys(this.form).forEach(key => {
                // Skip if null or undefined
                if (this.form[key] !== null) {
                    formData.append(key, this.form[key]);
                }
            });

            // Handle method spoofing for PUT
            if (this.formMethod === 'PUT') {
                formData.append('_method', 'PUT');
            }

            try {
                const response = await fetch(this.formAction, {
                    method: 'POST', // Always POST with _method spoofing for Laravel
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    this.showAlert(data.message, 'success');
                    this.closeModal();
                    window.location.reload(); // Simple reload to reflect changes
                } else {
                    this.showAlert(data.message || 'Error occurred', 'error');
                    if (data.errors) {
                        this.errors = data.errors;
                    }
                }
            } catch (e) {
                this.showAlert('Network Error', 'error');
            }
        },

        async deletePromo(id) {
            if (!confirm('Are you sure?')) return;
            try {
                const response = await fetch(`/admin/promo-codes/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                if (data.success) {
                    this.showAlert(data.message, 'success');
                    window.location.reload();
                } else {
                    this.showAlert(data.message, 'error');
                }
            } catch (e) {
                this.showAlert('Error deleting', 'error');
            }
        },

        async toggleStatus(id) {
            try {
                const response = await fetch(`/admin/promo-codes/${id}/toggle`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                if (data.success) {
                    this.showAlert(data.message, 'success');
                    window.location.reload();
                }
            } catch (e) {
                this.showAlert('Error toggling status', 'error');
            }
        },

        showAlert(msg, type) {
            // Use existing global toast system if available, else alert
            if (typeof toastNotifications !== 'undefined') {
                type === 'success' ? toastNotifications.success(msg) : toastNotifications.error(msg);
            } else {
                alert(msg);
            }
        }
    }));
});
