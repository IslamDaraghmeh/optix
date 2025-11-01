<?php
/**
 * File Upload Helper Class
 *
 * Handles file uploads, validation, and image processing
 *
 * @package App\Helpers
 * @author Optix Development Team
 * @version 1.0
 */

namespace App\Helpers;

use App\Helpers\Security;

class FileUpload
{
    /**
     * @var Security Security instance
     */
    private Security $security;

    /**
     * @var string Upload directory
     */
    private string $uploadDir;

    /**
     * Constructor
     *
     * @param string|null $uploadDir Custom upload directory
     */
    public function __construct(?string $uploadDir = null)
    {
        $this->security = new Security();
        $this->uploadDir = $uploadDir ?? UPLOAD_PATH;

        // Ensure upload directory exists
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    /**
     * Upload file
     *
     * @param array $file $_FILES array element
     * @param string|null $subdirectory Subdirectory within upload path
     * @param array $allowedTypes Allowed file extensions
     * @param int $maxSize Maximum file size in bytes
     * @return array ['success' => bool, 'message' => string, 'filepath' => string|null]
     */
    public function upload(array $file, ?string $subdirectory = null, array $allowedTypes = [], int $maxSize = 0): array
    {
        // Validate file upload
        $validation = $this->security->validateFileUpload($file, $allowedTypes, $maxSize);
        if (!$validation['success']) {
            return $validation;
        }

        // Prepare upload directory
        $uploadPath = $this->uploadDir;
        if ($subdirectory) {
            $uploadPath .= '/' . trim($subdirectory, '/');
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
        }

        // Generate secure filename
        $filename = $this->generateSecureFilename($file['name']);
        $filepath = $uploadPath . '/' . $filename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return [
                'success' => true,
                'message' => 'File uploaded successfully',
                'filepath' => $filepath,
                'filename' => $filename,
            ];
        }

        return [
            'success' => false,
            'message' => 'Failed to move uploaded file',
            'filepath' => null,
        ];
    }

    /**
     * Upload image with resizing
     *
     * @param array $file $_FILES array element
     * @param string|null $subdirectory Subdirectory within upload path
     * @param int $maxWidth Maximum width
     * @param int $maxHeight Maximum height
     * @return array
     */
    public function uploadImage(array $file, ?string $subdirectory = null, int $maxWidth = 1920, int $maxHeight = 1080): array
    {
        // Upload original file
        $result = $this->upload($file, $subdirectory, ['jpg', 'jpeg', 'png', 'gif']);

        if (!$result['success']) {
            return $result;
        }

        // Resize image
        $resized = $this->resizeImage($result['filepath'], $maxWidth, $maxHeight);

        if (!$resized) {
            return [
                'success' => false,
                'message' => 'Failed to resize image',
                'filepath' => null,
            ];
        }

        return $result;
    }

    /**
     * Resize image
     *
     * @param string $filepath Image file path
     * @param int $maxWidth Maximum width
     * @param int $maxHeight Maximum height
     * @return bool
     */
    public function resizeImage(string $filepath, int $maxWidth, int $maxHeight): bool
    {
        if (!file_exists($filepath)) {
            return false;
        }

        // Get image info
        $imageInfo = getimagesize($filepath);
        if (!$imageInfo) {
            return false;
        }

        [$width, $height, $type] = $imageInfo;

        // Check if resize is needed
        if ($width <= $maxWidth && $height <= $maxHeight) {
            return true;
        }

        // Calculate new dimensions
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = (int)($width * $ratio);
        $newHeight = (int)($height * $ratio);

        // Create image from file
        switch ($type) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($filepath);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($filepath);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($filepath);
                break;
            default:
                return false;
        }

        if (!$source) {
            return false;
        }

