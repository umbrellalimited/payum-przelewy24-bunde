<?php

namespace Umbrella\PayumPrzelewy24Bundle\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Request\Notify;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Umbrella\PayumPrzelewy24Bundle\Api\ApiAwareTrait;

class CaptureOffsite implements ApiAwareInterface, ActionInterface, GatewayAwareInterface
{
    use GenericTokenFactoryAwareTrait;
    use GatewayAwareTrait;
    use ApiAwareTrait;

    /**
     * @param mixed $request
     *
     * @throws \Payum\Core\Exception\RequestNotSupportedException if the action dose not support the request.
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $httpRequest = new GetHttpRequest();
        $this->gateway->execute($httpRequest);

        if ($httpRequest->request) {
            $model = new ArrayObject($httpRequest->request);
            $this->gateway->execute(new Notify($model));
        }
        else {
            throw new HttpPostRedirect(
                $this->api->getNewPaymentUrl(),
                $this->api->buildFormParamsForPostRequest($request->getFirstModel(), $request->getToken())
            );
        }
    }

    /**
     * @param GetHumanStatus $request
     *
     * @return boolean
     */
    public function supports($request)
    {
        return $request instanceof Capture
            && $request->getFirstModel() instanceof PaymentInterface;
    }
}
