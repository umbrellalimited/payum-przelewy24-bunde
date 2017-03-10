<?php

namespace Umbrella\PayumPrzelewy24Bundle\Api;

use Payum\Core\Exception\UnsupportedApiException;

trait ApiAwareTrait {

    /** @var ApiClient */
    protected $api;

    public function setApi($api) {
        if ($api instanceof ApiClient) {
            $this->api = $api;
            return;
        }

        throw new UnsupportedApiException();
    }
}
