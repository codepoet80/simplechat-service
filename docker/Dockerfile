FROM ubuntu

# Install dependencies and set timezone to UTC
RUN apt-get update -y
RUN apt-get upgrade -y
RUN apt-get install -y tzdata
ENV TZ "UTC"
RUN echo "UTC" > /etc/timezone
RUN dpkg-reconfigure --frontend noninteractive tzdata
RUN apt-get install -y git wget python cron curl apache2 php libapache2-mod-php php-mysql php-xml php-zip php-gd php-curl
RUN rm -f /etc/localtime
RUN ln -fs /usr/share/zoneinfo/UCT /etc/localtime

# Download codepoet80's simplechat service and configure it
RUN rm -rf /var/www/html/*
RUN cd /tmp; git clone https://github.com/codepoet80/simplechat-service
RUN mv /tmp/simplechat-service/* /var/www/html/
RUN mv /var/www/html/config-example.php /var/www/html/config.php

# Configure apache
RUN rm -f /etc/apache2/sites-available/000-default.conf
RUN wget https://raw.githubusercontent.com/h8pewou/Legacy-WebOS-Youtube-Service/main/000-default.conf -O /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite
RUN chown -R www-data:www-data /var/www/html/
ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2
ENV APACHE_LOCK_DIR /var/lock/apache2
ENV APACHE_PID_FILE /var/run/apache2.pid

EXPOSE 80

RUN wget https://raw.githubusercontent.com/codepoet80/simplechat-service/main/docker/run.sh -O /run.sh
RUN chmod a+rx /run.sh
CMD ["/bin/bash", "/run.sh"]
