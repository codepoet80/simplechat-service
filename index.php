<?php
    if (file_exists("index.html")) {
        header("Location: index.html");
        die();
    }
    $config = include('config.php');
    include('lib/emoji.php');
    $icon = "icon";
    if ($config['alticon'] != "")
	$icon = $config['alticon'];
?>
<html>
<head>
<title><?php echo $config['title']?></title>
<link rel="shortcut icon" href="<?php echo $icon ?>.ico">
<link rel="stylesheet" href="style.css">
<?php
    if (file_exists($config['customcss'])) {
        echo '<link rel="stylesheet" href="' . $config['customcss'] .'">';
    }
?>
<link href="lib/emoji.css?cb=<?php echo time(); ?>" rel="stylesheet" type="text/css" />
<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=1" />
<link rel="icon" href="<?php echo $icon ?>.png" type="image/png">
<meta http-equiv="Pragma" content="no-cache">
</head>
<script>
    setTimeout("location.reload(true);", 180000);
</script>
<body>
<div class="page-title"><?php echo $config['title']?></div>
<div class="description">
<?php
echo $config['welcomemessage'];
?>
    The log contains the last <?php echo $config['maxchatlength'] ?> messages sent, newest on top, and is read-only for now. 
</div>
<div class="message-area">
<?php
$url = get_function_endpoint('') . "get-chat.php";
$chatData = json_decode(get_chat($url, $config['clientids'][0]));
$chats = $chatData->messages;

$chats = array_reverse($chats);
foreach ($chats as $chat) {
    echo "<div class='message-group'>";
    echo "  <p><span class='sender'>". $chat->sender . ": </span>";
    echo "  <span class='message'>". emoji_unified_to_html($chat->message) . "</span</p>";
    echo "  <p class='timestamp'><small>" . $chat->timestamp . " UTC</small></p>";
    echo "</div>";
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

function get_chat($endpoint, $clientid) {
	if ($endpoint != "") {
   	    $ch = curl_init($endpoint);

	    if(isset($ch)) {
            $customHeaders = array(
                'Content-Type:application/json',
                'Client-Id:' . $clientid,
            );

     		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		    curl_setopt($ch, CURLOPT_HTTPHEADER, $customHeaders);
      		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
      		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      		$result = curl_exec($ch);
      		curl_close($ch);
      		return $result;
    	}
	}
}
?>
</div>
<div class='footer'>Provided by <a href="http://www.webosarchive.com">webOS Archive</a> | Copyright 2021 <a href="http://www.jonandnic.com">Jon Wise</a> | Open Source with a MIT License: <a href="https://github.com/codepoet80/simplechat-service">Git Repo</a></footer>
</body>
</html>
