<!-- Gallery Selection Modal -->
<div class="modal fade" id="galleryModal" tabindex="-1" aria-labelledby="galleryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="galleryModalLabel">Select Images from Gallery</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-2 p-md-3">
                <!-- Mobile-First Responsive Layout -->
                <div class="row g-2 g-md-3">
                    <!-- Upload Column - Full width on mobile, 1/3 on desktop -->
                    <div class="col-12 col-lg-4">
                        <div class="card h-100">
                            <div class="card-header bg-primary text-white py-2">
                                <h6 class="mb-0 text-truncate">
                                    <i class="bi bi-cloud-upload"></i> 
                                    <span class="d-none d-sm-inline">Upload New Images</span>
                                    <span class="d-inline d-sm-none">Upload</span>
                                </h6>
                            </div>
                            <div class="card-body p-2 p-md-3">
                                <div id="galleryUploadArea" class="upload-area">
                                    <div class="upload-content">
                                        <i class="bi bi-cloud-upload text-muted" style="font-size: 2rem;"></i>
                                        <h6 class="mt-2 mb-1">Drag & Drop Images</h6>
                                        <p class="text-muted small mb-2">or click to select files</p>
                                        <input type="file" id="galleryFileInput" multiple accept="image/*" style="display: none;">
                                        <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('galleryFileInput').click()">
                                            <i class="bi bi-plus-circle"></i> 
                                            <span class="d-none d-sm-inline">Choose Files</span>
                                            <span class="d-inline d-sm-none">Files</span>
                                        </button>
                                    </div>
                                </div>
                                <div id="galleryUploadPreview" class="upload-preview mt-2" style="display: none;">
                                    <h6 class="text-success small">
                                        <i class="bi bi-upload"></i> Uploading...
                                    </h6>
                                    <div id="galleryPreviewContainer" class="row g-1"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gallery Column - Full width on mobile, 2/3 on desktop -->
                    <div class="col-12 col-lg-8">
                        <div class="card h-100">
                            <div class="card-header bg-info text-white py-2">
                                <h6 class="mb-0 text-truncate">
                                    <i class="bi bi-images"></i> 
                                    <span class="d-none d-sm-inline">Browse Gallery</span>
                                    <span class="d-inline d-sm-none">Gallery</span>
                                </h6>
                            </div>
                            <div class="card-body p-2 p-md-3">
                                <!-- Search and Filter - Centered and Aligned -->
                                <div class="d-flex flex-column flex-md-row align-items-center justify-content-center gap-2 mb-3">
                                    <div class="flex-grow-1" style="min-width: 200px;">
                                        <input type="text" class="form-control form-control-sm text-center" id="gallerySearch" 
                                               placeholder="Search images...">
                                    </div>
                                    <div class="flex-grow-1" style="min-width: 200px;">
                                        <input type="text" class="form-control form-control-sm text-center" id="galleryTags" 
                                               placeholder="Filter by tags...">
                                    </div>
                                    <div class="flex-shrink-0">
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="searchGallery">
                                            <i class="bi bi-search"></i>
                                            <span class="d-none d-md-inline ms-1">Search</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Gallery Grid -->
                                <div id="galleryGrid" class="gallery-modal-grid">
                                    <!-- Images will be loaded here -->
                                </div>

                                <!-- Load More Button -->
                                <div class="text-center mt-2" id="loadMoreContainer" style="display: none;">
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="loadMoreBtn">
                                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                        Load More
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Selected Images Summary -->
                <div id="selectedImagesSummary" class="mt-3" style="display: none;">
                    <div class="alert alert-info">
                        <h6><i class="bi bi-check-circle"></i> Selected Images</h6>
                        <div id="selectedImagesList" class="selected-images-list"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="insertSelectedBtn" disabled>
                    <i class="bi bi-plus-circle"></i> Insert Selected Images
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.gallery-modal-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 15px;
    max-height: 400px;
    overflow-y: auto;
    padding: 10px;
    border: 1px solid #dee2e6;
    border-radius: 8px;
}

.gallery-modal-item {
    position: relative;
    aspect-ratio: 1;
    border-radius: 8px;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.gallery-modal-item:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.gallery-modal-item.selected {
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
}

.gallery-modal-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.gallery-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.gallery-modal-item:hover .gallery-modal-overlay {
    opacity: 1;
}

.gallery-modal-check {
    position: absolute;
    top: 5px;
    right: 5px;
    background: #007bff;
    color: white;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.gallery-modal-item.selected .gallery-modal-check {
    opacity: 1;
}

.gallery-modal-actions {
    position: absolute;
    top: 5px;
    left: 5px;
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: 10;
}

.gallery-modal-item:hover .gallery-modal-actions {
    opacity: 1;
}

.gallery-modal-actions .delete-image-btn {
    padding: 2px 6px;
    font-size: 10px;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    background: #dc3545;
    border: none;
    color: white;
}

.gallery-modal-actions .delete-image-btn:hover {
    transform: scale(1.1);
    background: #c82333;
}

.selected-images-list {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.selected-image-item {
    display: flex;
    align-items: center;
    background: white;
    padding: 5px 10px;
    border-radius: 20px;
    border: 1px solid #dee2e6;
    font-size: 12px;
}

.selected-image-item img {
    width: 20px;
    height: 20px;
    object-fit: cover;
    border-radius: 3px;
    margin-right: 5px;
}

.upload-area {
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 40px;
    text-align: center;
    background: #f8f9fa;
    transition: all 0.3s ease;
    cursor: pointer;
}

.upload-area:hover {
    border-color: #007bff;
    background: #e3f2fd;
}

.upload-area.dragover {
    border-color: #007bff;
    background: #e3f2fd;
}

.upload-preview {
    margin-top: 20px;
}

.upload-preview .col {
    position: relative;
}

.upload-preview img {
    width: 100%;
    height: 80px;
    object-fit: cover;
    border-radius: 4px;
}

.upload-preview .remove-preview {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    font-size: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Mobile responsive styles for delete buttons */
@media (max-width: 768px) {
    .gallery-modal-actions .delete-image-btn {
        padding: 4px 8px;
        font-size: 12px;
    }
    
    .gallery-modal-actions {
        top: 3px;
        left: 3px;
    }
}

@media (max-width: 576px) {
    .gallery-modal-actions .delete-image-btn {
        padding: 3px 6px;
        font-size: 11px;
    }
}
</style>
