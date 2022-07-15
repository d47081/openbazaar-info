<?php

class CurlProfile extends Curl {

    public function follow($peerId, $timeout) {

        $this->prepare('/ob/follow', 'POST', $timeout, [
            'id' => $peerId,
        ]);

        return $this->execute();
    }

    public function isFollowing($peerId, $timeout) {

        $this->prepare('/ob/isfollowing/' . $peerId, 'GET', $timeout);

        if ($response = $this->execute()) {
            if (isset($response['isFollowing'])) {
                return (bool) $response['isFollowing'];
            }
        }

        return 0;
    }

    public function getConfig($timeout) {

        $this->prepare('/ob/config', 'GET', $timeout);

        if ($response = $this->execute()) {

            switch (false) {
                case isset($response['peerID']):
                case isset($response['testnet']):
                case isset($response['tor']):
                case isset($response['wallets']) && is_array($response['wallets']):
                    return false;
            }

            return $response;
        }

        return false;
    }

    public function getPeers($timeout) {

        $this->prepare('/ob/peers', 'GET', $timeout);

        if ($response = $this->execute()) {

            $peers = [];
            foreach ($response as $peerId) {
                //if (46 == strlen($peerId)) {
                    $peers[] = $peerId;
                //}
            }

            return $peers;
        }

        return false;
    }

    public function getPeerIps($peerId, $timeout) {

        $this->prepare('/ob/peerinfo/' . $peerId, 'GET', $timeout);

        if ($response = $this->execute()) {

            if (isset($response['Addrs']) && isset($response['ID']) && $response['ID'] == $peerId) {

                $result = [];
                foreach ($response['Addrs'] as $address) {

                    $parts = explode('/', $address);

                    if (count($parts) >= 4 && filter_var($parts[2], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                        $result[] = [
                            'version'  => $parts[1],
                            'ip'       => $parts[2],
                            'protocol' => $parts[3],
                            'port'     => $parts[4],
                        ];
                    }
                }
                return $result;
            }
        }

        return false;
    }

    public function getProfile($peerId, $timeout) {

        $this->prepare('/ob/profile/' . $peerId, 'GET', $timeout);

        if ($response = $this->execute()) {

            switch (false) {
                case isset($response['peerID']):
                case isset($response['handle']):
                case isset($response['name']):
                case isset($response['location']):
                case isset($response['about']):
                case isset($response['shortDescription']):
                case isset($response['nsfw']):
                case isset($response['vendor']):
                case isset($response['moderator']):
                case isset($response['contactInfo']):
                case isset($response['bitcoinPubkey']):
                case isset($response['lastModified']):
                case isset($response['version']):

                    return false;
            }

            return $response;
        }

        return false;
    }

    public function getProfileRating($peerId, $timeout) {

        $this->prepare('/ob/ratings/' . $peerId, 'GET', $timeout);

        if ($response = $this->execute()) {

            switch (false) {
                case isset($response['average']):
                case isset($response['count']):
                case isset($response['ratings']):

                    return false;
            }

            $ratings = [];
            foreach ($response['ratings'] as $value) {
                //if (46 == strlen($value)) {
                    $ratings[] = $value;
                //}
            }

            $response['ratings'] = $ratings;

            return $response;
        }

        return false;
    }

    public function getProfileFollowing($peerId, $timeout) {

        $this->prepare('/ob/following/' . $peerId, 'GET', $timeout);

        if ($response = $this->execute()) {

            $peers = [];
            foreach ($response as $peerId) {
                //if (46 == strlen($peerId)) {
                    $peers[] = $peerId;
                //}
            }

            return $peers;
        }

        return false;
    }

    public function getProfileFollowers($peerId, $timeout) {

        $this->prepare('/ob/followers/' . $peerId, 'GET', $timeout);

        if ($response = $this->execute()) {

            $peers = [];
            foreach ($response as $peerId) {
                //if (46 == strlen($peerId)) {
                    $peers[] = $peerId;
                //}
            }

            return $peers;
        }

        return false;
    }
}
