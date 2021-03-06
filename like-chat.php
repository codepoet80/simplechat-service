<?php
$config = include('config.php');
header('Content-Type: application/json');

$file = "data/chatlog.json";
$numLikes = 0;
$found = false;

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

if (isset($postdata->uid) && $postdata->uid != "" && isset($postdata->like) && $postdata->like != "") {

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
        if ($chat->uid === $postdata->uid) {
            echo "found!";
            $found = true;
            if ($chat->likes) {
                if ($postdata->like == "+1") {
                    $chat->likes++;
                }
                else {
                    if ($chat->likes > 0)
                        $chat->likes--;
                }
            } else {
                if ($postdata->like == "+1")
                    $chat->likes = 1;
            }
            $numLikes = $chat->likes;
            
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
    die ("{\"error\":\"incomplete like payload\"}");
}

if (!$written) {
    die ("{\"error\":\"failed to write to chat file\"}");
} 

if ($found == false) {
    echo "{\"warning\":\"message with uid " . $postdata->uid . " not found to like\"}";
} else {
    echo "{\"liked\":\"" . $postdata->uid . "\", \"likes\":\"" . $numLikes . "\"}";
}
exit();
?>