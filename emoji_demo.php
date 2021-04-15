<?php
	header('Content-type: text/html; charset=UTF-8');

	include('lib/emoji.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<title>Emoji HTML Test</title>
	<link href="lib/emoji.css?cb=<?php echo time(); ?>" rel="stylesheet" type="text/css" />
</head>
<body>

<table border="1">
	<tr>
		<th>Unified</th>
		<th>Name</th>
		<th>Unified Text</th>
		<th>HTML</th>
		<th>HTML to Unified</th>
	</tr>
<?php

	$src = array(
		array(0x2600),		# BLACK SUN WITH RAYS
		array(0x1F494),		# BROKEN HEART (was U+1F493)
		array(0x1F197),		# OK SIGN (was U+1F502)
		array(0x32, 0x20E3),	# KEYCAP 2
		array(🎥)
	);

	function utf8_bytes($cp){

		if ($cp > 0x10000){
			# 4 bytes
			return	chr(0xF0 | (($cp & 0x1C0000) >> 18)).
				chr(0x80 | (($cp & 0x3F000) >> 12)).
				chr(0x80 | (($cp & 0xFC0) >> 6)).
				chr(0x80 | ($cp & 0x3F));
		}else if ($cp > 0x800){
			# 3 bytes
			return	chr(0xE0 | (($cp & 0xF000) >> 12)).
				chr(0x80 | (($cp & 0xFC0) >> 6)).
				chr(0x80 | ($cp & 0x3F));
		}else if ($cp > 0x80){
			# 2 bytes
			return	chr(0xC0 | (($cp & 0x7C0) >> 6)).
				chr(0x80 | ($cp & 0x3F));
		}else{
			# 1 byte
			return chr($cp);
		}
	}

	foreach ($src as $unified){

		$bytes = '';
		$hex = array();

		foreach ($unified as $cp){
			$bytes .= utf8_bytes($cp);
			$hex[] = sprintf('U+%04X', $cp);

		}

		$str = "Hello $bytes World";

		echo "<tr>\n";
		echo "<td>".implode(' ', $hex)."</td>\n";
		echo "<td>".HtmlSpecialChars(emoji_get_name($bytes))."</td>\n";
		echo "<td>$str</td>\n";
		echo "<td>".emoji_unified_to_html($str)."</td>\n";
		echo "<td>".emoji_html_to_unified(emoji_unified_to_html($str))."</td>\n";
		echo "</tr>\n";
	}


	function get_function_endpoint($functionName) {
		if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')   
			$url = "https://";
		else  
			$url = "http://";
		$url.= $_SERVER['HTTP_HOST'];   
		$url.= $_SERVER['REQUEST_URI'];  
		$url = strtok($url, "?");
		$page = basename($_SERVER['PHP_SELF']);
		$url = str_replace($page, $functionName, $url);
		return $url;
	}
?>
</table>

</body>
</html>
