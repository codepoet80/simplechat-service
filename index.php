<?php
    $config = include('config.php');
?>
<html>
<head>
<title>webOS SimpleChat Log</title>
<style>
    * { font-family:arial;}
    a { text-decoration: none; color:rgb(117,43,177); }
    a:hover { text-decoration: underline; color:rgb(117,43,177); }
    .page-title { font-size: 28px; padding-bottom: 18px; display: flex; align-items: center;}
    .page-title::before { content: url('icon.png'); margin-right: 18px; }
    .description { float:right; text-align: right;  margin-right: 2%; max-width: 80%; font-size: 14px; font-style: italic; color: dimgray;}
    .message-area { clear: both; padding-top: 20px; }
    .message-group { border-bottom: 1px solid gray; margin: -4px; padding-left: 8px; clear: both }
    .sender { font-weight: bold; }
    .timestamp { margin-top: -12px; }
    .footer { margin-top: 18px; font-size: 12px; color: dimgray;}
    small { font-size: 11px; color: dimgray; }
    strong { font-size: 26px; font-weight:bold; }
</style>
<link rel="shortcut icon" href="favicon.ico">
<link rel="stylesheet" href="style.css">
<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=1" />
<link rel="icon" href="icon.png" type="image/png">
<meta http-equiv="Pragma" content="no-cache">
</head>
<script>
    setTimeout("location.reload(true);", 180000);
</script>
<body>
<div class="page-title">webOS SimpleChat Log</div>
<div class="description">
    This chat is to help legacy webOS (Palm/HP Pre, Touchpad and related devices) stay in touch as a back-up to the <a href="http://www.webosnation.com">forums</a>.
    The log contains the last <?php echo $config['maxchatlength'] ?> messages sent, newest on top, and is read-only for now. To join the conversation, <a href="http://appcatalog.webosarchive.com/showMuseumDetails.php?category=Revisionist+History&count=12&app=1005777">install webOS SimpleChat</a> on your device or emulator!
</div>
<div class="message-area">
<?php
$url = get_function_endpoint('') . "get-chat.php";

$json = file_get_contents($url);
$chatData = json_decode($json);
$chats = $chatData->messages;
$chats = array_reverse($chats);
foreach ($chats as $chat) {
    echo "<div class='message-group'>";
    echo "  <p><span class='sender'>". $chat->sender . ": </span>";
    echo "  <span class='message'>". $chat->message . "</span</p>";
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
?>
</div>
<div class='footer'>Provided by <a href="http://www.webosarchive.com">webOS Archive</a> | Copyright 2021 <a href="http://www.jonandnic.com">Jon Wise</a> | Open Source with a MIT License: <a href="https://github.com/codepoet80/simplechat-service">Git Repo</a></footer>
</body>
</html>