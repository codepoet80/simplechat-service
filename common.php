<?php
function get_request_headers() {
	//Cross platform way to get request headers, thanks to https://stackoverflow.com/a/20164575/8216691
	$request_headers = [];
	if (!function_exists('getallheaders')) {
	    foreach ($_SERVER as $name => $value) {
	        /* RFC2616 (HTTP/1.1) defines header fields as case-insensitive entities. */
	        if (strtolower(substr($name, 0, 5)) == 'http_') {
	            $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
	        }
	    }
	    $request_headers = $headers;
	} else {
	    $request_headers = getallheaders();
	}
	return $request_headers;
}
?>
