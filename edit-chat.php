<?php
$config = include('config.php');
header('Content-Type: application/json');
$found = false;
$file = "data/chatlog.json";

//Make sure the chat file exists and can be loaded
if (!file_exists($file)){
    die ("{\"error\":\"chat file not found on server.\"}");
}

if (!is_writable($file)) {
    die ("{\"error\":\"chat file not writeable on server\"}");
}

//Make sure they sent a client id
$request_headers = getallheaders();
if (array_key_exists('Client-Id', $request_headers) && in_array($request_headers['Client-Id'], $config['clientids'])) {
} else {
    die ("{\"error\":\"no allowed Client-Id in POST headers\"}");
}

//Make sure we can get the input
$postjson = file_get_contents('php://input'); 
try {
    $postdata = json_decode($postjson);
}
catch (Exception $e) {
    die ("{\"error\":\"invalid chat payload: " . $e->getMessage() . "\"}");
}

if (isset($postdata->message) && $postdata->message != "" && isset($postdata->sender) && $postdata->sender != "" && isset($postdata->uid) && $postdata->uid != "" && isset($postdata->editKey) && $postdata->editKey != "") {

    //load existing chat data
    $chats = file_get_contents($file);
    try {
        $chatData = json_decode($chats);
    }
    catch (exception $e) {
        die ("{\"error\":\"chat content could not be loaded: " . $e->getMessage . "\"}");
    }

    foreach($chatData->messages as $chat)
    {
        if ($chat->uid == $postdata->uid) {
            if ($chat->senderKey === $postdata->editKey && $chat->sender === $postdata->sender) {
		//handle special webOS emoticons
		$postdata->message = str_replace("<3", "&lt;3", $postdata->message);
		$postdata->message = str_replace(">:-)", "&gt;:-)", $postdata->message);
		$postdata->message = str_replace(">:(", "&gt;:(", $postdata->message);
                $chat->message = strip_tags($postdata->message, $config['allowedhtml']);
                //calculate time stamp
                $now = new DateTime("now", new DateTimeZone("UTC"));
                $now = $now->format('Y-m-d H:i:s');
                $chat->edited = $now;
                $found = true;
            }
            else {
                die ("{\"error\":\"attempt to edit message with uid " . $postdata->uid . " was not authorized\"}");
            }
        }
    }
    try {
        $newChatData = json_encode($chatData, JSON_PRETTY_PRINT);
        $written = file_put_contents($file, $newChatData);
    }
    catch (exception $e) {
        die ("{\"error\":\"chat content could not be updated: " . $e->getMessage . "\"}");
    }
}
else {
    die ("{\"error\":\"incomplete edit payload\"}");
}

if (!$written) {
    die ("{\"error\":\"failed to write to chat file\"}");
} 

if (!$found) {
    echo "{\"warning\":\"message with uid " . $postdata->uid . " not found to edit\"}";
} else {
    echo "{\"edited\":\"" . $postdata->uid . "\"}";
}
exit();
?>
