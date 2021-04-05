<?php
$config = include('config.php');
header('Content-Type: application/json');

$file = "data/chatlog.json";
$template = "chatlog-template.json";
$bothook = "http://localhost:8001/";

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

if (!is_writable($file)) {
    die ("{\"error\":\"chat file not writeable on server\"}");
}

//Make sure they sent a client id
$request_headers = getallheaders();
if (array_key_exists('Client-Id', $request_headers) && in_array($request_headers['Client-Id'], $config['clientids'])) {
    //TODO: some kind of allowed client config file
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

if (isset($postdata->message) && $postdata->message != "" && isset($postdata->sender) && $postdata->sender != "") {

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
    $newpost->message = $postdata->message;
    $newpost->message = str_replace("<3", "&lt;3", $postdata->message);
    $newpost->message = str_replace(">:-)", "&gt;:-)", $postdata->message);
    $newpost->message = str_replace(">:(", "&gt;:(", $postdata->message);
    $newpost->message = strip_tags($newpost->message, $config['allowedhtml']);
    $newpost->timestamp = $now;

    //load existing chat data
    $chats = file_get_contents($file);
    try {
        $chatData = json_decode($chats);
    }
    catch (exception $e) {
        die ("{\"error\":\"chat content could not be loaded: " . $e->getMessage . "\"}");
    }

    //update with new chat message
    try {
        array_push($chatData->messages, $newpost);
        while (count($chatData->messages) > $config['maxchatlength']) {
            array_shift($chatData->messages);
        }
        $newChatData = json_encode($chatData, JSON_PRETTY_PRINT);
    }
    catch (exception $e) {
        die ("{\"error\":\"chat content could not be updated: " . $e->getMessage . "\"}");
    }
    $written = file_put_contents($file, $newChatData);

    //Copy to Discord
    $discordpost = botmsg($postdata->message, $newpost->sender, $newpost->uid, $bothook."post");
}
else {
    die ("{\"error\":\"incomplete chat payload\"}");
}

if (!$written) {
    die ("{\"error\":\"failed to write to chat file\"}");
}

echo "{\"posted\":\"" . $newid . "\", \"senderKey\":\"" . $senderKey . "\", \"discordResponse\":\"". $discordresponse ."\"}";

exit();

function botmsg($message, $user, $uid, $endpoint) {
	if ($endpoint != "") {
   	    $ch = curl_init($endpoint);
	    $data = array('username'=>$user, 'content'=>$message, 'uid'=>$uid);

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
