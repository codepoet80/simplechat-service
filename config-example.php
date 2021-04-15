<?php
return array(
	'clientids' => array (
        'OneOrMoreSecretsTheClientAndServerShare'
    ),	//This secret needs to be known to the client
    'title' => 'Your Chat!',
    'alt-image' => '',	//leave empty to use the default
    'welcomemessage' => 'Describe the purpose of your chat for visitors!'
    'allowedhtml' => '<p><b><i><u><br><ul><li><font>',
    'chatfile' => 'data/chatlog.json',
    'maxchatlength' => 100,
    'bothook' => '' //eg: http://localhost:8001/ -- leave empty if you don't want to use the discord bot. 
);
?>
