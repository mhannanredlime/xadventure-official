<div class="mb-4" x-data="imageUploader({
    id: '{{ $uploaderId ?? 'multiple-image-upload' }}',
    maxFiles: {{ $maxFiles ?? 10 }},
    maxFileSize: {{ $maxFileSize ?? 5 * 1024 * 1024 }},
    existingImages: @json($existingImagesArray ?? []),
    deleteUrl: '{{ $deleteUrl ?? '' }}',
    uploadUrl: '{{ $uploadUrl ?? '' }}',
    csrfToken: '{{ csrf_token() }}'
})" id="{{ $uploaderId ?? 'multiple-image-upload' }}" {{ $attributes }}>

    <div class="col-12">
        {{-- Hidden Input for Form Submission --}}
        <input type="file" x-ref="realFileInput" name="images[]" multiple accept="image/*" class="d-none"
            @change="handleFileSelect">
 sadfsdfdsfdsfdsfdfd
        <div class="multiple-image-upload-area">
            <div class="mb-3">
                <button type="button" class="btn btn-save jatio-bg-color upload-images-btn"
                    @click="$refs.realFileInput.click()">
                    <i class="bi bi-upload me-1"></i> Upload Images
                </button>
            </div>

            {{-- Grid --}}
            <div class="d-flex flex-wrap gap-2 additional-images-grid">

                {{-- Existing Images --}}
                <template x-for="(image, index) in existingImages" :key="'existing-' + image.id">
                    <div class="card additional-image-item position-relative" style="width:300px;height:300px;">
                        <img :src="'/storage/' + image.image_path" class="card-img-top object-fit-cover"
                            style="height:100%;" :alt="image.alt_text || 'Image'">

                        <button type="button"
                            class="btn btn-sm btn-outline-danger position-absolute top-0 end-0 m-2 delete-image"
                            @click="deleteExistingImage(image.id)">
                            <i class="bi bi-x"></i>
                        </button>

                        <div class="position-absolute bottom-0 start-0 m-2 badge-container">
                            <template x-if="index === 0 && newFiles.length === 0">
                                <span class="badge jatio-bg-color">Main Image</span>
                            </template>
                        </div>
                    </div>
                </template>

                {{-- New Files --}}
                <template x-for="(fileWrapper, index) in newFiles" :key="fileWrapper.id">
                    <div class="card additional-image-item position-relative" style="width:300px;height:300px;">
                        <img :src="fileWrapper.preview" class="card-img-top object-fit-cover" style="height:100%;"
                            :alt="fileWrapper.file.name">
                        <button type="button"
                            class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 remove-image"
                            @click="removeNewFile(fileWrapper.id)">
                            <i class="bi bi-x"></i>
                        </button>
                        <div class="position-absolute bottom-0 start-0 m-2 badge-container">
                            {{-- If no existing images, first new file is Main --}}
                            <template x-if="existingImages.length === 0 && index === 0">
                                <span class="badge jatio-bg-color">Main Image</span>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <small class="text-muted mt-2 d-block">
            <i class="bi bi-info-circle me-1"></i>
            Upload new images or manage existing ones.
        </small>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('imageUploader', (config) => ({
                newFiles: [], // Array of { id, file, preview }
                existingImages: config.existingImages,

                init() {
                    console.log('Alpine Image Uploader Initialized', this.existingImages);
                    // Expose this instance to the DOM element for parent access
                    this.$el._x_uploader = this;
                },

                handleFileSelect(e) {
                    const files = Array.from(e.target.files);
                    if (files.length === 0) return;

                    const validFiles = files.filter(file => this.validateFile(file));

                    validFiles.forEach(file => {
                        this.newFiles.push({
                            id: Date.now() + Math.random().toString(36).substring(2),
                            file: file,
                            preview: URL.createObjectURL(file)
                        });
                    });

                    // Clear input to allow re-selecting same file
                    e.target.value = '';

                    // Sync to parent/form immediately (optional, depending on strategy)
                    this.syncFiles();
                },

                validateFile(file) {
                    const max = config.maxFileSize;
                    if (file.size > max) {
                        alert(
                            `File ${file.name} is too large. Max size: ${Math.round(max/1024/1024)}MB`);
                        return false;
                    }
                    if (!file.type.startsWith('image/')) {
                        alert(`File ${file.name} is not an image.`);
                        return false;
                    }
                    if ((this.newFiles.length + this.existingImages.length) >= config.maxFiles) {
                        alert(`Maximum ${config.maxFiles} images allowed.`);
                        return false;
                    }
                    return true;
                },

                removeNewFile(id) {
                    const index = this.newFiles.findIndex(f => f.id === id);
                    if (index > -1) {
                        URL.revokeObjectURL(this.newFiles[index].preview);
                        this.newFiles.splice(index, 1);
                        this.syncFiles();
                    }
                },

                deleteExistingImage(id) {
                    if (!confirm('Are you sure you want to delete this image?')) return;

                    fetch(config.deleteUrl.replace(':id', id), {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': config.csrfToken
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                this.existingImages = this.existingImages.filter(img => img.id !==
                                    id);
                            } else {
                                alert(data.message || 'Failed to delete image');
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            alert('Error deleting image');
                        });
                },

                // Returns proper File objects for 
                getSelectedFiles() {
                    // Extract the raw File objects from the wrappers
                    return this.newFiles.map(f => f.file);
                },

                // Optional: Helper to update input if needed manually
                syncFiles() {
                    // This function is placeholder if we want to "push" to another input.
                    // But parent 'attachImages' pulls via getSelectedFiles() which is cleaner.
                }
            }));
        });
    </script>
</div>
