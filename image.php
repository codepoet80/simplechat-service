<?php
function base64url_encode($data)
{
  $b64 = base64_encode($data);
  if ($b64 === false) {
    return false;
  }
  $url = strtr($b64, '+/', '-_');
  return rtrim($url, '=');
}
function is_valid_base64($str){
    if (base64_decode($str, true) !== false){
        return true;
    } else {
        return false;
    }
}
?>
<html>
<head>
	<title>Image Proxy</title>
	<style>
		body { background-color: white; color:black; }
	</style>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
	<meta http-equiv="Pragma" content="no-cache" />
	<meta http-equiv="Expires" content="0" />
</head>
<body>
<?php
$source = $_SERVER['QUERY_STRING'];
if (is_valid_base64($source)) {
        $decoded = base64_decode($source);
        // SECURITY: Only allow http/https URLs to prevent javascript: URI injection
        if (!preg_match('/^https?:\/\//i', $decoded)) {
            die("invalid image URL");
        }
        echo '<img src="'. htmlspecialchars($decoded, ENT_QUOTES, 'UTF-8') . '">';
} else {
        die("could not parse image link");
}
?>
</body>
</html>
