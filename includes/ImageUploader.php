<?php

class ImageUploader {
    private $uploadDir;
    private $allowedTypes;
    private $maxFileSize;

    public function __construct() {
        $this->uploadDir = __DIR__ . '/../uploads/products/';
        $this->allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $this->maxFileSize = 5 * 1024 * 1024;
    }

    public function uploadImage($file, $productId = null) {
        try {
            if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
                throw new Exception('No file uploaded or upload error occurred');
            }

            if ($file['size'] > $this->maxFileSize) {
                throw new Exception('File size too large. Maximum 5MB allowed.');
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mimeType, $this->allowedTypes)) {
                throw new Exception('Invalid file type. Only JPEG, PNG, GIF, and WebP allowed.');
            }

            $extension = $this->getExtensionFromMimeType($mimeType);
            $filename = $this->generateUniqueFilename($extension, $productId);
            $filePath = $this->uploadDir . $filename;

            if (!is_dir($this->uploadDir)) {
                mkdir($this->uploadDir, 0755, true);
            }

            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                throw new Exception('Failed to move uploaded file');
            }

            $this->resizeImage($filePath, $mimeType);

            return 'uploads/products/' . $filename;

        } catch (Exception $e) {
            error_log('Image upload error: ' . $e->getMessage());
            throw $e;
        }
    }

    private function generateUniqueFilename($extension, $productId = null) {
        $prefix = $productId ? "product_{$productId}_" : "product_";
        $timestamp = time();
        $random = bin2hex(random_bytes(8));
        return $prefix . $timestamp . '_' . $random . '.' . $extension;
    }

    private function getExtensionFromMimeType($mimeType) {
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp'
        ];
        return $extensions[$mimeType] ?? 'jpg';
    }

    private function resizeImage($filePath, $mimeType, $maxWidth = 800, $maxHeight = 600) {
        if (!extension_loaded('gd')) {
            return;
        }

        try {
            list($width, $height) = getimagesize($filePath);

            if ($width <= $maxWidth && $height <= $maxHeight) {
                return;
            }

            $ratio = min($maxWidth / $width, $maxHeight / $height);
            $newWidth = round($width * $ratio);
            $newHeight = round($height * $ratio);

            $sourceImage = $this->createImageResource($filePath, $mimeType);
            if (!$sourceImage) {
                return;
            }

            $newImage = imagecreatetruecolor($newWidth, $newHeight);
            
            if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
                imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
            }

            imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            $this->saveImageResource($newImage, $filePath, $mimeType);

            imagedestroy($sourceImage);
            imagedestroy($newImage);

        } catch (Exception $e) {
            error_log('Image resize error: ' . $e->getMessage());
        }
    }

    private function createImageResource($filePath, $mimeType) {
        switch ($mimeType) {
            case 'image/jpeg':
                return imagecreatefromjpeg($filePath);
            case 'image/png':
                return imagecreatefrompng($filePath);
            case 'image/gif':
                return imagecreatefromgif($filePath);
            case 'image/webp':
                return imagecreatefromwebp($filePath);
            default:
                return false;
        }
    }

    private function saveImageResource($imageResource, $filePath, $mimeType, $quality = 85) {
        switch ($mimeType) {
            case 'image/jpeg':
                return imagejpeg($imageResource, $filePath, $quality);
            case 'image/png':
                return imagepng($imageResource, $filePath);
            case 'image/gif':
                return imagegif($imageResource, $filePath);
            case 'image/webp':
                return imagewebp($imageResource, $filePath, $quality);
            default:
                return false;
        }
    }

    public function deleteImage($imagePath) {
        if (empty($imagePath) || strpos($imagePath, 'http') === 0) {
            return true;
        }

        $fullPath = __DIR__ . '/../' . $imagePath;
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        return true;
    }

    public function isValidImageUrl($url) {
        if (empty($url)) return false;
        
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return true;
        }
        
        if (strpos($url, 'uploads/') === 0) {
            $fullPath = __DIR__ . '/../' . $url;
            return file_exists($fullPath);
        }
        
        return false;
    }
}
?>
