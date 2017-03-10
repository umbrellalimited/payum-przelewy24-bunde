<?php

namespace Umbrella\PayumPrzelewy24Bundle\Factory;

use GuzzleHttp\Client;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use Umbrella\PayumPrzelewy24Bundle\Api\ApiClient;

class Przelewy24OffsiteGatewayFactory extends GatewayFactory {

    protected function populateConfig(ArrayObject $config)
    {
        if (false == $config['payum.api']) {
            $config['payum.default_options'] = [
                'clientId' => null,
                'clientSecret' => null,
                'sandbox' => true
            ];

            $config['payum.http_client'] = new Client();

            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = ['clientId', 'clientSecret', 'returnUrl'];

            $config['httplug.client'] = new Client();

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                return new ApiClient(
                    $config['payum.http_client'], [
                    'clientId' => $config['clientId'],
                    'clientSecret' => $config['clientSecret'],
                    'returnUrl' => $config['returnUrl'],
                    'sandbox' => $config['sandbox']
                ]);
            };
        }
    }
}
