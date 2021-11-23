<?php
return array(
	'clientids' => array (
        'OneOrMoreSecretsTheClientAndServerShare'
    ),	//These secrets need to be known to the client
    'title' => 'Your Chat!',    //Name of your chat as shown to web visitors
    'alticon' => '',	//leave empty to use the default
    'welcomemessage' => 'Describe the purpose of your chat for visitors! To hide from web viewers, copy index.off to index.html',
    'customcss' => '',  //eg: custom/style.css
    'allowedhtml' => '<p><b><i><u><br><ul><li><font>',
    'chatfile' => 'data/chatlog.json',  //path where chatlog will be created/read
    'maxchatlength' => 100, //how many messages the chatlog should store before rolling over
    'bothook' => '', //eg: http://localhost:8001/ -- must end with / Leave empty if you don't want to use the discord bot (https://github.com/codepoet80/simplechat-discordbot)
    'imgurclientid' => ''   //only required for imgur proxy
);
?>
