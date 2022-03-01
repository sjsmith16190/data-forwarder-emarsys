# Databowl Emarsys API Forwarder
Custom Forwarder for Emarsys API v2. Takes a request and adds the required Emarsys WSSE Security Header token to the request then forwards it on.

## Setup config files

Create your configuration file:

    cp .env.example .env

There are various entries you need to fill in with data from your Emarsys account:

    EMARSYS_ENDPOINT=https://api.emarsys.net/api/v2

    EMARSYS_USERNAME=
    
    EMARSYS_SECRET=

## Install dependencies

    composer install

## Getting Started

Serve this project locally:

    php -S localhost:8000 -t public

Then curl a request in with the payload you'd expect to be sending to the Emarsys API:

#!/bin/sh
    emarsysApiRouteToHit="/contact"
    localRoute="http://localhost:8000/api"$emarsysApiRouteToHit
    data='{"3": "'$( echo $RANDOM$RANDOM )'@test.com", "4719": "brand", "4718": "destination", "7084": "source", "31": 1, "1": "steve", "2": "austin"}'

    curl -X POST -H "Content-Type: application/json" -d "${data}" "http://localhost:8000/api${emarsysApiRouteToHit}"

It will add the WSSE header to the request and you will receive the raw response the Emarsys API sends back.

## License

The Lumen framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
