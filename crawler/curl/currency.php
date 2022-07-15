<?php

class CurlCurrency extends Curl {

    public function getRates($timeout) {

        $this->prepare('/api', 'GET', $timeout);

        if ($response = $this->execute()) {

            $currencies = [];
            foreach ($response as $key => $value) {
                if (isset($value['ask']) &&
                    isset($value['bid']) &&
                    isset($value['last']) &&
                    isset($value['type'])) {

                    $currencies[$key] = $value;
                }
            }

            return $currencies;
        }

        return false;
    }
}
