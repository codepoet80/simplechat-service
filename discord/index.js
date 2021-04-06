const config = require('./config.json');
const http = require('http')
const express = require('express');
const bodyParser = require('body-parser');
const fs = require('file-system');
var dataFile = config.simpleChatDataFile;

const webapp = express();
webapp.use(express.json());
var webPort = config.webPort;

const Discord = require('discord.js');
const client = new Discord.Client();
var appId = config.discordAppId;

var listenChannel = "828052451164684319";	//prod channel

//Web server
webapp.post('/post', function (req, res) {
	console.log("post request was: " + JSON.stringify(req.body));
	var message = req.body.content;
	message = convertEmoticons(message);
	res.end("{'status':'ok'}")
	var channel = client.channels.cache.get(listenChannel);
        channel.send("**" + req.body.username + "**: " + message).then(message => {
                console.log("Sent message id: " + message.id);
		//update chatlog.json to include ID from discord
		discordIDToSimpleChat(req.body.uid, message.id);
        });
});

webapp.post('/like', async function (req, res) {
	console.log("like request was: " + JSON.stringify(req.body));
	var messageId = req.body.uid;
	var messageContent = req.body.content;
	var discordId = req.body.discordId;
	res.end("{'status':'ok'}")
	var channel = client.channels.cache.get(listenChannel);
	var findMsg = await findMessage(messageId, messageContent, discordId);

	if (findMsg) {
		var reactMsg = await channel.messages.fetch(findMsg);
		reactMsg.react('828100281295831041');	//heart emoji
	}

});

webapp.post('/edit', async function (req, res) {
	console.log("edit request was: " + JSON.stringify(req.body));
	var messageId = req.body.uid;
	var sender = req.body.sender;
	var newContent = convertEmoticons(req.body.newcontent);
	var oldContent = req.body.oldcontent;
	var discordId = req.body.discordId;
	res.end("{'status':'ok'}")
	var channel = client.channels.cache.get(listenChannel);
	var findMsg = await findMessage(messageId, oldContent, discordId);
	if (findMsg) {
		var editMsg = await channel.messages.fetch(findMsg);
		editMsg.edit("**" + sender + "**: " + newContent);
	}

});

var server = webapp.listen(webPort, function() {
	var host = server.address().address;
	var port = server.address().port;
	console.log("🤖 Listening for messages to send to Discord at http://%s:%s", host, port);
});

var findMessage = async function(messageId, messageContent, discordId) {
	console.log("looking for message: " + messageId + "/" + discordId + ": " + messageContent);
	var channel = client.channels.cache.get(listenChannel);
        var findMsg = await channel.messages.fetch({limit: 100}).then(messages => {
                for (message of messages) {
                        var checkMessage = message[1];
                        var checkMsgContent = message[1].cleanContent;
                        checkMsgContent = checkMsgContent.split("**: ");
                        checkMsgContent = checkMsgContent[checkMsgContent.length - 1];
                        if (checkMessage.id == messageId || checkMessage.id == discordId || checkMsgContent == messageContent) {
				console.log("Found matching message in Discord: " + checkMessage.id);
                                return checkMessage.id;
                        }
                }
        });
	return findMsg;
}

//Discord client
client.on('ready', () => {
	console.log(`Logged in as ${client.user.tag}!`);
});

client.on('message', msg => {
	console.log(msg.id + " is a new message from: " + msg.author + ", in channel:" + msg.channel);
	if (msg.channel == listenChannel)
		postToSimpleChat(msg);
	else
		console.log("not posting message");
});

client.on('messageReactionAdd', (reaction, user) => {
  console.log("a reaction happened on: " + reaction.message + " user was bot: " + user.bot);
  if (!user.bot && true == true) {
    //get rest of message
    var channel = client.channels.cache.get(listenChannel);
    channel.messages.fetch({around: reaction.messageID, limit: 1}).then(discordMessages => {
    	var discordMsg = discordMessages.first();
    	discordMsg = discordMsg.cleanContent;
    	discordMsg = discordMsg.split("**: ");
    	discordMsg = discordMsg[discordMsg.length - 1];

        fs.exists(dataFile, (exists) => {
	  fs.readFile(dataFile, function(err, data) {
		if (data) {
			var json = JSON.parse(data);
			if (json) {
				for (var m=0;m<json.messages.length;m++) {
					if (json.messages[m].uid == reaction.message || json.messages[m].message == discordMsg) {
						console.log("found chatlog message to like!");
						if (!json.messages[m].likes)
							json.messages[m].likes = 1;
						else
							json.messages[m].likes++;
					}
				}
				fs.writeFile(dataFile, JSON.stringify(json, null, 4));
			}
		}
	  });
       });
     });
   }
});

client.on('messageUpdate', (oldMsg, newMsg) => {
    console.log("an edit happened on: " + oldMsg.id);
    discordMsg = oldMsg.cleanContent;
    discordMsg = discordMsg.split("**: ");
    discordMsg = discordMsg[discordMsg.length - 1];

    fs.exists(dataFile, (exists) => {
	  fs.readFile(dataFile, function(err, data) {
		if (data) {
			var json = JSON.parse(data);
			if (json) {
				for (var m=0;m<json.messages.length;m++) {
					if (json.messages[m].uid == oldMsg.id || json.messages[m].message == discordMsg) {
						json.messages[m].message = convertEmojis(newMsg.cleanContent);
					}
				}
				fs.writeFile(dataFile, JSON.stringify(json, null, 4));
			}
		}
	  });
     });
});

