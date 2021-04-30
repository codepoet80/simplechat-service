#!/bin/sh
# Legacy WebOS Youtube Service startup script

# Update config.php keys based on environmental variables 
if [ $SIMPLECHAT_CLIENT_ID ]; then sed -i "/OneOrMoreSecretsTheClientAndServerShare/c\\\t'$SIMPLECHAT_CLIENT_ID'" /var/www/html/config.php; fi
if [ $SIMPLECHAT_TITLE ]; then sed -i "/title/c\\\t'title' => '$SIMPLECHAT_TITLE'," /var/www/html/config.php; fi
if [ $SIMPLECHAT_WELCOME ]; then sed -i "/welcomemessage/c\\\t'welcomemessage' => '$SIMPLECHAT_WELCOME'," /var/www/html/config.php; fi
if [ $SIMPLECHAT_BOTHOOK ]; then sed -i "/bothook/c\\\t'bothook' => '$SIMPLECHAT_BOTHOOK'" /var/www/html/config.php; fi

# Start Apache
rm -f /var/run/apache2/apache2.pid
apachectl -DFOREGROUND
