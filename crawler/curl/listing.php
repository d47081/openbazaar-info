<?php

class CurlListing extends Curl {

    public function getListings($peerId, $timeout) {

        $this->prepare('/ob/listings/' . $peerId, 'GET', $timeout);

        if ($response = $this->execute()) {

            $listings = [];
            foreach ($response as $listing) {

                # @TODO validate
                if (isset($listing['hash'])) {
                    $listings[] = $listing;
                }
            }

            return $listings;
        }

        return false;
    }

    public function getIpnsListing($peerId, $slug, $timeout) {

        $this->prepare('/ob/listing/' . $peerId . '/' . $slug, 'GET', $timeout);

        if ($response = $this->execute()) {

            # @TODO validate
            switch (false) {
                case isset($response['hash']):
                case isset($response['signature']):
                case isset($response['listing']):
                case isset($response['listing']['moderators']) && is_array($response['listing']['moderators']):
                case isset($response['listing']['metadata']['version']):
                case isset($response['listing']['metadata']['contractType']):
                case isset($response['listing']['metadata']['format']):
                case isset($response['listing']['metadata']['expiry']):
                case isset($response['listing']['metadata']['acceptedCurrencies']):
                case isset($response['listing']['metadata']['escrowTimeoutHours']):
                case isset($response['listing']['metadata']['coinType']):
                case isset($response['listing']['metadata']['coinDivisibility']):
                case isset($response['listing']['metadata']['priceModifier']):
                case isset($response['listing']['item']['price']):
                case isset($response['listing']['item']['bigPrice']):
                case isset($response['listing']['metadata']['pricingCurrency']):
                case isset($response['listing']['item']['priceCurrency']['code']):
                case isset($response['listing']['item']['priceCurrency']['divisibility']):
                case isset($response['listing']['item']['condition']):
                case isset($response['listing']['item']['nsfw']):
                case isset($response['listing']['item']['title']):
                case isset($response['listing']['item']['description']):
                case isset($response['listing']['item']['tags']):
                case isset($response['listing']['item']['categories']):
                case isset($response['listing']['item']['grams']):
                case isset($response['listing']['item']['processingTime']):
                case isset($response['listing']['item']['images']) && is_array($response['listing']['item']['images']):
                case isset($response['listing']['termsAndConditions']):
                case isset($response['listing']['refundPolicy']):
                case isset($response['listing']['vendorID']['pubkeys']['identity']):
                case isset($response['listing']['vendorID']['pubkeys']['bitcoin']):
                case isset($response['listing']['vendorID']['bitcoinSig']):

                    return false;
            }

            return $response;
        }

        return false;
    }

    public function getIpfsListing($hash, $timeout) {

        $this->prepare('/ob/listing/ipfs/' . $hash, 'GET', $timeout);

        if ($response = $this->execute()) {

            # @TODO validate
            switch (false) {
                case isset($response['hash']):
                case isset($response['listing']):
                case isset($response['listing']['metadata']['version']):
                case isset($response['listing']['metadata']['contractType']):
                case isset($response['listing']['metadata']['format']):
                case isset($response['listing']['metadata']['expiry']):
                case isset($response['listing']['item']['price']):
                case isset($response['listing']['item']['bigPrice']):
                case isset($response['listing']['metadata']['pricingCurrency']):
                case isset($response['listing']['item']['priceCurrency']['code']):
                case isset($response['listing']['item']['condition']):
                case isset($response['listing']['item']['nsfw']):
                case isset($response['listing']['item']['title']):
                case isset($response['listing']['item']['description']):
                case isset($response['listing']['item']['tags']):
                case isset($response['listing']['item']['categories']):

                    return false;
            }

            return $response;
        }

        return false;
    }
}
