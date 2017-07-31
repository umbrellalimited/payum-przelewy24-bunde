<?php

namespace Umbrella\PayumPrzelewy24Bundle\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\GetHumanStatus;
use Umbrella\PayumPrzelewy24Bundle\Api\ApiClient;

class Status implements ActionInterface
{
    /**
     * @param mixed $request
     *
     * @throws \Payum\Core\Exception\RequestNotSupportedException if the action dose not support the request.
     */
    public function execute($request)
    {
        /** @var PaymentInterface $payment */
        $payment = $request->getFirstModel();

        $details = ArrayObject::ensureArrayObject($payment->getDetails());

        if ($details['state'] == ApiClient::STATUS_SUCCESS) {
            $request->markCaptured();
            return;
        }

        if ($details['state'] == ApiClient::STATUS_FAILED) {
            $request->markFailed();
            return;
        }

        $request->markUnknown();
    }

    /**
     * @param mixed $request
     *
     * @return boolean
     */
    public function supports($request)
    {
        return $request instanceof GetHumanStatus
            && $request->getModel() instanceof ArrayObject
            && $request->getFirstModel() instanceof PaymentInterface;
    }
}
