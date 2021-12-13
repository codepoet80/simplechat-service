<?php
// ithumb Endpoint
//      This endpoint creates (if needed) and returns a smaller version of an image share as binary data that can be the source of an HTML img element
$config = include('config.php');
include('common.php');

//Handle more specific queries
$image = null;
$imgSize = 128;
if (isset($_GET["size"]))
    $imgSize = $_GET["size"];
if (isset($_GET['image']) && $_GET['image'] != "") {
    $image = $_GET['image'];
} else { //Accept a blanket query
    if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] != "")
        $image = $_SERVER['QUERY_STRING'];
}
if (!isset($image)) {    //Deal with no usable request
    gracefuldeath_httpcode(400);
}

if (!is_dir($config['attachmentcache'])) {
    gracefuldeath_httpcode(417);
}

//Make sure the file exists and can be loaded
$found = true;
//Prepare the cache name
$cacheID = "thumb-" . $image;
$path = $config['attachmentcache'];

//Fetch and cache the file if its not already cached
$image = $path . $image;
$path = $path . $cacheID;

if (!file_exists($path)) {
    resize_img($imgSize, $path, $image);
}

//Send the right headers
$info = getimagesize($path);
header("Content-Type: " . $info['mime']);
header("Content-Length: " . filesize($path));
//Dump the file and stop the script
$fp = fopen($path, 'r');
fpassthru($fp);
exit;

if (!$found) {
    gracefuldeath_httpcode(410);
}

//Function to resize common image formats
//  Found on https://stackoverflow.com/questions/13596794/resize-images-with-php-support-png-jpg
function resize_img($newWidth, $targetFile, $originalFile) {

    $info = getimagesize($originalFile);
    $mime = $info['mime'];

    switch ($mime) {
            case 'image/jpeg':
                    $image_create_func = 'imagecreatefromjpeg';
                    $image_save_func = 'imagejpeg';
                    $new_image_ext = 'jpg';
                    break;

            case 'image/png':
                    $image_create_func = 'imagecreatefrompng';
                    $image_save_func = 'imagepng';
                    $new_image_ext = 'png';
                    break;

            case 'image/gif':
                    $image_create_func = 'imagecreatefromgif';
                    $image_save_func = 'imagegif';
                    $new_image_ext = 'gif';
                    break;

            default: 
                    throw new Exception('Unknown image type.');
    }

    $img = $image_create_func($originalFile);
    list($width, $height) = getimagesize($originalFile);

    $newHeight = ($height / $width) * $newWidth;
    $tmp = imagecreatetruecolor($newWidth, $newHeight);
    imagesavealpha($tmp, true);
    $trans_colour = imagecolorallocatealpha($tmp, 0, 0, 0, 127);
    imagefill($tmp, 0, 0, $trans_colour);

    imagecopyresampled($tmp, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    if (file_exists($targetFile)) {
            unlink($targetFile);
    }
    $image_save_func($tmp, $targetFile);
}

function gracefuldeath_httpcode($code) {
    header($_SERVER["SERVER_PROTOCOL"] . $code);
}
?>