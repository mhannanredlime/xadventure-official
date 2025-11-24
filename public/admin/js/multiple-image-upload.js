/**
 * Multiple Image Upload Handler
 * Provides drag & drop, preview, and management functionality for multiple image uploads
 * Enhanced UI with professional main image section using Bootstrap Icons
 */
class MultipleImageUpload {
    constructor(containerId, options = {}) {
        this.container = document.getElementById(containerId);

        this.options = {
            maxFiles: options.maxFiles || 10,
            maxFileSize: options.maxFileSize || 5 * 1024 * 1024, // 5MB
            acceptedTypes: options.acceptedTypes || [
                'image/jpeg',
                'image/png',
                'image/jpg',
                'image/gif',
                'image/webp',
                'image/bmp',
                'image/svg+xml',
                'image/tiff',
                'image/avif',
                'image/heic',
                'image/heif'
            ],
            uploadUrl: options.uploadUrl || null,
            ...options
        };

        // Get URLs from data attributes
        this.urls = {
            images: this.container.dataset.imagesUrl,
            primary: this.container.dataset.primaryUrl,
            reorder: this.container.dataset.reorderUrl,
            altText: this.container.dataset.altTextUrl,
            delete: this.container.dataset.deleteUrl
        };

        this.files = [];
        this.uploadedImages = [];
        this.init();
    }

    init() {
        this.createUploadArea();
        this.bindEvents();
        // Load existing images after DOM elements are created
        setTimeout(() => {
            this.loadExistingImages();
        }, 300);
    }

    loadExistingImages() {
        const existingImagesData = this.container.dataset.existingImages;

        if (existingImagesData) {
            try {
                const images = JSON.parse(existingImagesData);
                if (images && images.length > 0) {
                    images.forEach(image => {
                        this.addExistingImage(image);
                    });

                    const primaryImage = images.find(img => img.is_primary);
                    if (primaryImage) {
                        setTimeout(() => {
                            this.updateMainPreviewWithExistingImage(primaryImage);
                        }, 500);
                    } else if (images.length > 0) {
                        setTimeout(() => {
                            this.updateMainPreviewWithExistingImage(images[0]);
                        }, 500);
                    }
                    return;
                }
            } catch (error) {
                console.error('Error parsing existing images:', error);
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
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.images) {
                        data.images.forEach(image => {
                            this.addExistingImage(image);
                        });

                        const primaryImage = data.images.find(img => img.is_primary);
                        if (!primaryImage && data.images.length > 0) {
                            this.updateMainPreviewWithExistingImage(data.images[0]);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error loading existing images:', error);
                });
        }
    }

