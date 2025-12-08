<div class="mb-4">
    
        <div class="col-12">
            <input type="file" id="package_images_input" name="images[]" multiple accept="image/*" hidden>
            <div id="multiple-image-upload" data-model-type="{{ $modelType }}" data-model-id="{{ $modelId }}"
                data-upload-url="{{ $uploadUrl }}" data-update-url="{{ $updateUrl }}"
                data-images-url="{{ $imagesUrl }}" data-primary-url="{{ $primaryUrl }}"
                data-reorder-url="{{ $reorderUrl }}" data-alt-text-url="{{ $altTextUrl }}"
                data-delete-url="{{ $deleteUrl }}" data-existing-images="{{ $existingImages }}"
                data-max-files="{{ $maxFiles }}" data-max-file-size="{{ $maxFileSize }}">
            </div>
            @if (!empty($existingImagesArray))
                <div class="mt-3">
                    <p class="text-success mb-2">
                        <i class="bi bi-check-circle me-1"></i>
                        {{ count($existingImagesArray) }} existing image(s) found
                    </p>

                    <div class="image-preview-container">
                        @foreach ($existingImagesArray as $idx => $image)
                            <div class="existing-image">
                                <img src="{{ asset('storage/' . $image['image_path']) }}"
                                    alt="Image {{ $idx + 1 }}"
                                    title="{{ $image['alt_text'] ?? 'Package Image' }}">
                                <span class="image-number">{{ $idx + 1 }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <small class="text-muted mt-2 d-block">
                <i class="bi bi-info-circle me-1"></i>
                Upload new images or manage existing ones.
            </small>

        </div>
    
</div>
