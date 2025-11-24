@extends('layouts.admin')
@push('styles')
@endpush

@section('content')
    <div class="container-fluid mt-4">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Image Gallery</h1>
            @can('gallery.manage')
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                    <i class="bi bi-cloud-upload"></i> Upload Images
                </button>
            @endcan
        </div>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Admin</li>
                <li class="breadcrumb-item active">Image Gallery</li>
            </ol>
        </nav>

        <!-- Search and Filter Bar -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.gallery.index') }}" id="searchForm">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="search" class="form-label">Search Images</label>
                            <input type="text" class="form-control" id="search" name="search"
                                value="{{ $search }}" placeholder="Search by filename or alt text...">
                        </div>
                        <div class="col-md-4">
                            <label for="tags" class="form-label">Filter by Tags</label>
                            <input type="text" class="form-control" id="tags" name="tags"
                                value="{{ is_array($tags) ? implode(',', $tags) : $tags }}"
                                placeholder="Enter tags separated by commas...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="bi bi-search"></i> Search
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Gallery Grid -->
        <div class="card shadow">
            <div class="card-body">
                @if ($images->count() > 0)
                    <div class="row g-3" id="galleryGrid">
                        @foreach ($images as $image)
                            <div class="col-lg-3 col-md-4 col-sm-6" data-image-id="{{ $image->id }}">
                                <div class="gallery-item">
                                    <div class="gallery-image-container">
                                        <img src="{{ $image->thumbnail_url }}" alt="{{ $image->alt_text }}"
                                            class="gallery-image" data-bs-toggle="modal" data-bs-target="#imageModal"
                                            data-image-id="{{ $image->id }}">
                                        <div class="gallery-overlay">
                                            <div class="gallery-actions">
                                                <button type="button" class="btn btn-sm btn-outline-light view-image"
                                                    data-image-id="{{ $image->id }}">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                @can('gallery.manage')
                                                    <button type="button" class="btn btn-sm btn-outline-light edit-image"
                                                        data-image-id="{{ $image->id }}">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger delete-image"
                                                        data-image-id="{{ $image->id }}">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                @endcan
                                            </div>
                                        </div>
                                    </div>
                                    <div class="gallery-info">
                                        <h6 class="gallery-filename" title="{{ $image->original_name }}">
                                            {{ Str::limit($image->original_name, 20) }}
                                        </h6>
                                        <small class="text-muted">
                                            {{ number_format($image->file_size / 1024, 1) }} KB
                                        </small>
                                        <small class="text-muted d-block">
                                            {{ $image->created_at->format('M d, Y') }}
                                        </small>
                                        @if ($image->tags && count($image->tags) > 0)
                                            <div class="gallery-tags">
                                                @foreach (array_slice($image->tags, 0, 2) as $tag)
                                                    <span class="badge bg-secondary me-1">{{ $tag }}</span>
                                                @endforeach
                                                @if (count($image->tags) > 2)
                                                    <span
                                                        class="badge bg-light text-dark">+{{ count($image->tags) - 2 }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $images->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-images display-1 text-muted"></i>
                        <h4 class="mt-3 text-muted">No images found</h4>
                        <p class="text-muted">
                            @if ($search || $tags)
                                Try adjusting your search criteria or
                                <a href="{{ route('admin.gallery.index') }}">clear filters</a>.
                            @else
                                Upload your first images to get started.
                            @endif
                        </p>
                        @if (!$search && !$tags)
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#uploadModal">
                                <i class="bi bi-cloud-upload"></i> Upload Images
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadModalLabel">Upload Images to Gallery</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="uploadArea" class="upload-area">
                        <div class="upload-content">
                            <i class="bi bi-cloud-upload display-1 text-muted"></i>
                            <h5>Drag & Drop Images Here</h5>
                            <p class="text-muted">or click to select files</p>
                            <input type="file" id="fileInput" multiple accept="image/*" style="display: none;">
                            <button type="button" class="btn btn-primary"
                                onclick="document.getElementById('fileInput').click()">
                                Choose Files
                            </button>
                        </div>
                    </div>
                    <div id="uploadPreview" class="upload-preview" style="display: none;">
                        <h6>Selected Files:</h6>
                        <div id="previewContainer" class="row g-2"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="uploadBtn" disabled>
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Upload Images
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Image View Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">Image Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" alt="" class="img-fluid mb-3">
                    <div id="imageDetails">
                        <!-- Image details will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-outline-primary edit-image" id="modalEditBtn">
                        <i class="bi bi-pencil"></i> Edit
                    </button>
                    <button type="button" class="btn btn-outline-danger delete-image" id="modalDeleteBtn">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Image Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Image Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editAltText" class="form-label">Alt Text</label>
                            <input type="text" class="form-control" id="editAltText" name="alt_text">
                        </div>
                        <div class="mb-3">
                            <label for="editTags" class="form-label">Tags (comma-separated)</label>
                            <input type="text" class="form-control" id="editTags" name="tags"
                                placeholder="e.g., vehicle, atv, outdoor">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="spinner-border spinner-border-sm d-none" role="status"
                                aria-hidden="true"></span>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('admin.gallery.partials.gallery-modal')
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('admin/css/gallery.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('admin/js/gallery-manager.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize gallery manager
            if (typeof GalleryManager !== 'undefined') {
                new GalleryManager();
            }
        });
    </script>
@endpush