    createUploadArea() {
        this.container.innerHTML = `
            <div class="multiple-image-upload-area">
                <div class="row g-4">
                    <!-- Enhanced Main Image Section -->
                    <div class="col-12">
                        <div class="main-image-section">
                            <h5 class="section-title mb-3">
                                <i class="bi bi-star-fill text-warning me-2"></i>
                                Main Image
                                <span class="badge jatio-bg-color ms-2">Primary Display</span>
                            </h5>
                            <div class="main-image-container">
                                <div class="main-image-preview-card">
                                    <div class="card border-primary">
                                        <div class="card-header bg-light py-2">
                                            <small class="text-muted">
                                                <i class="bi bi-info-circle me-1"></i>
                                                This image will be featured as the main display
                                            </small>
                                        </div>
                                        <div class="card-body p-0">
                                            <div id="main-preview-${this.container.id}" 
                                                 class="main-preview-area"
                                                 style="min-height: 280px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); cursor: pointer;">
                                                <div class="empty-state text-center p-4">
                                                    <div class="empty-icon mb-3">
                                                        <i class="bi bi-camera fa-4x text-muted"></i>
                                                    </div>
                                                    <h6 class="text-muted mb-2">No Main Image Selected</h6>
                                                    <p class="text-muted small mb-3">This will be your primary display image</p>
                                                    <button type="button" class="btn jatio-bg-color btn-sm">
                                                        <i class="bi bi-upload me-1"></i>Select Main Image
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer bg-light">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                                    <i class="bi bi-shield-check me-1"></i>
                                                    Recommended: 1200×800px
                                                </small>
                                                <div class="image-actions">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm me-2 change-main-image">
                                                        <i class="bi bi-arrow-repeat me-1"></i>Change
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger btn-sm remove-main-image">
                                                        <i class="bi bi-x me-1"></i>Remove
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Images Section -->
                    <div class="col-12">
                        <div class="additional-images-section">
                            <h5 class="section-title mb-3">
                                <i class="bi bi-layers text-info me-2"></i>
                                Additional Images
                                <span class="badge bg-secondary ms-2">Optional</span>
                            </h5>
                            
                            <div class="upload-actions mb-3">
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="button" class="btn jatio-bg-color btn-sm upload-images-btn">
                                        <i class="bi bi-upload me-1"></i>Upload Images
                                    </button>
                                    <button type="button" class="btn btn-outline-primary btn-sm gallery-images-btn">
                                        <i class="bi bi-images me-1"></i>Choose from Gallery
                                    </button>
                                    <button type="button" class="btn btn-outline-info btn-sm reorder-images-btn">
                                        <i class="bi bi-sort-down me-1"></i>Reorder Images
                                    </button>
                                </div>
                            </div>

                            <div class="additional-images-grid-container">
                                <div id="additional-images-${this.container.id}" class="additional-images-grid">
                                    <!-- Add More Box -->
                                    <div class="image-card add-more-card" style="width: 300px; height: 300px;">
                                        <div class="card h-100 border-dashed">
                                            <div class="card-body d-flex flex-column align-items-center justify-content-center text-center">
                                                <div class="add-icon mb-2">
                                                    <i class="bi bi-plus-circle fa-3x jatio-text-color"></i>
                                                </div>
                                                <h6 class="jatio-text-color mb-1">Add Images</h6>
                                                <p class="text-muted small mb-0">Click to upload or select from gallery</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="image-upload-info mt-3">
                                <div class="alert alert-light border d-flex align-items-center">
                                    <i class="bi bi-info-circle jatio-text-color me-2 fa-lg"></i>
                                    <div>
                                        <small class="text-muted">
                                            <strong>Supported formats:</strong> JPG, PNG, WebP • 
                                            <strong>Max size:</strong> 5MB per image • 
                                            <strong>Recommended:</strong> Square or landscape orientation
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hidden File Input -->
                <input type="file" id="file-input-${this.container.id}" multiple accept="image/*" style="display: none;">
            </div>
        `;

        this.mainPreview = document.getElementById(`main-preview-${this.container.id}`);
        this.fileInput = document.getElementById(`file-input-${this.container.id}`);
        this.additionalImagesGrid = document.getElementById(`additional-images-${this.container.id}`);

        this.initializeUploadAreaEvents();
    }

