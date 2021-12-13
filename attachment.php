<?php
// ithumb Endpoint
//      This endpoint creates (if needed) and returns a smaller version of an image share as binary data that can be the source of an HTML img element
$config = include('config.php');
include('common.php');

//Handle more specific queries
$image = null;
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
$path = $config['attachmentcache'];

//Fetch the file
$image = $path . $image;

if (!file_exists($image)) {
    gracefuldeath_httpcode(410);
}

//Send the right headers
$info = getimagesize($image);
header("Content-Type: " . $info['mime']);
header("Content-Length: " . filesize($image));
//Dump the file and stop the script
$fp = fopen($image, 'r');
fpassthru($fp);
exit;

function gracefuldeath_httpcode($code) {
    header($_SERVER["SERVER_PROTOCOL"] . $code);
}
?>