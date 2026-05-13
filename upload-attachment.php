<?php
$config = include('config.php');
include('common.php');

header('Content-Type: application/json');

// Validate Client-Id header
$request_headers = get_request_headers();
if (!array_key_exists('Client-Id', $request_headers) || !in_array($request_headers['Client-Id'], $config['clientids'])) {
    die('{"error":"no allowed Client-Id in POST headers"}');
}

// Validate attachment cache is configured and accessible
$cachePath = $config['attachmentcache'];
if (!$cachePath || !is_dir($cachePath)) {
    die('{"error":"attachment cache not configured or not found on server"}');
}

// Validate file was received
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    $errCode = isset($_FILES['image']) ? $_FILES['image']['error'] : 'no file';
    die('{"error":"file upload error: ' . $errCode . '"}');
}

$fileItem = $_FILES['image'];

// Size limit: 5MB
if ($fileItem['size'] > 5242880) {
    die('{"error":"image is too large; maximum size is 5MB"}');
}

// Validate it's actually an image by reading file bytes -- don't trust the client-declared type
$imageInfo = getimagesize($fileItem['tmp_name']);
if ($imageInfo === false) {
    die('{"error":"uploaded file is not a valid image"}');
}
$mime = $imageInfo['mime'];

$allowedMimes = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($mime, $allowedMimes)) {
    die('{"error":"unsupported image type: ' . htmlspecialchars($mime, ENT_QUOTES) . '"}');
}

switch ($mime) {
    case 'image/jpeg': $ext = 'jpg'; break;
    case 'image/png':  $ext = 'png'; break;
    case 'image/gif':  $ext = 'gif'; break;
}

// Save uploaded file
$newid = uniqid();
$origFile = $cachePath . $newid . '.' . $ext;
$rsFile   = $cachePath . 'rs-' . $newid . '.' . $ext;

if (!move_uploaded_file($fileItem['tmp_name'], $origFile)) {
    die('{"error":"failed to save uploaded file"}');
}

// Create resized version (max 1024px, matches Discord bot convention)
try {
    $dims = resize_img(1024, $rsFile, $origFile);
} catch (Exception $e) {
    copy($origFile, $rsFile);
    $info = getimagesize($origFile);
    $dims = ['width' => $info[0], 'height' => $info[1]];
}

echo json_encode([
    'filename'  => 'rs-' . $newid . '.' . $ext,
    'extension' => $ext,
    'width'     => $dims['width'],
    'height'    => $dims['height']
]);
exit;

function resize_img($newWidth, $targetFile, $originalFile) {
    $info = getimagesize($originalFile);
    $mime = $info['mime'];
    $origWidth  = $info[0];
    $origHeight = $info[1];

    // Don't upscale images smaller than the target width
    if ($origWidth <= $newWidth) {
        copy($originalFile, $targetFile);
        return ['width' => $origWidth, 'height' => $origHeight];
    }

    switch ($mime) {
        case 'image/jpeg':
            $image_create_func = 'imagecreatefromjpeg';
            $image_save_func   = 'imagejpeg';
            break;
        case 'image/png':
            $image_create_func = 'imagecreatefrompng';
            $image_save_func   = 'imagepng';
            break;
        case 'image/gif':
            $image_create_func = 'imagecreatefromgif';
            $image_save_func   = 'imagegif';
            break;
        default:
            throw new Exception('Unsupported image type: ' . $mime);
    }

    $img = $image_create_func($originalFile);
    $newHeight = (int)(($origHeight / $origWidth) * $newWidth);
    $tmp = imagecreatetruecolor($newWidth, $newHeight);
    imagesavealpha($tmp, true);
    $trans_colour = imagecolorallocatealpha($tmp, 0, 0, 0, 127);
    imagefill($tmp, 0, 0, $trans_colour);
    imagecopyresampled($tmp, $img, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
    $image_save_func($tmp, $targetFile);
    imagedestroy($img);
    imagedestroy($tmp);

    return ['width' => $newWidth, 'height' => $newHeight];
}
?>
