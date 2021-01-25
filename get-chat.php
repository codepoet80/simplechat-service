<?php
$config = include('config.php');
header('Content-Type: application/json');

$file = "data/chatlog.json";
$template = "chatlog-template.json";

//Make sure the chat file exists and can be loaded
if (!file_exists($file)){
    if (!file_exists($template)) {
        die ("{\"error\":\"chat files not found on server.\"}");
    } else {
        try {
            copy($template, $file);
        }
        catch (exception $e)
        {
            die ("{\"error\":\"chat file not writeable on server\"}");
        }
    }
}

try {
    $chats = file_get_contents($file);
    $chatdata = json_decode($chats, true);
}
catch (exception $e) {
    die ("{\"error\":\"chat content could not be loaded: " . $e->getMessage . "\"}");
}

$newmessages = [];
class messagedata {};
foreach($chatdata['messages'] as $chat)
{
    $chat = convert_message_to_public_schema($chat);
    array_push($newmessages, $chat);
}
$chatdata['messages'] = $newmessages;

print_r (json_encode($chatdata));
exit();

//remove sender key from the public
function convert_message_to_public_schema($data) {
    $msg = new messagedata();
    $msg->uid = $data['uid'];
    $msg->sender = $data['sender'];
    $msg->message = $data['message'];
    $msg->timestamp = $data['timestamp'];
    return $msg;
}
?>