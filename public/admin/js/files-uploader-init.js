document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('multiple-image-upload');
    let uploaderInstance = null;

    if (container && typeof MultipleImageUpload !== 'undefined') {
        try {
            uploaderInstance = new MultipleImageUpload(container.id, {
                maxFiles: parseInt(container.dataset.maxFiles),
                maxFileSize: parseInt(container.dataset.maxFileSize),
                modelType: container.dataset.modelType,
                modelId: container.dataset.modelId,
                uploadUrl: container.dataset.uploadUrl,
                updateUrl: container.dataset.updateUrl,
                imagesUrl: container.dataset.imagesUrl,
                primaryUrl: container.dataset.primaryUrl,
                reorderUrl: container.dataset.reorderUrl,
                altTextUrl: container.dataset.altTextUrl,
                deleteUrl: container.dataset.deleteUrl,
                existingImages: JSON.parse(container.dataset.existingImages || '[]')
            });

            window.multipleImageUploadInstance = uploaderInstance;
        } catch (error) {
            console.error('Image uploader init failed:', error);
        }
    }
});
