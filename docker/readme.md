# Simplechat Service

## Introduction

A tiny message board for retro devices and related communities. This image contains apache, php, and codepoet80's simplechat service code.

## Usage

### Docker run (x64)

#### Using environmental variables
You can simply run the following command to set the service up. 

```bash
docker run -d --name simplechat-service \
--restart=unless-stopped \
-p 8084:80 \
-e SIMPLECHAT_CLIENT_ID=your_simplechat_client_id \
-e SIMPLECHAT_TITLE=your_simplechat_title \
-e SIMPLECHAT_WELCOME=your_simplechat_welcome_message \
-e SIMPLECHAT_BOTHOOK="http://bothook-address:8001" \
h8pewou/simplechat-service:latest
```

Note that the secure keys are optional. See the WebOS app settings for more details on configuration.


#### Using persistent volumes

You can also use volumes to configure the application. Download a sample config.php from [here](https://raw.githubusercontent.com/codepoet80/simplechat-service/main/config-example.php).


Example:
```bash
wget https://raw.githubusercontent.com/codepoet80/simplechat-service/main/config-example.php
```

Ensure that /path/to is replaced with the actual path:

```bash
docker run -d --name simplechat-service \
--restart=unless-stopped \
-p 8084:80 \
-v /path/to/config.php:/var/www/html/config.php \
h8pewou/simplechat-service:latest
```


### Docker-compose (x64)

Alternatively you can use the following docker-compose.yml:

```yaml
version: '3.9'

networks:
  bridge:
    driver: bridge

services:
  wrapper:
    image: h8pewou/simplechat-service:latest
    networks:
      - bridge
    ports:
      - "8084:80"
    restart: unless-stopped
    environment:
      - SIMPLECHAT_CLIENT_ID=your_client_id # Not required if config.php volume is configured
      - SIMPLECHAT_TITLE=your_title # Not required if config.php volume is configured
      - SIMPLECHAT_WELCOME=your_welcome_message # Not required if config.php volume is configured
      - SIMPLECHAT_BOTHOOK="http://bothook-address:8001" # Not required if config.php volume is configured
    volumes:
      - /path/to/config.php:/var/www/html/config.php # Optional if environment variables are configured above
```

Ensure that /path/to is replaced with the actual path. Issue ```docker-compose up``` to start the service.


### Are you on arm64 (e.g., Raspberry Pi)?

Replace ```h8pewou/simplechat-service:latest``` with ```h8pewou/simplechat-service:arm64```.
