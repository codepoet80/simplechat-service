<?php
$config = include('config.php');
include('lib/emoji.php');
$chatfile = $config['chatfile'];
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
    $msg->likes = $data['likes'];
    $msg->edited = $data['edited'];
    if($data['postedFrom']) {
	$msg->postedFrom = $data['postedFrom'];
    }
    return $msg;
}
?>
