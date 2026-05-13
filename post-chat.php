<?php
$config = include('config.php');
$chatfile = $config['chatfile'];
$template = "chatlog-template.json";
$bothook = $config['bothook'];
include('common.php');

header('Content-Type: application/json');

//Make sure the chat file exists and can be loaded
if (!file_exists($chatfile)){
    if (!file_exists($template)) {
        die ("{\"error\":\"chat files not found on server.\"}");
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

if (!is_writable($chatfile)) {
    die ("{\"error\":\"chat file not writeable on server\"}");
}

//Make sure they sent a client id
$request_headers = get_request_headers();
if (array_key_exists('Client-Id', $request_headers) && in_array($request_headers['Client-Id'], $config['clientids'])) {
    //nothing to do
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

$hasMessage = isset($postdata->message) && $postdata->message != "";
$hasAttachments = isset($postdata->attachments) && is_array($postdata->attachments) && count($postdata->attachments) > 0;

if (($hasMessage || $hasAttachments) && isset($postdata->sender) && $postdata->sender != "") {

    //assign ids (one public, one for the sender only)
    $newid = uniqid();
    $senderKey = uniqid();

    //calculate time stamp
    $now = new DateTime("now", new DateTimeZone("UTC"));
    $now = $now->format('Y-m-d H:i:s');

    //cleanse and prep incoming data
    $newpost = new stdClass();
    $newpost->uid = $newid;
    $newpost->senderKey = $senderKey;
    $newpost->sender = strip_tags($postdata->sender, $config['allowedhtml']);
    //handle special webOS emoticons
    $newpost->message = $hasMessage ? $postdata->message : '';
    $newpost->message = str_replace("<3", "&lt;3", $newpost->message);
    $newpost->message = str_replace(">:-)", "&gt;:-)", $newpost->message);
    $newpost->message = str_replace(">:(", "&gt;:(", $newpost->message);
    $newpost->message = strip_tags($newpost->message, $config['allowedhtml']);
    $newpost->timestamp = $now;
    if ($hasAttachments)
        $newpost->attachments = $postdata->attachments;

    //load existing chat data
    $chats = file_get_contents($chatfile);
    $chatData = json_decode($chats);
    if (json_last_error() !== JSON_ERROR_NONE) {
        die ("{\"error\":\"chat content could not be loaded: " . json_last_error_msg() . "\"}");
    }

    //update with new chat message
    try {
        array_push($chatData->messages, $newpost);
        while (count($chatData->messages) > $config['maxchatlength']) {
            array_shift($chatData->messages);
        }
        $newChatData = json_encode($chatData, JSON_PRETTY_PRINT);
        $written = file_put_contents($chatfile, $newChatData, LOCK_EX);
    }
    catch (exception $e) {
        die ("{\"error\":\"chat content could not be updated: " . $e->getMessage() . "\"}");
    }
    //Copy to Discord
    if ($bothook != "")
        $discordpost = botmsg($newpost->message, $newpost->sender, $newpost->uid, $bothook."post", $hasAttachments ? $postdata->attachments : null);
}
else {
    die ("{\"error\":\"incomplete chat payload\"}");
}

if (!$written) {
    die ("{\"error\":\"failed to write to chat file\"}");
}

echo "{\"posted\":\"" . $newid . "\", \"senderKey\":\"" . $senderKey . "\"}";

exit();

function botmsg($message, $user, $uid, $endpoint, $attachments = null) {
	if ($endpoint != "") {
   	    $ch = curl_init($endpoint);
	    $data = array('username'=>$user, 'content'=>$message, 'uid'=>$uid);
	    if ($attachments !== null)
	        $data['attachments'] = $attachments;

	    if(isset($ch)) {
     		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
      		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
      		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      		$result = curl_exec($ch);
      		curl_close($ch);
      		return $result;
    	}
	}
}

?>
