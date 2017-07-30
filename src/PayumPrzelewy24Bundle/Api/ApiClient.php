<?php

namespace Umbrella\PayumPrzelewy24Bundle\Api;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Model\PaymentInterface;


class ApiClient{

    const STATUS_SUCCESS = 'TRUE';
    const STATUS_FAILED = 'err00';

    const CURRENCY = 'PLN';

    /** @var ClientInterface */
    private $httpClient;

    /**
     * @param ClientInterface $httpClient
     * @param array $parameters
     */
    public function __construct(ClientInterface $httpClient, $parameters = [])
    {
        $this->httpClient = $httpClient;
        $this->parameters = $parameters;
    }

    public function getNewPaymentUrl()
    {
        return $this->parameters['sandbox'] ?
            "https://sandbox.przelewy24.pl/index.php" :
            "https://secure.przelewy24.pl/index.php";
    }

    public function getStatusPaymentUrl()
    {
        return $this->parameters['sandbox'] ?
            "https://sandbox.przelewy24.pl/transakcja.php" :
            "https://przelewy24.pl/transakcja.php";
    }

    public function buildFormParamsForPostRequest(PaymentInterface $payment, TokenInterface $token)
    {
        $details = $payment->getDetails();

        $params = [
            'p24_session_id' => $details['p24_session_id'],
            'p24_opis' => $details['p24_opis'],
            'p24_id_sprzedawcy' => $this->parameters['clientId'],
            'p24_kwota' => $details['p24_kwota'],
            'p24_email' => $details['p24_email'],
            'p24_return_url_ok' => sprintf('%s/payment/capture/%s', $this->parameters['returnUrl'], $token->getHash()),
            'p24_return_url_error' => sprintf('%s/payment/capture/%s', $this->parameters['returnUrl'], $token->getHash()),
            'p24_sign' => $this->createHashForNewPayment($details)
        ];

        return $params;
    }

    public function getPaymentStatus(ArrayObject $notificationResponse)
    {
        if (!isset($notificationResponse['p24_session_id']) || !isset($notificationResponse['p24_order_id']) ||
            !isset($notificationResponse['p24_kwota'])) {
            throw new \InvalidArgumentException("Missing one of parameter.");
        }

        try {
            $response = $this->httpClient->post(
                $this->getStatusPaymentUrl(), [
                    'form_params' => [
                        'p24_id_sprzedawcy' => $this->parameters['clientId'],
                        'p24_session_id' => $notificationResponse['p24_session_id'],
                        'p24_order_id' => $notificationResponse['p24_order_id'],
                        'p24_kwota' => $notificationResponse['p24_kwota'],
                        'p24_sign' => $this->createHashForPaymentStatus(
                            $notificationResponse->toUnsafeArray()
                        )
                    ]
                ]
            );

            return $this->parseResponse($response->getBody());

        } catch (RequestException $requestException) {
            throw new \RuntimeException($requestException->getMessage());
        }
    }

    private function createHashForPaymentStatus(array $details)
    {
        return $this->createHash(
            $details,
            $details['p24_order_id']
        );
    }

    private function createHashForNewPayment(array $details)
    {
        return $this->createHash(
            $details,
            $this->parameters['clientId']
        );
    }

    private function createHash(array $details, $gatewayIdOrOrderId)
    {
        return md5(
            $details['p24_session_id'] . '|' .
            $gatewayIdOrOrderId . '|' .
            $details['p24_kwota'] . '|' .
            self::CURRENCY . '|' .
            $this->parameters['clientSecret']
        );
    }

    private function parseResponse($response)
    {
        $responseArray = explode("\n", $response);
        if (count($responseArray) > 2) {
            $code = $responseArray[2];
        } else {
            $code = $responseArray[1];
        }

        return $code;
    }
}
