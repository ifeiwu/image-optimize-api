#!/bin/bash

php -S 0.0.0.0:8182 -t /app &
php /app/server.php

wait