        // Create new image
        $destination = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency for PNG and GIF
        if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_GIF) {
            imagealphablending($destination, false);
            imagesavealpha($destination, true);
            $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
            imagefilledrectangle($destination, 0, 0, $newWidth, $newHeight, $transparent);
        }

        // Resize
        imagecopyresampled($destination, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Save resized image
        switch ($type) {
            case IMAGETYPE_JPEG:
                $result = imagejpeg($destination, $filepath, 90);
                break;
            case IMAGETYPE_PNG:
                $result = imagepng($destination, $filepath, 9);
                break;
            case IMAGETYPE_GIF:
                $result = imagegif($destination, $filepath);
                break;
            default:
                $result = false;
        }

        // Free memory
        imagedestroy($source);
        imagedestroy($destination);

        return $result;
    }

    /**
     * Create thumbnail
     *
     * @param string $filepath Original image path
     * @param int $width Thumbnail width
     * @param int $height Thumbnail height
     * @param string|null $outputPath Output path (if null, appends _thumb to filename)
     * @return string|false Thumbnail path or false on failure
     */
    public function createThumbnail(string $filepath, int $width = 150, int $height = 150, ?string $outputPath = null)
    {
        if (!file_exists($filepath)) {
            return false;
        }

        // Get image info
        $imageInfo = getimagesize($filepath);
        if (!$imageInfo) {
            return false;
        }

        [$origWidth, $origHeight, $type] = $imageInfo;

        // Generate output path if not provided
        if (!$outputPath) {
            $pathInfo = pathinfo($filepath);
            $outputPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];
        }

        // Create image from file
        switch ($type) {
            case IMAGETYPE_JPEG:
                $source = imagecreatefromjpeg($filepath);
                break;
            case IMAGETYPE_PNG:
                $source = imagecreatefrompng($filepath);
                break;
            case IMAGETYPE_GIF:
                $source = imagecreatefromgif($filepath);
                break;
            default:
                return false;
        }

        if (!$source) {
            return false;
        }

        // Calculate dimensions (crop to fit)
        $ratio = max($width / $origWidth, $height / $origHeight);
        $tempWidth = (int)($origWidth * $ratio);
        $tempHeight = (int)($origHeight * $ratio);

        $temp = imagecreatetruecolor($tempWidth, $tempHeight);
        imagecopyresampled($temp, $source, 0, 0, 0, 0, $tempWidth, $tempHeight, $origWidth, $origHeight);

        // Crop to exact size
        $thumbnail = imagecreatetruecolor($width, $height);
        $x = ($tempWidth - $width) / 2;
        $y = ($tempHeight - $height) / 2;

        imagecopy($thumbnail, $temp, 0, 0, $x, $y, $width, $height);

        // Save thumbnail
        switch ($type) {
            case IMAGETYPE_JPEG:
                $result = imagejpeg($thumbnail, $outputPath, 90);
                break;
            case IMAGETYPE_PNG:
                $result = imagepng($thumbnail, $outputPath, 9);
                break;
            case IMAGETYPE_GIF:
                $result = imagegif($thumbnail, $outputPath);
                break;
            default:
                $result = false;
        }

        // Free memory
        imagedestroy($source);
        imagedestroy($temp);
        imagedestroy($thumbnail);

        return $result ? $outputPath : false;
    }

    /**
     * Delete file
     *
     * @param string $filepath File path
     * @return bool
     */
    public function delete(string $filepath): bool
    {
        if (file_exists($filepath) && is_file($filepath)) {
            return unlink($filepath);
        }
        return false;
    }

    /**
     * Generate secure filename
     *
     * @param string $originalName Original filename
     * @return string
     */
    private function generateSecureFilename(string $originalName): string
    {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $basename = pathinfo($originalName, PATHINFO_FILENAME);

        // Sanitize basename
        $basename = preg_replace('/[^a-zA-Z0-9_-]/', '', $basename);
        $basename = substr($basename, 0, 50); // Limit length

        // Add timestamp and random string
        $timestamp = date('YmdHis');
        $random = bin2hex(random_bytes(8));

        return $basename . '_' . $timestamp . '_' . $random . '.' . $extension;
    }

    /**
     * Get file extension
     *
     * @param string $filename Filename
     * @return string
     */
    public function getExtension(string $filename): string
    {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }

    /**
     * Get file size in human-readable format
     *
     * @param string $filepath File path
     * @return string
     */
    public function getHumanReadableSize(string $filepath): string
    {
        if (!file_exists($filepath)) {
            return '0 B';
        }

        $size = filesize($filepath);
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }

        return round($size, 2) . ' ' . $units[$i];
    }

    /**
     * Check if file is image
     *
     * @param string $filepath File path
     * @return bool
     */
    public function isImage(string $filepath): bool
    {
        $extension = $this->getExtension($filepath);
        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif']);
    }

    /**
     * Get MIME type
     *
     * @param string $filepath File path
     * @return string|false
     */
    public function getMimeType(string $filepath)
    {
        if (!file_exists($filepath)) {
            return false;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filepath);
        finfo_close($finfo);

        return $mimeType;
    }
}
