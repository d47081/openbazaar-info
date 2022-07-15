<?php

class CurlIp extends Curl {

    public function getLocation($ip, $timeout) {

        $this->prepare('/json.gp?ip=' . $ip, 'GET', $timeout);

        if ($response = $this->execute()) {

            switch (false) {
                case isset($response['geoplugin_request']):
                case isset($response['geoplugin_status']):
                case isset($response['geoplugin_city']):
                case isset($response['geoplugin_region']):
                case isset($response['geoplugin_regionName']):
                case isset($response['geoplugin_countryCode']):
                case isset($response['geoplugin_countryName']):
                case isset($response['geoplugin_latitude']):
                case isset($response['geoplugin_longitude']):

                    return false;
            }

            return $response;
        }

        return false;
    }
}
