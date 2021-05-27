<?php
$config = include('config.php');
include('lib/emoji.php');
$chatfile = $config['chatfile'];
include('common.php');
$template = "chatlog-template.json";

header('Content-Type: application/json');

//Make sure the chat file exists and can be loaded
if (!file_exists($chatfile)){
    if (!file_exists($template)) {
        die ("{\"error\":\"chat file not found on server.\"}");
    } else {
        try {
            copy($template, $chatfile);
        }
        catch (exception $e)
        {
            die ("{\"error\":\"chat file not writeable on server\"}");
        }
    }
}

//Make sure they sent a client id
$request_headers = get_request_headers();
if (array_key_exists('Client-Id', $request_headers) && in_array($request_headers['Client-Id'], $config['clientids'])) {
} else {
    die ('
        {
            "messages": [{
                "uid": "999999",
                "sender": "Service Messenger",
                "message": "Your client or app is misconfigured or out-of-date and needs to be updated before you can send or receive messages. Please ensure you install the latest app, or that your client code has the correct Client ID (on webOS, this is the secrets.js file in the root of your app folder.)",
                "timestamp": "2021-04-16 16:16:42",
                "senderkey": "0000000"
            }]
        }
    ');
}

try {
    $chats = file_get_contents($chatfile);
    $chatData = json_decode($chats, true);
}
catch (exception $e) {
    die ("{\"error\":\"chat content could not be loaded: " . $e->getMessage . "\"}");
}

$newmessages = [];
class messagedata {};
foreach($chatData['messages'] as $chat)
{
    $chat = convert_message_to_public_schema($chat);
    array_push($newmessages, $chat);
}
$chatData['messages'] = $newmessages;

print_r (json_encode($chatData));
exit();

//remove sender key from the public
function convert_message_to_public_schema($data) {
    $msg = new messagedata();
    $msg->uid = $data['uid'];
    $msg->sender = $data['sender'];
    $msg->message = emoji_unified_to_html($data['message']);
    $msg->timestamp = $data['timestamp'];
    if (isset($data['likes']))
	    $msg->likes = $data['likes'];
    if (isset($data['edited']))
	    $msg->edited = $data['edited'];
    if (isset($data['postedFrom'])) {
	$msg->postedFrom = $data['postedFrom'];
    }
    return $msg;
}
?>
