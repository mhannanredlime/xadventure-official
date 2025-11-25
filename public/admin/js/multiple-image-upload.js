/**
 * Multiple Image Upload Handler
 * Drag & drop, preview, and management functionality for multiple image uploads
 * Automatically marks first image as Main Image
 * Hover overlay/border removed
 */
class MultipleImageUpload {
    constructor(containerId, options = {}) {
        this.container = document.getElementById(containerId);

        this.options = {
            maxFiles: options.maxFiles || 10,
            maxFileSize: options.maxFileSize || 5 * 1024 * 1024, // 5MB
            acceptedTypes: options.acceptedTypes || [
                'image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp', 'image/bmp', 'image/svg+xml',
                'image/tiff', 'image/avif', 'image/heic', 'image/heif'
            ],
            uploadUrl: options.uploadUrl || null,
            ...options
        };

        this.urls = {
            images: this.container.dataset.imagesUrl,
            primary: this.container.dataset.primaryUrl,
            altText: this.container.dataset.altTextUrl,
            delete: this.container.dataset.deleteUrl
        };

        this.files = [];
        this.uploadedImages = [];
        this.deletingImages = new Set();

        this.init();
    }

    init() {
        this.createUploadArea();
        setTimeout(() => this.loadExistingImages(), 300);
    }

    // Load images from data-existing-images or server
    loadExistingImages() {
        const existingImagesData = this.container.dataset.existingImages;
        if (existingImagesData) {
            try {
                const images = JSON.parse(existingImagesData);
                if (images.length > 0) {
                    images.forEach(image => this.addExistingImage(image));
                    return;
                }
            } catch (err) {
                console.error('Error parsing existing images:', err);
            }
        }
        this.loadExistingImagesFromServer();
    }

