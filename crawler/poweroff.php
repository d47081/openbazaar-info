<?php

/*
 * Shutdown server signal on power issues (router & server UPS relation check)
 * Priority: hight
 * Permission: root
 */

exec('/usr/bin/curl -Is 192.168.0.1 | head -n 1', $response);

if (isset($response[0]) && $response[0] == 'HTTP/1.1 200 OK') {
    echo "Power ON\n";
} else {
    echo "Power OFF\n\n";
    $response = exec("/sbin/shutdown");
    echo print_r($response, true);
}