    initializeUploadAreaEvents() {
        // Main preview click event
        this.mainPreview.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.openGalleryModal();
        });

        // Change main image button
        const changeBtn = this.mainPreview.closest('.main-image-preview-card').querySelector('.change-main-image');
        changeBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.openGalleryModal();
        });

        // Remove main image button
        const removeBtn = this.mainPreview.closest('.main-image-preview-card').querySelector('.remove-main-image');
        removeBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.clearMainImage();
        });

        // Upload images button
        const uploadBtn = this.container.querySelector('.upload-images-btn');
        uploadBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.fileInput.click();
        });

        // Gallery images button
        const galleryBtn = this.container.querySelector('.gallery-images-btn');
        galleryBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.openGalleryModal();
        });

        // Reorder images button
        const reorderBtn = this.container.querySelector('.reorder-images-btn');
        reorderBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.reorderImages();
        });

        // Add more card click
        const addMoreCard = this.additionalImagesGrid.querySelector('.add-more-card');
        addMoreCard.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.openGalleryModal();
        });

        // File input change
        this.fileInput.addEventListener('change', (e) => {
            this.handleFiles(e.target.files);
        });

        // Hover effects for main preview
        this.mainPreview.addEventListener('mouseenter', () => {
            if (!this.mainPreview.querySelector('img')) {
                this.mainPreview.style.background = 'linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%)';
            }
        });

        this.mainPreview.addEventListener('mouseleave', () => {
            if (!this.mainPreview.querySelector('img')) {
                this.mainPreview.style.background = 'linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%)';
            }
        });
    }

    bindEvents() {
        // Additional event bindings if needed
    }

    handleFiles(fileList) {
        const files = Array.from(fileList);
        files.forEach(file => {
            if (this.validateFile(file)) {
                this.addFile(file);
            }
        });
    }

    validateFile(file) {
        if (!this.options.acceptedTypes.includes(file.type)) {
            this.showError(`File type ${file.type} is not supported.`);
            return false;
        }

        if (file.size > this.options.maxFileSize) {
            this.showError(`File ${file.name} is too large. Maximum size is ${this.options.maxFileSize / (1024 * 1024)}MB.`);
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
        this.createPreview(file);
        this.updateMainPreview();
    }

    addGalleryImage(galleryImage) {
        this.files.push(galleryImage);
        this.createGalleryPreview(galleryImage);
        this.updateMainPreviewWithGalleryImage();
    }

    createPreview(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            const previewId = `preview-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;

            const previewHtml = `
                <div class="additional-image-item" id="${previewId}" style="position: relative; margin-bottom: 10px; margin-right: 10px; display: inline-block;">
                    <div style="width: 300px; height: 300px; border-radius: 8px; overflow: hidden; position: relative;">
                        <img src="${e.target.result}" alt="${file.name}" style="width: 100%; height: 100%; object-fit: cover;">
                        <div class="image-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s;">
                            <button type="button" class="btn btn-sm btn-danger remove-image" data-file="${file.name}" style="border-radius: 50%; width: 30px; height: 30px; padding: 0;">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;

            const addMoreCard = this.additionalImagesGrid.querySelector('.add-more-card');
            this.additionalImagesGrid.insertBefore(this.createElementFromHTML(previewHtml), addMoreCard);

            const removeBtn = document.querySelector(`#${previewId} .remove-image`);
            removeBtn.addEventListener('click', () => {
                this.removeFile(file.name);
            });

            const imageItem = document.querySelector(`#${previewId}`);
            imageItem.addEventListener('mouseenter', () => {
                const overlay = imageItem.querySelector('.image-overlay');
                if (overlay) overlay.style.opacity = '1';
            });

            imageItem.addEventListener('mouseleave', () => {
                const overlay = imageItem.querySelector('.image-overlay');
                if (overlay) overlay.style.opacity = '0';
            });
        };
        reader.readAsDataURL(file);
    }

    createGalleryPreview(galleryImage) {
        const previewId = `gallery-preview-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;

        const previewHtml = `
            <div class="additional-image-item" id="${previewId}" style="position: relative; margin-bottom: 10px; margin-right: 10px; display: inline-block;">
                <div style="width: 300px; height: 300px; border-radius: 8px; overflow: hidden; position: relative;">
                    <img src="${galleryImage.url}" alt="${galleryImage.name}" style="width: 100%; height: 100%; object-fit: cover;">
                    <div class="image-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s;">
                        <div class="btn-group btn-group-sm">
                            <span class="badge bg-info" style="font-size: 12px; padding: 4px 8px;">From Gallery</span>
                            <button type="button" class="btn btn-sm btn-danger remove-image" data-file="${galleryImage.name}" data-gallery-id="${galleryImage.galleryId || ''}" style="font-size: 12px; padding: 4px 8px;">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        const addMoreCard = this.additionalImagesGrid.querySelector('.add-more-card');
        this.additionalImagesGrid.insertBefore(this.createElementFromHTML(previewHtml), addMoreCard);

        const removeBtn = document.querySelector(`#${previewId} .remove-image`);
        removeBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.removeGalleryImage(galleryImage.name, galleryImage.galleryId);
        });

        const imageItem = document.querySelector(`#${previewId}`);
        imageItem.addEventListener('mouseenter', () => {
            const overlay = imageItem.querySelector('.image-overlay');
            if (overlay) overlay.style.opacity = '1';
        });

        imageItem.addEventListener('mouseleave', () => {
            const overlay = imageItem.querySelector('.image-overlay');
            if (overlay) overlay.style.opacity = '0';
        });
    }

    createElementFromHTML(htmlString) {
        const div = document.createElement('div');
        div.innerHTML = htmlString.trim();
        return div.firstChild;
    }

    updateMainPreview() {
        if (this.files.length > 0) {
            const reader = new FileReader();
            reader.onload = (e) => {
                this.updateMainPreviewWithImage(e.target.result);
            };
            reader.readAsDataURL(this.files[0]);
        }
    }

    updateMainPreviewWithGalleryImage() {
        if (this.files.length > 0) {
            const firstFile = this.files[0];
            if (firstFile.isGalleryImage) {
                this.updateMainPreviewWithImage(firstFile.url);
            } else {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.updateMainPreviewWithImage(e.target.result);
                };
                reader.readAsDataURL(firstFile);
            }
        }
    }

    updateMainPreviewWithImage(imageUrl) {
        this.mainPreview.innerHTML = `
            <div class="main-image-loaded">
                <img src="${imageUrl}" alt="Main package image" 
                     class="main-image-display" 
                     style="width: 100%; height: 280px; object-fit: cover; border-radius: 4px;">
                <div class="main-image-overlay">
                    <div class="overlay-content">
                        <span class="badge jatio-bg-color mb-2">
                            <i class="bi bi-check me-1"></i>Main Image
                        </span>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-light change-main-image">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-light remove-main-image">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Re-bind events for the new buttons
        const changeBtn = this.mainPreview.querySelector('.change-main-image');
        const removeBtn = this.mainPreview.querySelector('.remove-main-image');

        changeBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.openGalleryModal();
        });

        removeBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.clearMainImage();
        });
    }

    clearMainImage() {
        this.mainPreview.innerHTML = `
            <div class="empty-state text-center p-4">
                <div class="empty-icon mb-3">
                    <i class="bi bi-camera fa-4x text-muted"></i>
                </div>
                <h6 class="text-muted mb-2">No Main Image Selected</h6>
                <p class="text-muted small mb-3">This will be your primary display image</p>
                <button type="button" class="btn jatio-bg-color btn-sm">
                    <i class="bi bi-upload me-1"></i>Select Main Image
                </button>
            </div>
        `;
        this.mainPreview.style.background = 'linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%)';

        // Re-bind the click event
        const selectBtn = this.mainPreview.querySelector('button');
        selectBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.openGalleryModal();
        });
    }

    removeFile(fileName) {
        this.files = this.files.filter(file => file.name !== fileName);
        const previewElement = document.querySelector(`[data-file="${fileName}"]`);
        if (previewElement) {
            const imageItem = previewElement.closest('.additional-image-item');
            if (imageItem) {
                imageItem.remove();
            }
        }

        if (this.files.length > 0) {
            this.updateMainPreviewWithGalleryImage();
        } else {
            this.clearMainImage();
        }
    }

    removeGalleryImage(fileName, galleryId) {
        this.files = this.files.filter(file => file.name !== fileName);
        const previewElement = document.querySelector(`[data-file="${fileName}"]`);
        if (previewElement) {
            const imageItem = previewElement.closest('.additional-image-item');
            if (imageItem) {
                imageItem.remove();
            }
        }

        if (this.files.length > 0) {
            this.updateMainPreviewWithGalleryImage();
        } else {
            this.clearMainImage();
        }
    }

    updateAllPreviews() {
        const additionalItems = this.additionalImagesGrid.querySelectorAll('.additional-image-item');
        additionalItems.forEach(item => item.remove());

        this.files.forEach(file => {
            this.createPreview(file);
        });

        this.updateMainPreview();
    }

    addExistingImage(image) {
        const imageId = `existing-${image.id}`;

        const imageHtml = `
            <div class="additional-image-item" id="${imageId}" style="position: relative; margin-bottom: 10px; margin-right: 10px; display: inline-block;">
                <div style="width: 300px; height: 300px; border-radius: 8px; overflow: hidden; position: relative;">
                    <img src="${image.url}" alt="${image.alt_text || 'Image'}" style="width: 100%; height: 100%; object-fit: cover;">
                    <div class="image-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s;">
                        <div class="btn-group btn-group-sm">
                            ${image.is_primary ? '<span class="badge jatio-bg-color" style="font-size: 12px; padding: 4px 8px;">Primary</span>' :
                '<button type="button" class="btn btn-sm jatio-bg-color set-primary" data-image-id="' + image.id + '" style="font-size: 12px; padding: 4px 8px;">Set Primary</button>'}
                            <button type="button" class="btn btn-sm btn-outline-danger delete-image" data-image-id="${image.id}" style="font-size: 12px; padding: 4px 8px;">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        const addMoreCard = this.additionalImagesGrid.querySelector('.add-more-card');
        this.additionalImagesGrid.insertBefore(this.createElementFromHTML(imageHtml), addMoreCard);

        if (image.is_primary) {
            setTimeout(() => {
                this.updateMainPreviewWithExistingImage(image);
            }, 200);
        }

        const setPrimaryBtn = document.querySelector(`#${imageId} .set-primary`);
        if (setPrimaryBtn) {
            setPrimaryBtn.addEventListener('click', () => {
                this.setPrimaryImage(image.id);
            });
        }

        const deleteBtn = document.querySelector(`#${imageId} .delete-image`);
        deleteBtn.addEventListener('click', () => {
            this.deleteImage(image.id);
        });

        const imageItem = document.querySelector(`#${imageId}`);
        imageItem.addEventListener('mouseenter', () => {
            const overlay = imageItem.querySelector('.image-overlay');
            if (overlay) overlay.style.opacity = '1';
        });

        imageItem.addEventListener('mouseleave', () => {
            const overlay = imageItem.querySelector('.image-overlay');
            if (overlay) overlay.style.opacity = '0';
        });
    }

    updateMainPreviewWithExistingImage(image) {
        if (!this.mainPreview) {
            this.mainPreview = document.getElementById(`main-preview-${this.container.id}`);
        }

        if (this.mainPreview) {
            this.mainPreview.innerHTML = '';
            const img = document.createElement('img');
            img.src = image.url;
            img.alt = image.alt_text || 'Main preview';
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.objectFit = 'cover';
            img.style.display = 'block';
            img.style.position = 'relative';
            img.style.zIndex = '2';

            img.onerror = () => {
                this.mainPreview.innerHTML = `
                    <div style="text-align: center; color: #6c757d; font-size: 16px; padding: 20px;">
                        <i class="bi bi-image fa-4x mb-3"></i>
                        <p>Image failed to load</p>
                        <small>URL: ${image.url}</small>
                    </div>
                `;
            };

            this.mainPreview.appendChild(img);
            this.mainPreview.style.display = 'block';
            this.mainPreview.style.position = 'absolute';
            this.mainPreview.style.top = '0';
            this.mainPreview.style.zIndex = '1';
        }
    }

    setPrimaryImage(imageId) {
        const url = this.urls.primary.replace(':id', imageId);
        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.showSuccess('Primary image updated successfully.');
                    location.reload();
                } else {
                    this.showError(data.message || 'Failed to update primary image.');
                }
            })
            .catch(error => {
                this.showError('An error occurred while updating primary image.');
            });
    }

    deleteImage(imageId) {
        if (this.deletingImages && this.deletingImages.has(imageId)) {
            return;
        }

        if (!this.deletingImages) {
            this.deletingImages = new Set();
        }
        this.deletingImages.add(imageId);

        if (confirm('Are you sure you want to delete this image? This action cannot be undone.')) {
            this.performDeleteImage(imageId);
        } else {
            this.deletingImages.delete(imageId);
        }
    }

    performDeleteImage(imageId) {
        const url = this.urls.delete.replace(':id', imageId);
        fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    this.showSuccess('Image deleted successfully.');
                    const imageElement = document.querySelector(`#existing-${imageId}`);
                    if (imageElement) {
                        const isPrimary = imageElement.querySelector('.badge.jatio-bg-color');
                        imageElement.remove();
                        if (isPrimary) {
                            this.updateMainPreviewAfterDeletion();
                        }
                    }
                } else {
                    this.showError(data.message || 'Failed to delete image.');
                }
            })
            .catch(error => {
                this.showError('An error occurred while deleting image: ' + error.message);
            })
            .finally(() => {
                if (this.deletingImages) {
                    this.deletingImages.delete(imageId);
                }
            });
    }

    updateMainPreviewAfterDeletion() {
        const remainingImages = this.additionalImagesGrid.querySelectorAll('.additional-image-item img');
        if (remainingImages.length > 0) {
            const firstImage = remainingImages[0];
            const imageData = {
                url: firstImage.src,
                alt_text: firstImage.alt || 'Main preview',
                is_primary: true
            };
            this.updateMainPreviewWithExistingImage(imageData);
        } else {
            this.clearMainImage();
        }
    }

    updateAltText(imageId, altText) {
        const url = this.urls.altText.replace(':id', imageId);
        fetch(url, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ alt_text: altText })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.showSuccess('Alt text updated successfully.');
                } else {
                    this.showError(data.message || 'Failed to update alt text.');
                }
            })
            .catch(error => {
                this.showError('An error occurred while updating alt text.');
            });
    }

    getFiles() {
        return this.files;
    }

    getSelectedFiles() {
        return this.files;
    }

    showSuccess(message) {
        if (typeof toastNotifications !== 'undefined') {
            toastNotifications.success(message);
        } else if (typeof toastr !== 'undefined') {
            toastr.success(message);
        } else {
            console.log('Success:', message);
        }
    }

    showError(message) {
        if (typeof toastNotifications !== 'undefined') {
            toastNotifications.error(message);
        } else if (typeof toastr !== 'undefined') {
            toastr.error(message);
        } else {
            console.error('Error:', message);
        }
    }

    testMainPreviewUpdate(imageUrl) {
        const testImage = {
            url: imageUrl,
            alt_text: 'Test Image',
            is_primary: true
        };
        this.updateMainPreviewWithExistingImage(testImage);
    }

    forceUpdateMainPreview() {
        const primaryImageElement = document.querySelector('.additional-image-item .badge.jatio-bg-color');
        if (primaryImageElement) {
            const imageContainer = primaryImageElement.closest('.additional-image-item');
            const img = imageContainer.querySelector('img');
            if (img) {
                const imageData = {
                    url: img.src,
                    alt_text: img.alt || 'Primary Image',
                    is_primary: true
                };
                this.updateMainPreviewWithExistingImage(imageData);
                return;
            }
        }

        const firstImageElement = document.querySelector('.additional-image-item img');
        if (firstImageElement) {
            const imageData = {
                url: firstImageElement.src,
                alt_text: firstImageElement.alt || 'First Image',
                is_primary: false
            };
            this.updateMainPreviewWithExistingImage(imageData);
        }
    }

    openGalleryModal() {
        if (typeof openGalleryModal === 'function') {
            openGalleryModal({
                multiple: true,
                onSelect: (selectedImages) => {
                    this.handleGallerySelection(selectedImages);
                }
            });
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

            this.addGalleryImage(mockFile);
        });
    }

    reorderImages() {
        // Implement reorder functionality
        this.showSuccess('Reorder functionality would be implemented here');
    }
}

// Initialize multiple image upload
document.addEventListener('DOMContentLoaded', function () {
    const containers = document.querySelectorAll('#multiple-image-upload');
    containers.forEach((container, index) => {
        const instance = new MultipleImageUpload(container.id, {
            maxFiles: parseInt(container.dataset.maxFiles) || 10,
            maxFileSize: parseInt(container.dataset.maxFileSize) || 2 * 1024 * 1024
        });

        window.multipleImageUploadInstance = instance;
        window[`multipleImageUploadInstance_${index}`] = instance;

        setTimeout(() => {
            if (instance && typeof instance.forceUpdateMainPreview === 'function') {
                instance.forceUpdateMainPreview();
            }
        }, 1000);
    });
});