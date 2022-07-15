<?php

class CurlRating extends Curl {

    public function getRatings($peerId, $timeout) {

        $this->prepare('/ob/ratings/' . $peerId, 'GET', $timeout);

        if ($response = $this->execute()) {

            switch (false) {
                case isset($response['average']):
                case isset($response['count']):
                case isset($response['ratings']) && is_array($response['ratings']):
                    return false;
            }

            return $response;
        }

        return false;
    }

    public function getRating($ratingHash, $timeout) {

        $this->prepare('/ob/rating/' . $ratingHash, 'GET', $timeout);

        if ($response = $this->execute()) {

            switch (false) {
                case isset($response['ratingData']):
                case isset($response['ratingData']['customerService']):
                case isset($response['ratingData']['deliverySpeed']):
                case isset($response['ratingData']['description']):
                case isset($response['ratingData']['overall']):
                case isset($response['ratingData']['quality']):
                case isset($response['ratingData']['buyerID']['peerID']):
                case isset($response['ratingData']['timestamp']['seconds']):
                    return false;
            }

            return $response;
        }

        return false;
    }
}
