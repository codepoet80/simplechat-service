# SimpleChat Service

This is a very simple, very tiny, public chat service.

It provides little in the way of security or privacy, its just a way for people to share short messages with each other.

It was originally written for webOS users, but could be used for other retro devices as well.

There's also a discord bot that can be integrated with it to share messages to-and-from a discord server: https://github.com/codepoet80/simplechat-discordbot 

Emoji library with huge thanks to: https://github.com/iamcal/php-emoji

## Install Notes

- **php-gd** is required. On Linux, do something like: `imagecreatefromjpeg`

Some functions depend on PHP file_get_contents to read the input. Ensure its enabled: https://www.php.net/manual/en/filesystem.configuration.php#ini.allow-url-fopen

If its enabled and you still have problems with things like "likes" check to make sure you're not [redirecting from HTTP to HTTPS, which does not preserve the post content](https://stackoverflow.com/questions/19146984/file-get-contentsphp-input-always-returns-an-empty-string). If you prefer to use HTTPS, ensure the client is configured not to attempt HTTP.