client.login(appId);

//Helper functions

function postToSimpleChat(msg) {
	var user = new Discord.User(client, msg.author);
	if (!user.bot && !user.system) {
		console.log("posting to simplechat file");
		var newMessage = {
			"uid": msg.id,
			"senderKey": msg.nonce,
			"sender": user.username,
			"message": convertEmojis(msg.cleanContent),
			"timestamp": formatDateTime(msg.createdAt),
			"postedFrom": "discord",
			"discordId": msg.id
		}
		console.log("Posting: " + JSON.stringify(newMessage));

		fs.readFile(dataFile, function(err, data) {
			if (data) {
				var json = JSON.parse(data);
				if (json) {
					json.messages.push(newMessage);
					fs.writeFile(dataFile, JSON.stringify(json, null, 4));
				}
			}
		});
	}
}

function discordIDToSimpleChat(uid, did) {
	console.log("append discordid " + did + " to simplechat uid: " + uid);
        fs.readFile(dataFile, function(err, data) {
        	if (data) {
                	var json = JSON.parse(data);
                        if (json) {
				for (var m=0;m<json.messages.length;m++) {
					if (json.messages[m].uid == uid)
						json.messages[m].discordId = did;
				}
                        	fs.writeFile(dataFile, JSON.stringify(json, null, 4));
                	}
        	}
	});
}

function convertEmoticons(message) {
	for (var e=0;e<emojiTranslate.length;e++)
	{
		if (message.indexOf(emojiTranslate[e].webOS) != -1) {
			var useEmoji = emojiTranslate[e].emoji;
			message = message.replace(emojiTranslate[e].webOS, useEmoji);
		}
	}
	return message;
}

function convertEmojis(message) {
	var emojis = message.match(/<.*?>/gs);
		if (emojis) {
		console.log("Detected emojis: " + emojis);
		for (var e=0;e<emojis.length;e++) {
			newEmoji = emojis[e];
			message = message.replace(emojis[e], newEmoji);
			emojis[e] = newEmoji;
		}
		for (var e=0;e<emojis.length;e++) {
			message = message.replace(emojis[e], discordToWebOSEmoji(emojis[e]));
		}
	}
	return message;
}

function discordToWebOSEmoji(emoji) {
	for (var i=0;i<emojiTranslate.length;i++) {
		if (emojiTranslate[i].emoji == emoji) {
			return emojiTranslate[i].webOS;
		}
	}
	return "[?]";
}

function formatDateTime(currentDateTime) {
	function appendLeadingZeroes(n){
	  if(n <= 9){
	    return "0" + n;
	  }
	  return n
	}
	currentDateTime = currentDateTime.getFullYear() + "-" + appendLeadingZeroes(currentDateTime.getMonth() + 1) + "-" + appendLeadingZeroes(currentDateTime.getDate()) + " " + appendLeadingZeroes(currentDateTime.getHours()) + ":" + appendLeadingZeroes(currentDateTime.getMinutes()) + ":" + appendLeadingZeroes(currentDateTime.getSeconds());
	return currentDateTime;
}

var emojiTranslate = [
	{ "emoji": "<:sc_wink:828100281899548695>", "webOS":";)" },
	{ "emoji": "<:sc_eek:828100281660080188>", "webOS": ":-!" },
	{ "emoji": "<:sc_angel:828100281585500171>", "webOS":"O:)" },
	{ "emoji": "<:sc_smile:828100281614729236>", "webOS": ":)" },
	{ "emoji": "<:sc_evil:828100281564397568>", "webOS":">:-)" },
	{ "emoji": "<:sc_doh:828100281559416862>", "webOS":":/" },
	{ "emoji": "<:sc_sick:828100281542901780>", "webOS":":@"},
	{ "emoji": "<:sc_wtf:828100281522454528>", "webOS":"o_O" },
	{ "emoji": "<:sc_mad:828100281518260224>", "webOS":">:(" },
	{ "emoji": "<:sc_redface:828100281509478410>", "webOS":":[" },
	{ "emoji": "<:sc_omg:828100281509085184>", "webOS":":O" },
	{ "emoji": "<:sc_sad:828100281505808404>", "webOS":":(" },
	{ "emoji": "<:sc_cool:828100281455345725>", "webOS":"B-)" },
	{ "emoji": "<:sc_lol:828100281358090262>", "webOS":":D" },
	{ "emoji": "<:sc_kiss:828100281300156437>", "webOS":":-*" },
	{ "emoji": "<:sc_heart:828100281295831041>", "webOS":"<3" },
	{ "emoji": "<:sc_yuck:828100281271058453>", "webOS":":P" },
	{ "emoji": "<:sc_neutral:828100281266601995>", "webOS":":|" },
	{ "emoji": "<:sc_eww:828100281120194612>", "webOS":"X(" },
	{ "emoji": "<:sc_grin:828100281107611680>", "webOS":"^_^" },
	{ "emoji": "<:sc_cry:828100281408684103>", "webOS":":'(" }
];
