<?php

class CurlImage extends Curl {

    public function getImage($imageHash, $timeout) {

        $this->prepare('/ob/images/' . $imageHash, 'GET', $timeout);

        if ($response = $this->execute(false)) {
            return $response;
        }

        return false;
    }
}
