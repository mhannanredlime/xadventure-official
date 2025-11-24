/**
 * Multiple Image Upload Handler
 * Provides drag & drop, preview, and management functionality for multiple image uploads
 * Matches the existing design style with side-by-side image boxes
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
        // Use a longer delay to ensure all DOM elements are properly created
        setTimeout(() => {
            this.loadExistingImages();
        }, 300);
    }

    loadExistingImages() {
        // First try to load from data attribute (server-side)
        const existingImagesData = this.container.dataset.existingImages;

        if (existingImagesData) {
            try {
                const images = JSON.parse(existingImagesData);
                if (images && images.length > 0) {
                    // Add existing images to the additional images grid
                    images.forEach(image => {
                        this.addExistingImage(image);
                    });

                    // Check for primary image and update main preview
                    const primaryImage = images.find(img => img.is_primary);
                    if (primaryImage) {
                        // Add a longer delay to ensure DOM is ready and all elements are created
                        setTimeout(() => {
                            this.updateMainPreviewWithExistingImage(primaryImage);
                        }, 500);
                    } else if (images.length > 0) {
                        // Add a longer delay to ensure DOM is ready and all elements are created
                        setTimeout(() => {
                            this.updateMainPreviewWithExistingImage(images[0]);
                        }, 500);
                    }
                    return;
                }
            } catch (error) {
                // Error parsing existing images data
            }
        }

        // Fallback to AJAX if no data attribute
        this.loadExistingImagesFromServer();
    }

    loadExistingImagesFromServer() {
        // Get model type and ID from data attributes
        const modelType = this.container.dataset.modelType;
        const modelId = this.container.dataset.modelId;

        if (modelType && modelId && this.urls.images) {
            // Fetch existing images from server
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

                        // If no primary image is set, use the first image as main preview
                        const primaryImage = data.images.find(img => img.is_primary);
                        if (!primaryImage && data.images.length > 0) {
                            this.updateMainPreviewWithExistingImage(data.images[0]);
                        }
                    }
                })
                .catch(error => {
                    // Error loading existing images
                });
        }
    }

    createUploadArea() {
        this.container.innerHTML = `
            <div class="multiple-image-upload-area">
                <div class="row g-3">
                    <div class="col-md-6">
                        <h6 class="mb-3">Main Image</h6>
                        <div class="image-preview-box" style="position: relative; border: 2px dashed #ccc; border-radius: 8px; overflow: hidden; background-color: #f8f9fa;">
                            <div id="main-preview-${this.container.id}" style="width: 100%; display: flex; align-items: center; justify-content: center; color: #6c757d; font-size: 16px;">
                                <div style="text-align: center;">
                                    <i class="fa fa-image fa-4x mb-3"></i>
                                    <p>No image selected</p>
                                </div>
                            </div>
                            <input type="file" id="file-input-${this.container.id}" multiple accept="image/*" style="display: none;">
                            <div class="image-upload-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.3); cursor: pointer; display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s; z-index: 1;">
                                <div style="text-align: center; color: white;">
                                    <i class="fa fa-images fa-3x mb-2"></i>
                                    <p style="margin: 0; font-size: 14px;">Click to browse gallery</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="mb-3">Additional Images</h6>
                        <div class="additional-images-grid" id="additional-images-${this.container.id}" style="display: flex; flex-wrap: wrap; gap: 10px;">
                            <div class="add-more-box" onclick="document.getElementById('file-input-${this.container.id}').click()" style="width: 300px; height: 300px; border: 2px dashed #ccc; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; background-color: #f8f9fa;">
                                <i class="fa fa-plus fa-2x text-muted"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        this.mainPreview = document.getElementById(`main-preview-${this.container.id}`);
        this.fileInput = document.getElementById(`file-input-${this.container.id}`);
        this.additionalImagesGrid = document.getElementById(`additional-images-${this.container.id}`);
    }

    bindEvents() {
        // File input change
        this.fileInput.addEventListener('change', (e) => {
            this.handleFiles(e.target.files);
        });

        // Prevent file input from being triggered by clicks
        this.fileInput.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            this.openGalleryModal();
        });

        // Click to open gallery modal instead of file input
        this.mainPreview.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            this.openGalleryModal();
        });

        // Also handle overlay clicks
        const overlay = this.mainPreview.parentNode.querySelector('.image-upload-overlay');
        if (overlay) {
            overlay.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                this.openGalleryModal();
            });
        }

        // Prevent any clicks on the main preview container from triggering file input
        const mainPreviewContainer = this.mainPreview.parentNode;
        if (mainPreviewContainer) {
            mainPreviewContainer.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                this.openGalleryModal();
            });
        }

        // Hover effects for main preview
        this.mainPreview.addEventListener('mouseenter', () => {
            const overlay = this.mainPreview.parentNode.querySelector('.image-upload-overlay');
            if (overlay) overlay.style.opacity = '1';
        });

        this.mainPreview.addEventListener('mouseleave', () => {
            const overlay = this.mainPreview.parentNode.querySelector('.image-upload-overlay');
            if (overlay) overlay.style.opacity = '0';
        });
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
        // Check file type
        if (!this.options.acceptedTypes.includes(file.type)) {
            this.showError(`File type ${file.type} is not supported.`);
            return false;
        }

        // Check file size
        if (file.size > this.options.maxFileSize) {
            this.showError(`File ${file.name} is too large. Maximum size is ${this.options.maxFileSize / (1024 * 1024)}MB.`);
            return false;
        }

        // Check total files limit
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
        // Add gallery image to files array
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
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;

            // Insert before the add-more box
            const addMoreBox = this.additionalImagesGrid.querySelector('.add-more-box');
            this.additionalImagesGrid.insertBefore(
                this.createElementFromHTML(previewHtml),
                addMoreBox
            );

            // Bind remove event
            const removeBtn = document.querySelector(`#${previewId} .remove-image`);
            removeBtn.addEventListener('click', () => {
                this.removeFile(file.name);
            });

            // Hover effects
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
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Insert before the add-more box
        const addMoreBox = this.additionalImagesGrid.querySelector('.add-more-box');
        this.additionalImagesGrid.insertBefore(
            this.createElementFromHTML(previewHtml),
            addMoreBox
        );

        // Bind remove event
        const removeBtn = document.querySelector(`#${previewId} .remove-image`);
        removeBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.removeGalleryImage(galleryImage.name, galleryImage.galleryId);
        });

        // Hover effects
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
                this.mainPreview.innerHTML = `<img src="${e.target.result}" alt="Main preview" style="width: 100%; height: 100%; object-fit: cover;">`;
            };
            reader.readAsDataURL(this.files[0]);
        }
    }

    updateMainPreviewWithGalleryImage() {
        if (this.files.length > 0) {
            const firstFile = this.files[0];
            if (firstFile.isGalleryImage) {
                // For gallery images, use the URL directly
                this.mainPreview.innerHTML = `<img src="${firstFile.url}" alt="Main preview" style="width: 100%; height: 100%; object-fit: cover;">`;
            } else {
                // For regular files, use FileReader
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.mainPreview.innerHTML = `<img src="${e.target.result}" alt="Main preview" style="width: 100%; height: 100%; object-fit: cover;">`;
                };
                reader.readAsDataURL(firstFile);
            }
        }
    }

    removeFile(fileName) {
        // Remove from files array
        this.files = this.files.filter(file => file.name !== fileName);

        // Remove the specific preview element
        const previewElement = document.querySelector(`[data-file="${fileName}"]`);
        if (previewElement) {
            const imageItem = previewElement.closest('.additional-image-item');
            if (imageItem) {
                imageItem.remove();
            }
        }

        // Update main preview only if we removed the first file
        if (this.files.length > 0) {
            this.updateMainPreviewWithGalleryImage();
        } else {
            // Reset main preview to default state
            this.mainPreview.innerHTML = `
                <div style="text-align: center; color: #6c757d; font-size: 16px;">
                    <i class="fa fa-image fa-4x mb-3"></i>
                    <p>No image selected</p>
                </div>
            `;
        }
    }

    removeGalleryImage(fileName, galleryId) {
        // Remove from files array
        this.files = this.files.filter(file => file.name !== fileName);

        // Remove the specific preview element
        const previewElement = document.querySelector(`[data-file="${fileName}"]`);
        if (previewElement) {
            const imageItem = previewElement.closest('.additional-image-item');
            if (imageItem) {
                imageItem.remove();
            }
        }

        // Update main preview only if we removed the first file
        if (this.files.length > 0) {
            this.updateMainPreviewWithGalleryImage();
        } else {
            // Reset main preview to default state
            this.mainPreview.innerHTML = `
                <div style="text-align: center; color: #6c757d; font-size: 16px;">
                    <i class="fa fa-image fa-4x mb-3"></i>
                    <p>No image selected</p>
                </div>
            `;
        }
    }

    updateAllPreviews() {
        // Clear all previews
        const additionalItems = this.additionalImagesGrid.querySelectorAll('.additional-image-item');
        additionalItems.forEach(item => item.remove());

        // Recreate previews
        this.files.forEach(file => {
            this.createPreview(file);
        });

        // Update main preview
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
                            ${image.is_primary ? '<span class="badge bg-primary" style="font-size: 12px; padding: 4px 8px;">Primary</span>' :
                '<button type="button" class="btn btn-sm btn-outline-primary set-primary" data-image-id="' + image.id + '" style="font-size: 12px; padding: 4px 8px;">Set Primary</button>'}
                            <button type="button" class="btn btn-sm btn-outline-danger delete-image" data-image-id="${image.id}" style="font-size: 12px; padding: 4px 8px;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Insert before the add-more box
        const addMoreBox = this.additionalImagesGrid.querySelector('.add-more-box');
        this.additionalImagesGrid.insertBefore(
            this.createElementFromHTML(imageHtml),
            addMoreBox
        );

        // Update main preview if this is the primary image
        if (image.is_primary) {
            setTimeout(() => {
                this.updateMainPreviewWithExistingImage(image);
            }, 200);
        }

        // Bind events
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

        // Hover effects
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
        // Try to find the main preview element if it's not already set
        if (!this.mainPreview) {
            this.mainPreview = document.getElementById(`main-preview-${this.container.id}`);
        }

        if (this.mainPreview) {

            // Clear the main preview area first
            this.mainPreview.innerHTML = '';

            // Create a new image element
            const img = document.createElement('img');
            img.src = image.url;
            img.alt = image.alt_text || 'Main preview';
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.objectFit = 'cover';
            img.style.display = 'block';
            img.style.position = 'relative';
            img.style.zIndex = '2';

            // Add error handling for image loading
            img.onerror = () => {
                this.mainPreview.innerHTML = `
                    <div style="text-align: center; color: #6c757d; font-size: 16px; padding: 20px;">
                        <i class="fa fa-image fa-4x mb-3"></i>
                        <p>Image failed to load</p>
                        <small>URL: ${image.url}</small>
                    </div>
                `;
            };

            // Append the image to the main preview area
            this.mainPreview.appendChild(img);

            // Also update the main preview area style to ensure it's visible
            this.mainPreview.style.display = 'block';
            this.mainPreview.style.position = 'absolute';
            this.mainPreview.style.top = '0';
            this.mainPreview.style.zIndex = '1';
        } else {
            // Try alternative selectors
            const alternativeSelectors = [
                `#main-preview-${this.container.id}`,
                '.image-preview-box div[id*="main-preview"]',
                '.image-preview-box > div:first-child'
            ];

            for (const selector of alternativeSelectors) {
                const element = document.querySelector(selector);
                if (element) {
                    this.mainPreview = element;
                    this.updateMainPreviewWithExistingImage(image);
                    return;
                }
            }

            // If still not found, try to find it by looking for the container and then the preview
            const container = document.getElementById(this.container.id);
            if (container) {
                const previewBox = container.querySelector('.image-preview-box');
                if (previewBox) {
                    const previewDiv = previewBox.querySelector('div[id*="main-preview"]');
                    if (previewDiv) {
                        this.mainPreview = previewDiv;
                        this.updateMainPreviewWithExistingImage(image);
                        return;
                    }
                }
            }
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
                    location.reload(); // Refresh to update UI
                } else {
                    this.showError(data.message || 'Failed to update primary image.');
                }
            })
            .catch(error => {
                this.showError('An error occurred while updating primary image.');
            });
    }

    deleteImage(imageId) {
        console.log('deleteImage called with imageId:', imageId);

        // Prevent multiple calls for the same image
        if (this.deletingImages && this.deletingImages.has(imageId)) {
            console.log('Already deleting image:', imageId);
            return;
        }

        if (!this.deletingImages) {
            this.deletingImages = new Set();
        }
        this.deletingImages.add(imageId);

        // Use native confirm for now to test if the issue is with the modal system
        if (confirm('Are you sure you want to delete this image? This action cannot be undone.')) {
            console.log('Native confirm confirmed, calling performDeleteImage with:', imageId);
            this.performDeleteImage(imageId);
        } else {
            // Remove from deleting set if cancelled
            this.deletingImages.delete(imageId);
        }
    }

    performDeleteImage(imageId) {
        const url = this.urls.delete.replace(':id', imageId);
        console.log('performDeleteImage called with:', { imageId, url, deleteUrl: this.urls.delete });

        fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            }
        })
            .then(response => {
                console.log('Delete response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Delete response data:', data);
                if (data.success) {
                    this.showSuccess('Image deleted successfully.');
                    const imageElement = document.querySelector(`#existing-${imageId}`);
                    console.log('Looking for image element with selector:', `#existing-${imageId}`, 'Found:', imageElement);
                    if (imageElement) {
                        // Check if this was the primary image
                        const isPrimary = imageElement.querySelector('.badge.bg-primary');
                        console.log('Is primary image:', !!isPrimary);
                        imageElement.remove();

                        // If this was the primary image, update main preview
                        if (isPrimary) {
                            this.updateMainPreviewAfterDeletion();
                        }
                    } else {
                        console.warn('Image element not found for deletion');
                    }
                } else {
                    this.showError(data.message || 'Failed to delete image.');
                }
            })
            .catch(error => {
                console.error('Delete error:', error);
                this.showError('An error occurred while deleting image: ' + error.message);
            })
            .finally(() => {
                // Remove from deleting set
                if (this.deletingImages) {
                    this.deletingImages.delete(imageId);
                }
            });
    }

    updateMainPreviewAfterDeletion() {
        // Find the next available image to set as primary
        const remainingImages = this.additionalImagesGrid.querySelectorAll('.additional-image-item img');
        if (remainingImages.length > 0) {
            // Use the first remaining image
            const firstImage = remainingImages[0];
            const imageData = {
                url: firstImage.src,
                alt_text: firstImage.alt || 'Main preview',
                is_primary: true
            };
            this.updateMainPreviewWithExistingImage(imageData);
        } else {
            // No images left, reset to default state
            this.mainPreview.innerHTML = `
                <div style="text-align: center; color: #6c757d; font-size: 16px;">
                    <i class="fa fa-image fa-4x mb-3"></i>
                    <p>No image selected</p>
                </div>
            `;
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
        // You can customize this to show success messages
        if (typeof toastNotifications !== 'undefined') {
            toastNotifications.success(message);
        } else if (typeof toastr !== 'undefined') {
            toastr.success(message);
        } else {
            console.log('Success:', message);
        }
    }

    showError(message) {
        // You can customize this to show error messages
        if (typeof toastNotifications !== 'undefined') {
            toastNotifications.error(message);
        } else if (typeof toastr !== 'undefined') {
            toastr.error(message);
        } else {
            console.error('Error:', message);
        }
    }

    // Test method to manually update main preview
    testMainPreviewUpdate(imageUrl) {
        const testImage = {
            url: imageUrl,
            alt_text: 'Test Image',
            is_primary: true
        };
        this.updateMainPreviewWithExistingImage(testImage);
    }

    // Force update main preview with any available image
    forceUpdateMainPreview() {

        // First try to find a primary image
        const primaryImageElement = document.querySelector('.additional-image-item .badge.bg-primary');
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

        // If no primary image, use the first available image
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

    // Open gallery modal for image selection
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

    // Handle gallery image selection
    handleGallerySelection(selectedImages) {
        selectedImages.forEach(image => {
            // Create a mock file object for gallery images
            const mockFile = {
                name: image.alt || 'gallery-image.jpg',
                size: 0,
                type: 'image/jpeg',
                lastModified: Date.now(),
                isGalleryImage: true,
                galleryId: image.id,
                url: image.url
            };

            // Add to the upload component
            this.addGalleryImage(mockFile);
        });
    }
}

// Initialize multiple image upload for all containers with class 'multiple-image-upload'
document.addEventListener('DOMContentLoaded', function () {
    const containers = document.querySelectorAll('#multiple-image-upload');

    containers.forEach((container, index) => {
        const instance = new MultipleImageUpload(container.id, {
            maxFiles: parseInt(container.dataset.maxFiles) || 10,
            maxFileSize: parseInt(container.dataset.maxFileSize) || 2 * 1024 * 1024
        });

        // Store instance globally for form submission and testing
        window.multipleImageUploadInstance = instance;
        window[`multipleImageUploadInstance_${index}`] = instance;

        // Add a final fallback to ensure main preview is updated
        setTimeout(() => {
            if (instance && typeof instance.forceUpdateMainPreview === 'function') {
                instance.forceUpdateMainPreview();
            }
        }, 1000);
    });
});