    loadExistingImagesFromServer() {
        const modelType = this.container.dataset.modelType;
        const modelId = this.container.dataset.modelId;
        if (modelType && modelId && this.urls.images) {
            fetch(`${this.urls.images}?model_type=${encodeURIComponent(modelType)}&model_id=${modelId}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
                .then(res => res.ok ? res.json() : Promise.reject(`HTTP ${res.status}`))
                .then(data => {
                    if (data.success && data.images) {
                        data.images.forEach(img => this.addExistingImage(img));
                    }
                })
                .catch(err => console.error('Error loading existing images:', err));
        }
    }

    createUploadArea() {
        this.container.innerHTML = `
        <div class="multiple-image-upload-area">
            <div class="mb-3">
                <button type="button" class="btn jatio-bg-color btn-sm upload-images-btn">
                    <i class="bi bi-upload me-1"></i>Upload Images
                </button>
                <button type="button" class="btn btn-outline-primary btn-sm gallery-images-btn">
                    <i class="bi bi-images me-1"></i>Choose from Gallery
                </button>
            </div>
            <div class="d-flex flex-wrap gap-2 additional-images-grid" id="additional-images-${this.container.id}">
                <div class="image-card add-more-card d-none" style="width: 300px; height: 300px;">
                    <div class="card h-100 border-dashed d-flex align-items-center justify-content-center">
                        <i class="bi bi-image fa-3x text-secondary"></i>
                    </div>
                </div>
            </div>
            <input type="file" id="file-input-${this.container.id}" multiple accept="image/*" style="display: none;">
        </div>
    `;

        this.fileInput = document.getElementById(`file-input-${this.container.id}`);
        this.additionalImagesGrid = document.getElementById(`additional-images-${this.container.id}`);

        // Upload button
        this.container.querySelector('.upload-images-btn').addEventListener('click', e => {
            console.log('Upload button clicked');
            e.preventDefault();
            this.fileInput.click();
        });

        // Gallery button with debugging
        const galleryBtn = this.container.querySelector('.gallery-images-btn');
        galleryBtn.addEventListener('click', e => {
            console.log('Gallery button clicked');
            console.log('this.openGalleryModal exists:', typeof this.openGalleryModal);
            console.log('global openGalleryModal exists:', typeof openGalleryModal);
            e.preventDefault();
            this.openGalleryModal();
        });

        this.fileInput.addEventListener('change', e => {
            console.log('File input changed, files:', e.target.files.length);
            this.handleFiles(e.target.files);
        });
    }

    // Make sure openGalleryModal method exists in your class
    openGalleryModal() {
        console.log('openGalleryModal method called');

        if (typeof openGalleryModal === 'function') {
            console.log('Calling global openGalleryModal function');
            openGalleryModal({
                multiple: true,
                onSelect: (selectedImages) => {
                    console.log('Gallery selection:', selectedImages);
                    this.handleGallerySelection(selectedImages);
                }
            });
        } else {
            console.error('Global openGalleryModal function not found!');
            // Fallback: show file input instead
            this.fileInput.click();
        }
    }

    handleFiles(fileList) {
        Array.from(fileList).forEach(file => {
            if (this.validateFile(file)) this.addFile(file);
        });
    }

    validateFile(file) {
        if (!this.options.acceptedTypes.includes(file.type)) { this.showError(`File type ${file.type} is not supported.`); return false; }
        if (file.size > this.options.maxFileSize) { this.showError(`File ${file.name} exceeds max size.`); return false; }
        if (this.files.length >= this.options.maxFiles) { this.showError(`Maximum ${this.options.maxFiles} files allowed.`); return false; }
        return true;
    }

    addFile(file) {
        this.files.push(file);
        this.createPreview(file);
    }

    createPreview(file) {
        const reader = new FileReader();
        reader.onload = e => {
            const previewId = `preview-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
            const previewHtml = `
                <div class="card additional-image-item position-relative" id="${previewId}" data-file="${file.name}" style="width: 300px; height: 300px;">
                    <img src="${e.target.result}" class="card-img-top object-fit-cover" style="height: 100%;" alt="${file.name}">
                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 remove-image">
                        <i class="bi bi-x"></i>
                    </button>
                    <div class="position-absolute bottom-0 start-0 m-2 badge-container"></div>
                </div>
            `;
            const addMoreCard = this.additionalImagesGrid.querySelector('.add-more-card');
            const element = this.createElementFromHTML(previewHtml);
            this.additionalImagesGrid.insertBefore(element, addMoreCard);

            // Remove image event
            element.querySelector('.remove-image').addEventListener('click', () => this.removeFile(file.name));

            this.updateAllBadges();
        };
        reader.readAsDataURL(file);
    }

    addExistingImage(image) {
        const imageId = `existing-${image.id}`;
        const imageHtml = `
            <div class="card additional-image-item position-relative" id="${imageId}" style="width: 300px; height: 300px;">
                <img src="${image.url}" class="card-img-top object-fit-cover" style="height: 100%;" alt="${image.alt_text || 'Image'}">
                <button type="button" class="btn btn-sm btn-outline-danger position-absolute top-0 end-0 m-2 delete-image">
                    <i class="bi bi-x"></i>
                </button>
                <div class="position-absolute bottom-0 start-0 m-2 badge-container"></div>
            </div>
        `;
        const addMoreCard = this.additionalImagesGrid.querySelector('.add-more-card');
        const element = this.createElementFromHTML(imageHtml);
        this.additionalImagesGrid.insertBefore(element, addMoreCard);

        element.querySelector('.delete-image').addEventListener('click', () => this.deleteImage(image.id));

        this.updateAllBadges();
    }

    updateAllBadges() {
        const items = this.additionalImagesGrid.querySelectorAll('.additional-image-item');
        items.forEach((item, index) => {
            const badgeContainer = item.querySelector('.badge-container');
            badgeContainer.innerHTML = index === 0 ? '<span class="badge jatio-bg-color">Main Image</span>' : '';
        });
    }

    removeFile(fileName) {
        this.files = this.files.filter(f => f.name !== fileName);
        const previewElement = this.additionalImagesGrid.querySelector(`[data-file="${fileName}"]`);
        if (previewElement) previewElement.remove();
        this.updateAllBadges();
    }

    deleteImage(imageId) {
        if (this.deletingImages.has(imageId)) return;
        this.deletingImages.add(imageId);

        if (!confirm('Are you sure you want to delete this image?')) {
            this.deletingImages.delete(imageId);
            return;
        }

        const url = this.urls.delete.replace(':id', imageId);
        fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
            .then(res => res.ok ? res.json() : Promise.reject(res.status))
            .then(data => {
                if (data.success) {
                    const el = document.getElementById(`existing-${imageId}`);
                    if (el) el.remove();
                    this.updateAllBadges();
                } else this.showError(data.message || 'Failed to delete image');
            })
            .catch(err => this.showError('Error deleting image: ' + err))
            .finally(() => this.deletingImages.delete(imageId));
    }

    createElementFromHTML(html) {
        const div = document.createElement('div');
        div.innerHTML = html.trim();
        return div.firstChild;
    }

    openGalleryModal() {
        if (typeof openGalleryModal === 'function') {
            openGalleryModal({ multiple: true, onSelect: selectedImages => this.handleGallerySelection(selectedImages) });
        }
    }

    handleGallerySelection(selectedImages) {
        selectedImages.forEach(image => {
            const mockFile = {
                name: image.alt || 'gallery-image.jpg',
                size: 0,
                type: 'image/jpeg',
                lastModified: Date.now(),
                isGalleryImage: true,
                galleryId: image.id,
                url: image.url
            };
            this.addFile(mockFile);
        });
    }

    showSuccess(msg) { console.log('Success:', msg); }
    showError(msg) { console.error('Error:', msg); }

    getSelectedFiles() {
        return this.files;
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    const containers = document.querySelectorAll('#multiple-image-upload');
    containers.forEach((container, i) => {
        const instance = new MultipleImageUpload(container.id, {
            maxFiles: parseInt(container.dataset.maxFiles) || 10,
            maxFileSize: parseInt(container.dataset.maxFileSize) || 2 * 1024 * 1024
        });
        window[`multipleImageUploadInstance_${i}`] = instance;
    });
});
