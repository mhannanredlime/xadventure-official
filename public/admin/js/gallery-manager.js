/**
 * Gallery Manager - Handles gallery page functionality and modal
 */
class GalleryManager {
    constructor() {
        this.selectedImages = new Set();
        this.currentPage = 1;
        this.hasMorePages = false;
        this.isLoading = false;
        this.searchTimeout = null;
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.initializeUpload();
    }

    bindEvents() {
        // Upload modal events
        const uploadModal = document.getElementById('uploadModal');
        if (uploadModal) {
            this.initializeUploadModal();
        }

        // Image view modal events
        const imageModal = document.getElementById('imageModal');
        if (imageModal) {
            this.initializeImageModal();
        }

        // Edit modal events
        const editModal = document.getElementById('editModal');
        if (editModal) {
            this.initializeEditModal();
        }

        // Gallery actions
        this.bindGalleryActions();
    }

    initializeUploadModal() {
        const fileInput = document.getElementById('fileInput');
        const uploadArea = document.getElementById('uploadArea');
        const uploadBtn = document.getElementById('uploadBtn');
        const previewContainer = document.getElementById('previewContainer');
        const uploadPreview = document.getElementById('uploadPreview');

        // File input change
        fileInput.addEventListener('change', (e) => {
            this.handleFileSelection(e.target.files, previewContainer, uploadPreview, uploadBtn);
        });

        // Drag and drop
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            this.handleFileSelection(e.dataTransfer.files, previewContainer, uploadPreview, uploadBtn);
        });

        // Click to select files
        uploadArea.addEventListener('click', () => {
            fileInput.click();
        });

        // Upload button
        uploadBtn.addEventListener('click', () => {
            this.uploadFiles(fileInput.files);
        });
    }

    initializeImageModal() {
        const imageModal = document.getElementById('imageModal');
        const modalImage = document.getElementById('modalImage');
        const imageDetails = document.getElementById('imageDetails');
        const modalEditBtn = document.getElementById('modalEditBtn');
        const modalDeleteBtn = document.getElementById('modalDeleteBtn');

        // View image buttons
        document.addEventListener('click', (e) => {
            if (e.target.closest('.view-image') || e.target.closest('.gallery-image')) {
                const imageId = e.target.closest('[data-image-id]').dataset.imageId;
                this.viewImage(imageId);
            }
        });

        // Edit button
        modalEditBtn.addEventListener('click', () => {
            const imageId = modalImage.dataset.imageId;
            this.editImage(imageId);
        });

        // Delete button
        modalDeleteBtn.addEventListener('click', () => {
            const imageId = modalImage.dataset.imageId;
            this.deleteImage(imageId);
        });
    }

    initializeEditModal() {
        const editForm = document.getElementById('editForm');
        const editModal = document.getElementById('editModal');

        editForm.addEventListener('submit', (e) => {
            e.preventDefault();
            this.updateImage();
        });

        // Reset form when modal is hidden
        editModal.addEventListener('hidden.bs.modal', () => {
            editForm.reset();
        });
    }

    bindGalleryActions() {
        // Edit image buttons
        document.addEventListener('click', (e) => {
            if (e.target.closest('.edit-image')) {
                const imageId = e.target.closest('[data-image-id]').dataset.imageId;
                this.editImage(imageId);
            }
        });

        // Delete image buttons
        document.addEventListener('click', (e) => {
            if (e.target.closest('.delete-image')) {
                const imageId = e.target.closest('[data-image-id]').dataset.imageId;
                this.deleteImage(imageId);
            }
        });
    }

    handleFileSelection(files, previewContainer, uploadPreview, uploadBtn) {
        if (files.length === 0) return;

        // Clear previous previews
        previewContainer.innerHTML = '';
        uploadPreview.style.display = 'block';

        Array.from(files).forEach((file, index) => {
            if (this.validateFile(file)) {
                this.createFilePreview(file, previewContainer, index);
            }
        });

        uploadBtn.disabled = false;
    }

    validateFile(file) {
        const maxSize = 5 * 1024 * 1024; // 5MB
        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp', 'image/bmp', 'image/svg+xml'];

        if (!allowedTypes.includes(file.type)) {
            this.showError(`File type ${file.type} is not supported.`);
            return false;
        }

        if (file.size > maxSize) {
            this.showError(`File ${file.name} is too large. Maximum size is 5MB.`);
            return false;
        }

        return true;
    }

    createFilePreview(file, container, index) {
        const reader = new FileReader();
        reader.onload = (e) => {
            const col = document.createElement('div');
            col.className = 'col-md-3 col-sm-4 col-6';
            col.innerHTML = `
                <div class="position-relative">
                    <img src="${e.target.result}" alt="${file.name}" class="img-fluid rounded">
                    <button type="button" class="remove-preview" data-index="${index}">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            `;
            container.appendChild(col);

            // Remove button
            col.querySelector('.remove-preview').addEventListener('click', () => {
                col.remove();
            });
        };
        reader.readAsDataURL(file);
    }

    async uploadFiles(files) {
        const uploadBtn = document.getElementById('uploadBtn');
        const spinner = uploadBtn.querySelector('.spinner-border');
        
        uploadBtn.disabled = true;
        spinner.classList.remove('d-none');

        const formData = new FormData();
        Array.from(files).forEach(file => {
            formData.append('images[]', file);
        });

        try {
            const response = await fetch('/admin/gallery/upload', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.showSuccess(data.message);
                // Close modal and reload page
                const modal = bootstrap.Modal.getInstance(document.getElementById('uploadModal'));
                modal.hide();
                location.reload();
            } else {
                this.showError(data.message || 'Upload failed');
            }
        } catch (error) {
            this.showError('An error occurred during upload');
        } finally {
            uploadBtn.disabled = false;
            spinner.classList.add('d-none');
        }
    }

    async viewImage(imageId) {
        try {
            const response = await fetch(`/admin/gallery/${imageId}`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                }
            });

            const data = await response.json();

            if (data.success) {
                const image = data.image;
                const modalImage = document.getElementById('modalImage');
                const imageDetails = document.getElementById('imageDetails');
                const modalEditBtn = document.getElementById('modalEditBtn');
                const modalDeleteBtn = document.getElementById('modalDeleteBtn');

                modalImage.src = image.url;
                modalImage.alt = image.alt_text;
                modalImage.dataset.imageId = image.id;

                imageDetails.innerHTML = `
                    <div class="image-details">
                        <h6>File Information</h6>
                        <p><strong>Name:</strong> ${image.original_name}</p>
                        <p><strong>Size:</strong> ${this.formatFileSize(image.file_size)}</p>
                        <p><strong>Type:</strong> ${image.mime_type}</p>
                        <p><strong>Uploaded:</strong> ${new Date(image.created_at).toLocaleDateString()}</p>
                        <p><strong>By:</strong> ${image.uploader ? image.uploader.name : 'Unknown'}</p>
                        ${image.alt_text ? `<p><strong>Alt Text:</strong> ${image.alt_text}</p>` : ''}
                        ${image.tags && image.tags.length > 0 ? `
                            <p><strong>Tags:</strong> 
                                ${image.tags.map(tag => `<span class="badge bg-secondary me-1">${tag}</span>`).join('')}
                            </p>
                        ` : ''}
                    </div>
                `;

                modalEditBtn.dataset.imageId = image.id;
                modalDeleteBtn.dataset.imageId = image.id;

                const modal = new bootstrap.Modal(document.getElementById('imageModal'));
                modal.show();
            } else {
                this.showError(data.message || 'Failed to load image details');
            }
        } catch (error) {
            this.showError('An error occurred while loading image details');
        }
    }

    async editImage(imageId) {
        try {
            const response = await fetch(`/admin/gallery/${imageId}`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                }
            });

            const data = await response.json();

            if (data.success) {
                const image = data.image;
                const editForm = document.getElementById('editForm');
                const editAltText = document.getElementById('editAltText');
                const editTags = document.getElementById('editTags');

                editAltText.value = image.alt_text || '';
                editTags.value = image.tags ? image.tags.join(', ') : '';
                editForm.dataset.imageId = image.id;

                const modal = new bootstrap.Modal(document.getElementById('editModal'));
                modal.show();
            } else {
                this.showError(data.message || 'Failed to load image details');
            }
        } catch (error) {
            this.showError('An error occurred while loading image details');
        }
    }

    async updateImage() {
        const editForm = document.getElementById('editForm');
        const imageId = editForm.dataset.imageId;
        const submitBtn = editForm.querySelector('button[type="submit"]');
        const spinner = submitBtn.querySelector('.spinner-border');

        submitBtn.disabled = true;
        spinner.classList.remove('d-none');

        const formData = new FormData(editForm);
        const tags = formData.get('tags') ? formData.get('tags').split(',').map(tag => tag.trim()).filter(tag => tag) : [];

        try {
            const response = await fetch(`/admin/gallery/${imageId}`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    alt_text: formData.get('alt_text'),
                    tags: tags
                })
            });

            const data = await response.json();

            if (data.success) {
                this.showSuccess('Image updated successfully');
                const modal = bootstrap.Modal.getInstance(document.getElementById('editModal'));
                modal.hide();
                location.reload();
            } else {
                this.showError(data.message || 'Failed to update image');
            }
        } catch (error) {
            this.showError('An error occurred while updating image');
        } finally {
            submitBtn.disabled = false;
            spinner.classList.add('d-none');
        }
    }

    async deleteImage(imageId) {
        if (!confirm('Are you sure you want to delete this image? This action cannot be undone.')) {
            return;
        }

        try {
            const response = await fetch(`/admin/gallery/${imageId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                }
            });

            const data = await response.json();

            if (data.success) {
                this.showSuccess('Image deleted successfully');
                // Remove image from DOM
                const imageElement = document.querySelector(`[data-image-id="${imageId}"]`);
                if (imageElement) {
                    imageElement.closest('.col-lg-3, .col-md-4, .col-sm-6').remove();
                }
                // Close any open modals
                const modals = document.querySelectorAll('.modal.show');
                modals.forEach(modal => {
                    const bsModal = bootstrap.Modal.getInstance(modal);
                    if (bsModal) bsModal.hide();
                });
            } else {
                this.showError(data.message || 'Failed to delete image');
            }
        } catch (error) {
            this.showError('An error occurred while deleting image');
        }
    }

    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    showSuccess(message) {
        // Clear any existing error messages first
        this.clearMessages();
        
        if (typeof toastNotifications !== 'undefined') {
            toastNotifications.success(message);
        } else if (typeof toastr !== 'undefined') {
            toastr.success(message);
        } else {
            alert(message);
        }
    }

    clearMessages() {
        // Clear any existing toast notifications
        if (typeof toastr !== 'undefined') {
            toastr.clear();
        }
        
        // Clear any existing error/success messages in the modal
        const existingAlerts = document.querySelectorAll('.alert');
        existingAlerts.forEach(alert => {
            if (alert.parentNode) {
                alert.parentNode.removeChild(alert);
            }
        });
    }

    showError(message) {
        if (typeof toastNotifications !== 'undefined') {
            toastNotifications.error(message);
        } else if (typeof toastr !== 'undefined') {
            toastr.error(message);
        } else {
            alert(message);
        }
    }
}

/**
 * Gallery Modal - Handles gallery selection modal functionality
 */
class GalleryModal {
    constructor(options = {}) {
        this.options = {
            multiple: options.multiple || false,
            onSelect: options.onSelect || null,
            ...options
        };
        
        this.selectedImages = new Set();
        this.currentPage = 1;
        this.hasMorePages = false;
        this.isLoading = false;
        
        this.init();
    }

    init() {
        this.bindEvents();
    }

    bindEvents() {
        // Search functionality
        const searchInput = document.getElementById('gallerySearch');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                clearTimeout(this.searchTimeout);
                this.searchTimeout = setTimeout(() => {
                    this.searchImages();
                }, 500);
            });
        }

        // Search button
        const searchBtn = document.getElementById('searchGallery');
        if (searchBtn) {
            searchBtn.addEventListener('click', () => {
                this.searchImages();
            });
        }

        // Load more button
        const loadMoreBtn = document.getElementById('loadMoreBtn');
        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', () => {
                this.loadMoreImages();
            });
        }

        // Insert selected button
        const insertBtn = document.getElementById('insertSelectedBtn');
        if (insertBtn) {
            insertBtn.addEventListener('click', () => {
                this.insertSelectedImages();
            });
        }

        // Upload functionality
        this.initializeUpload();
    }

    initializeUpload() {
        const fileInput = document.getElementById('galleryFileInput');
        const uploadArea = document.getElementById('galleryUploadArea');
        const previewContainer = document.getElementById('galleryPreviewContainer');
        const uploadPreview = document.getElementById('galleryUploadPreview');
        // Upload button removed - upload is now automatic

        if (!fileInput || !uploadArea) return;

        // File input change
        fileInput.addEventListener('change', (e) => {
            this.handleFileSelection(e.target.files, previewContainer, uploadPreview);
        });

        // Drag and drop
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            this.handleFileSelection(e.dataTransfer.files, previewContainer, uploadPreview);
        });

        // Click to select files
        uploadArea.addEventListener('click', () => {
            fileInput.click();
        });

        // Upload is now automatic, no button needed
    }

    async searchImages() {
        const search = document.getElementById('gallerySearch').value;
        const tags = document.getElementById('galleryTags').value;
        
        this.currentPage = 1;
        this.selectedImages.clear();
        this.updateSelectedSummary();
        
        await this.loadImages(search, tags);
    }

    async loadImages(search = '', tags = '', page = 1) {
        if (this.isLoading) return;
        
        this.isLoading = true;
        const loadMoreBtn = document.getElementById('loadMoreBtn');
        if (loadMoreBtn) {
            const spinner = loadMoreBtn.querySelector('.spinner-border');
            spinner.classList.remove('d-none');
            loadMoreBtn.disabled = true;
        }

        try {
            const params = new URLSearchParams({
                search: search,
                tags: tags,
                per_page: 12,
                page: page
            });

            const response = await fetch(`/admin/gallery/images?${params}`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                }
            });

            const data = await response.json();

            if (data.success) {
                if (page === 1) {
                    this.renderImages(data.images);
                } else {
                    this.appendImages(data.images);
                }
                
                this.hasMorePages = data.pagination.has_more;
                this.updateLoadMoreButton();
            } else {
                this.showError(data.message || 'Failed to load images');
            }
        } catch (error) {
            this.showError('An error occurred while loading images');
        } finally {
            this.isLoading = false;
            if (loadMoreBtn) {
                const spinner = loadMoreBtn.querySelector('.spinner-border');
                spinner.classList.add('d-none');
                loadMoreBtn.disabled = false;
            }
        }
    }

    renderImages(images) {
        const grid = document.getElementById('galleryGrid');
        if (!grid) return;

        grid.innerHTML = '';

        images.forEach(image => {
            const imageElement = this.createImageElement(image);
            grid.appendChild(imageElement);
        });
    }

    appendImages(images) {
        const grid = document.getElementById('galleryGrid');
        if (!grid) return;

        images.forEach(image => {
            const imageElement = this.createImageElement(image);
            grid.appendChild(imageElement);
        });
    }

    createImageElement(image) {
        const div = document.createElement('div');
        div.className = 'gallery-modal-item';
        div.dataset.imageId = image.id;
        
        div.innerHTML = `
            <img src="${image.thumbnail_url}" alt="${image.alt_text || image.original_name}">
            <div class="gallery-modal-overlay">
                <i class="bi bi-eye"></i>
            </div>
            <div class="gallery-modal-check">
                <i class="bi bi-check"></i>
            </div>
            <div class="gallery-modal-actions">
                <button type="button" class="btn btn-danger btn-sm delete-image-btn" data-image-id="${image.id}" title="Delete Image">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;

        // Click to select/deselect (but not on delete button)
        div.addEventListener('click', (e) => {
            if (!e.target.closest('.delete-image-btn')) {
                this.toggleImageSelection(image.id, div);
            }
        });

        // Delete button click handler
        const deleteBtn = div.querySelector('.delete-image-btn');
        deleteBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.deleteImage(image.id, image.original_name);
        });

        return div;
    }

    toggleImageSelection(imageId, element) {
        if (this.selectedImages.has(imageId)) {
            this.selectedImages.delete(imageId);
            element.classList.remove('selected');
        } else {
            if (!this.options.multiple && this.selectedImages.size > 0) {
                // Clear previous selection for single select mode
                this.selectedImages.clear();
                document.querySelectorAll('.gallery-modal-item.selected').forEach(el => {
                    el.classList.remove('selected');
                });
            }
            this.selectedImages.add(imageId);
            element.classList.add('selected');
        }

        this.updateSelectedSummary();
    }

    updateSelectedSummary() {
        const summary = document.getElementById('selectedImagesSummary');
        const insertBtn = document.getElementById('insertSelectedBtn');
        
        if (this.selectedImages.size > 0) {
            summary.style.display = 'block';
            insertBtn.disabled = false;
            
            const list = document.getElementById('selectedImagesList');
            list.innerHTML = '';
            
            this.selectedImages.forEach(imageId => {
                const imageElement = document.querySelector(`[data-image-id="${imageId}"]`);
                if (imageElement) {
                    const img = imageElement.querySelector('img');
                    const div = document.createElement('div');
                    div.className = 'selected-image-item';
                    div.innerHTML = `
                        <img src="${img.src}" alt="${img.alt}">
                        <span>${img.alt}</span>
                    `;
                    list.appendChild(div);
                }
            });
        } else {
            summary.style.display = 'none';
            insertBtn.disabled = true;
        }
    }

    updateLoadMoreButton() {
        const container = document.getElementById('loadMoreContainer');
        if (container) {
            container.style.display = this.hasMorePages ? 'block' : 'none';
        }
    }

    async loadMoreImages() {
        if (!this.hasMorePages || this.isLoading) return;
        
        this.currentPage++;
        const search = document.getElementById('gallerySearch').value;
        const tags = document.getElementById('galleryTags').value;
        
        await this.loadImages(search, tags, this.currentPage);
    }

    insertSelectedImages() {
        if (this.selectedImages.size === 0) {
            return;
        }

        const selectedImagesData = [];
        this.selectedImages.forEach(imageId => {
            const imageElement = document.querySelector(`[data-image-id="${imageId}"]`);
            if (imageElement) {
                const img = imageElement.querySelector('img');
                selectedImagesData.push({
                    id: imageId,
                    url: img.src,
                    alt: img.alt
                });
            }
        });

        if (this.options.onSelect) {
            this.options.onSelect(selectedImagesData);
        }

        // Close modal after a short delay to ensure callback is processed
        setTimeout(() => {
            this.closeModal();
        }, 100);
    }

    closeModal() {
        try {
            // Method 1: Try to get existing modal instance
            const modal = bootstrap.Modal.getInstance(document.getElementById('galleryModal'));
            if (modal) {
                modal.hide();
                this.resetSelection();
                return;
            }

            // Method 2: Create new modal instance and hide
            const modalElement = document.getElementById('galleryModal');
            if (modalElement) {
                const bsModal = new bootstrap.Modal(modalElement);
                bsModal.hide();
                this.resetSelection();
                return;
            }

            // Method 3: Fallback - trigger close event
            const closeEvent = new Event('hide.bs.modal');
            modalElement.dispatchEvent(closeEvent);
            this.resetSelection();
            
        } catch (error) {
            // Final fallback - remove modal backdrop and hide element
            const modalElement = document.getElementById('galleryModal');
            if (modalElement) {
                modalElement.style.display = 'none';
                modalElement.classList.remove('show');
                document.body.classList.remove('modal-open');
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.remove();
                }
                this.resetSelection();
            }
        }
    }

    resetSelection() {
        // Clear selected images
        this.selectedImages.clear();
        
        // Remove selection indicators from UI
        document.querySelectorAll('.gallery-modal-item.selected').forEach(item => {
            item.classList.remove('selected');
        });
        
        // Hide selected images summary
        const summary = document.getElementById('selectedImagesSummary');
        if (summary) {
            summary.style.display = 'none';
        }
        
        // Disable insert button
        const insertBtn = document.getElementById('insertSelectedBtn');
        if (insertBtn) {
            insertBtn.disabled = true;
        }
    }

    handleFileSelection(files, previewContainer, uploadPreview) {
        if (files.length === 0) return;

        previewContainer.innerHTML = '';
        uploadPreview.style.display = 'block';

        // Store files for upload
        this.selectedFiles = files;

        // Show uploading message
        const uploadingDiv = document.createElement('div');
        uploadingDiv.className = 'col-12 text-center';
        uploadingDiv.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Uploading...</span></div><p class="mt-2">Uploading images to gallery...</p>';
        previewContainer.appendChild(uploadingDiv);

        // Automatically upload files
        this.uploadSelectedFiles();
    }

    validateFile(file) {
        const maxSize = 5 * 1024 * 1024; // 5MB
        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp', 'image/bmp', 'image/svg+xml'];

        if (!allowedTypes.includes(file.type)) {
            this.showError(`File type ${file.type} is not supported.`);
            return false;
        }

        if (file.size > maxSize) {
            this.showError(`File ${file.name} is too large. Maximum size is 5MB.`);
            return false;
        }

        return true;
    }

    createFilePreview(file, container, index) {
        const reader = new FileReader();
        reader.onload = (e) => {
            const col = document.createElement('div');
            col.className = 'col-md-3 col-sm-4 col-6';
            col.innerHTML = `
                <div class="position-relative">
                    <img src="${e.target.result}" alt="${file.name}" class="img-fluid rounded">
                    <button type="button" class="remove-preview" data-index="${index}">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            `;
            container.appendChild(col);

            // Remove button
            col.querySelector('.remove-preview').addEventListener('click', () => {
                col.remove();
            });
        };
        reader.readAsDataURL(file);
    }

    showError(message) {
        if (typeof toastNotifications !== 'undefined') {
            toastNotifications.error(message);
        } else if (typeof toastr !== 'undefined') {
            toastr.error(message);
        } else {
            alert(message);
        }
    }

    clearMessages() {
        // Clear any existing toast notifications
        if (typeof toastr !== 'undefined') {
            toastr.clear();
        }
        
        // Clear any existing error/success messages in the modal
        const existingAlerts = document.querySelectorAll('.alert');
        existingAlerts.forEach(alert => {
            if (alert.parentNode) {
                alert.parentNode.removeChild(alert);
            }
        });
    }

    showSimpleSuccess(message) {
        // Create a simple success notification
        const successDiv = document.createElement('div');
        successDiv.className = 'alert alert-success alert-dismissible fade show';
        successDiv.style.position = 'fixed';
        successDiv.style.top = '20px';
        successDiv.style.right = '20px';
        successDiv.style.zIndex = '9999';
        successDiv.innerHTML = `
            <i class="bi bi-check-circle"></i> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(successDiv);
        
        // Auto-remove after 3 seconds
        setTimeout(() => {
            if (successDiv.parentNode) {
                successDiv.parentNode.removeChild(successDiv);
            }
        }, 3000);
    }

    showSimpleError(message) {
        // Create a simple error notification
        const errorDiv = document.createElement('div');
        errorDiv.className = 'alert alert-danger alert-dismissible fade show';
        errorDiv.style.position = 'fixed';
        errorDiv.style.top = '20px';
        errorDiv.style.right = '20px';
        errorDiv.style.zIndex = '9999';
        errorDiv.innerHTML = `
            <i class="bi bi-exclamation-triangle"></i> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(errorDiv);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (errorDiv.parentNode) {
                errorDiv.parentNode.removeChild(errorDiv);
            }
        }, 5000);
    }

    async deleteImage(imageId, imageName) {
        if (!confirm(`Are you sure you want to delete "${imageName}"? This action cannot be undone.`)) {
            return;
        }

        try {
            const response = await fetch(`/admin/gallery/${imageId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                }
            });

            const data = await response.json();

            if (data.success) {
                this.showSimpleSuccess('Image deleted successfully!');
                
                // Remove image from selection if it was selected
                this.selectedImages.delete(imageId);
                this.updateSelectedSummary();
                
                // Remove image element from DOM
                const imageElement = document.querySelector(`[data-image-id="${imageId}"]`);
                if (imageElement) {
                    imageElement.remove();
                }
                
                // Refresh gallery to update the view
                this.searchImages();
            } else {
                this.showSimpleError(data.message || 'Failed to delete image');
            }
        } catch (error) {
            this.showSimpleError('An error occurred while deleting the image');
        }
    }
}

// Add uploadSelectedFiles method to GalleryModal prototype
GalleryModal.prototype.uploadSelectedFiles = async function() {
    if (!this.selectedFiles || this.selectedFiles.length === 0) {
        this.showError('No files selected for upload.');
        return;
    }

    const formData = new FormData();
    Array.from(this.selectedFiles).forEach(file => {
        formData.append('images[]', file);
    });

        try {
            const response = await fetch('/admin/gallery/upload', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: formData
            });

            // Check if response is ok first
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

        if (data.success === true) {
            // Clear any existing error messages first
            if (typeof this.clearMessages === 'function') {
                this.clearMessages();
            }
            
            // Show success message using simple notification
            if (typeof this.showSimpleSuccess === 'function') {
                this.showSimpleSuccess(data.message || 'Images uploaded successfully!');
            } else {
                alert('Images uploaded successfully!');
            }
            
            // Clear selected files and preview
            this.selectedFiles = null;
            document.getElementById('galleryFileInput').value = '';
            document.getElementById('galleryUploadPreview').style.display = 'none';
            document.getElementById('galleryPreviewContainer').innerHTML = '';
            
            // Refresh gallery to show newly uploaded images
            if (typeof this.searchImages === 'function') {
                this.searchImages();
            }
        } else {
            if (typeof this.showSimpleError === 'function') {
                this.showSimpleError(data.message || 'Upload failed');
            } else {
                alert('Upload failed: ' + (data.message || 'Unknown error'));
            }
            // Hide upload preview on error
            document.getElementById('galleryUploadPreview').style.display = 'none';
        }
        } catch (error) {
            if (typeof this.showSimpleError === 'function') {
                this.showSimpleError('An error occurred during upload: ' + error.message);
            } else {
                alert('An error occurred during upload: ' + error.message);
            }
            // Hide upload preview on error
            document.getElementById('galleryUploadPreview').style.display = 'none';
        }
};

// Global function to open gallery modal
function openGalleryModal(options = {}) {
    const modal = new bootstrap.Modal(document.getElementById('galleryModal'));
    
    // Initialize gallery modal if not already done
    if (!window.galleryModalInstance) {
        window.galleryModalInstance = new GalleryModal(options);
    } else {
        // Update options
        window.galleryModalInstance.options = { ...window.galleryModalInstance.options, ...options };
        // Re-bind events for the modal
        window.galleryModalInstance.bindEvents();
    }
    
    // Load images when modal is shown
    document.getElementById('galleryModal').addEventListener('shown.bs.modal', () => {
        window.galleryModalInstance.searchImages();
    }, { once: true });
    
    modal.show();
}

// Make classes available globally
window.GalleryManager = GalleryManager;
window.GalleryModal = GalleryModal;
window.openGalleryModal = openGalleryModal;
