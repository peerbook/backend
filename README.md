# Peerbook Backend
Backend of the App peerbook

## Requirements
- PHP5.3+
- MongoDB Database 2.7+
- MongoDB PHP Driver
- Writable directories `webroot/protected/runtime`, `webroot/css`, `webroot/data`

## Installation
`composer install` and `bower install`

## Setup
You will change your settings in `webroot/protected/config/main.php` and other config files

## Testing API
The backend is mainly an restfull API which can be tested by using our API Explorer.
This can be found at `yourdomain.com/site/explorer`.

When you check `yourdomain.com/api/ping` you should get a response with `ping` when everything is allright.

## Made by 
Han van der Veen and Albert Wieringa

[Link to App](https://github.com/peerbook/app)
