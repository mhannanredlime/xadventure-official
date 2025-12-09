/**
 * Multiple Image Upload Handler
 * Supports file upload, preview, and deletion
 * Automatically marks first image as Main Image
 */
class MultipleImageUpload {
    constructor(containerId, options = {}) {
        this.container = document.getElementById(containerId);

        this.options = {
            maxFiles: options.maxFiles || 10,
            maxFileSize: options.maxFileSize || 5 * 1024 * 1024, // 5MB
            acceptedTypes: options.acceptedTypes || [
                'image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'
            ],
            uploadUrl: options.uploadUrl || null,
            ...options
        };

        this.urls = {
            images: this.container.dataset.imagesUrl,
            delete: this.container.dataset.deleteUrl
        };

        this.files = [];
        this.deletingImages = new Set();

        // Attach instance to container for external access
        this.container._instance = this;

        this.init();
    }

    init() {
        this.createUploadArea();
        setTimeout(() => this.loadExistingImages(), 300);
    }

    loadExistingImages() {
        const existingImagesData = this.container.dataset.existingImages;
        if (existingImagesData) {
            try {
                const images = JSON.parse(existingImagesData);
                images.forEach(img => this.addExistingImage(img));
                return;
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
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json'
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
                    <button type="button" class="btn btn-save jatio-bg-color upload-images-btn">
                        <i class="bi bi-upload me-1"></i> Upload Images
                    </button>
                </div>
                <div class="d-flex flex-wrap gap-2 additional-images-grid" id="additional-images-${this.container.id}">
                    <div class="image-card add-more-card d-none" style="width: 300px; height: 300px;">
                        <div class="card h-100 border-dashed d-flex align-items-center justify-content-center">
                            <i class="bi bi-image fa-3x text-secondary"></i>
                        </div>
                    </div>
                </div>
                <input type="file" id="file-input-${this.container.id}" multiple accept="image/*" style="display:none;">
            </div>
        `;

        this.fileInput = document.getElementById(`file-input-${this.container.id}`);
        this.additionalImagesGrid = document.getElementById(`additional-images-${this.container.id}`);

        // Upload button click
        const uploadBtn = this.container.querySelector('.upload-images-btn');
        uploadBtn.addEventListener('click', e => {
            e.preventDefault();
            this.fileInput.click();
        });

        // File input change
        this.fileInput.addEventListener('change', e => this.handleFiles(e.target.files));
    }

    handleFiles(fileList) {
        Array.from(fileList).forEach(file => {
            if (this.validateFile(file)) this.addFile(file);
        });
    }

    validateFile(file) {
        if (!this.options.acceptedTypes.includes(file.type)) {
            this.showError(`File type ${file.type} is not supported.`);
            return false;
        }
        if (file.size > this.options.maxFileSize) {
            this.showError(`File ${file.name} exceeds max size.`);
            return false;
        }
        if (this.files.length >= this.options.maxFiles) {
            this.showError(`Maximum ${this.options.maxFiles} files allowed.`);
            return false;
        }
        return true;
    }

    addFile(file) {
        this.files.push(file);
        const reader = new FileReader();
        reader.onload = e => this.createPreview(file, e.target.result);
        reader.readAsDataURL(file);
    }

    createPreview(file, src) {
        const previewId = `preview-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
        const previewHtml = `
            <div class="card additional-image-item position-relative" id="${previewId}" data-file="${file.name}" style="width:300px;height:300px;">
                <img src="${src}" class="card-img-top object-fit-cover" style="height:100%;" alt="${file.name}">
                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 remove-image">
                    <i class="bi bi-x"></i>
                </button>
                <div class="position-absolute bottom-0 start-0 m-2 badge-container"></div>
            </div>
        `;
        const addMoreCard = this.additionalImagesGrid.querySelector('.add-more-card');
        const element = this.createElementFromHTML(previewHtml);
        this.additionalImagesGrid.insertBefore(element, addMoreCard);

        element.querySelector('.remove-image').addEventListener('click', () => this.removeFile(file.name));

        this.updateAllBadges();
    }

    addExistingImage(image) {
        const imageId = `existing-${image.id}`;
        const imageHtml = `
            <div class="card additional-image-item position-relative" id="${imageId}" style="width:300px;height:300px;">
                <img src="${image.url}" class="card-img-top object-fit-cover" style="height:100%;" alt="${image.alt_text || 'Image'}">
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

    showSuccess(msg) { console.log('Success:', msg); }
    showError(msg) { console.error('Error:', msg); }

    getSelectedFiles() { return this.files; }
}

// Initialize all containers
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('#multiple-image-upload').forEach((container, i) => {
        window[`multipleImageUploadInstance_${i}`] = new MultipleImageUpload(container.id, {
            maxFiles: parseInt(container.dataset.maxFiles) || 10,
            maxFileSize: parseInt(container.dataset.maxFileSize) || 2 * 1024 * 1024
        });
    });
});
