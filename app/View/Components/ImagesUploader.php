<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ImagesUploader extends Component
{
    public $modelType, $modelId, $uploadUrl, $updateUrl;
    public $imagesUrl, $primaryUrl, $reorderUrl, $altTextUrl, $deleteUrl;
    public $existingImages, $existingImagesArray;
    public $maxFiles, $maxFileSize;

    public function __construct(
        $modelType,
        $modelId = null,
        $uploadUrl,
        $updateUrl = null,
        $imagesUrl,
        $primaryUrl,
        $reorderUrl,
        $altTextUrl,
        $deleteUrl,
        $existingImages = '[]',
        $maxFiles = 4,
        $maxFileSize = 5242880
    ) {
        $this->modelType = $modelType;
        $this->modelId = $modelId;
        $this->uploadUrl = $uploadUrl;
        $this->updateUrl = $updateUrl;
        $this->imagesUrl = $imagesUrl;
        $this->primaryUrl = $primaryUrl;
        $this->reorderUrl = $reorderUrl;
        $this->altTextUrl = $altTextUrl;
        $this->deleteUrl = $deleteUrl;
        $this->existingImages = $existingImages;
        $this->existingImagesArray = json_decode($existingImages, true);
        $this->maxFiles = $maxFiles;
        $this->maxFileSize = $maxFileSize;
    }

    public function render()
    {
        return view('components.images-uploader');
    }
}
