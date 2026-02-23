<?php
/**
 * Procesamiento y optimización de imágenes
 * Compatible con GD Library (disponible en la mayoría de hostings compartidos)
 */

/**
 * Procesar y optimizar imagen subida
 * Retorna [filename, thumbnail_filename] o false en caso de error
 */
function processUploadedImage(array $file): array|false {
    // Validar que sea un archivo subido
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return false;
    }

    // Validar tamaño
    if ($file['size'] > MAX_IMAGE_SIZE) {
        return false;
    }

    // Validar tipo MIME real
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($mimeType, $allowedMimes)) {
        return false;
    }

    // Crear imagen desde el archivo
    $source = createImageFromFile($file['tmp_name'], $mimeType);
    if (!$source) return false;

    // Generar nombre único
    $filename = uniqid('profile_', true) . '.jpg';

    // Redimensionar imagen principal
    $resized = resizeImage($source, PROFILE_MAX_WIDTH, PROFILE_MAX_HEIGHT);

    // Guardar imagen principal optimizada como JPEG
    if (!imagejpeg($resized, PROFILES_PATH . $filename, JPEG_QUALITY)) {
        imagedestroy($source);
        imagedestroy($resized);
        return false;
    }

    // Crear thumbnail cuadrado
    $thumb = createSquareThumbnail($source, THUMB_WIDTH);
    $thumbFilename = 'thumb_' . $filename;
    imagejpeg($thumb, THUMBNAILS_PATH . $thumbFilename, JPEG_QUALITY);

    // Liberar memoria
    imagedestroy($source);
    imagedestroy($resized);
    imagedestroy($thumb);

    return [
        'filename' => $filename,
        'thumbnail' => $thumbFilename
    ];
}

/**
 * Crear recurso de imagen desde archivo
 */
function createImageFromFile(string $path, string $mimeType): GdImage|false {
    return match ($mimeType) {
        'image/jpeg' => imagecreatefromjpeg($path),
        'image/png' => imagecreatefrompng($path),
        'image/webp' => imagecreatefromwebp($path),
        default => false,
    };
}

/**
 * Redimensionar imagen manteniendo proporción
 */
function resizeImage(GdImage $source, int $maxWidth, int $maxHeight): GdImage {
    $origWidth = imagesx($source);
    $origHeight = imagesy($source);

    // Si ya es más pequeña, retornar copia
    if ($origWidth <= $maxWidth && $origHeight <= $maxHeight) {
        $copy = imagecreatetruecolor($origWidth, $origHeight);
        imagecopy($copy, $source, 0, 0, 0, 0, $origWidth, $origHeight);
        return $copy;
    }

    // Calcular nuevas dimensiones
    $ratio = min($maxWidth / $origWidth, $maxHeight / $origHeight);
    $newWidth = (int)round($origWidth * $ratio);
    $newHeight = (int)round($origHeight * $ratio);

    $resized = imagecreatetruecolor($newWidth, $newHeight);

    // Preservar transparencia para PNGs
    imagealphablending($resized, false);
    imagesavealpha($resized, true);

    imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

    return $resized;
}

/**
 * Crear thumbnail cuadrado (recorte centrado)
 */
function createSquareThumbnail(GdImage $source, int $size): GdImage {
    $origWidth = imagesx($source);
    $origHeight = imagesy($source);

    // Determinar el recorte cuadrado centrado
    $cropSize = min($origWidth, $origHeight);
    $srcX = (int)(($origWidth - $cropSize) / 2);
    $srcY = (int)(($origHeight - $cropSize) / 2);

    $thumb = imagecreatetruecolor($size, $size);
    imagecopyresampled($thumb, $source, 0, 0, $srcX, $srcY, $size, $size, $cropSize, $cropSize);

    return $thumb;
}

/**
 * Eliminar imagen y su thumbnail
 */
function deleteImage(string $filename): void {
    $profilePath = PROFILES_PATH . $filename;
    $thumbPath = THUMBNAILS_PATH . 'thumb_' . $filename;

    if (file_exists($profilePath)) unlink($profilePath);
    if (file_exists($thumbPath)) unlink($thumbPath);
}
