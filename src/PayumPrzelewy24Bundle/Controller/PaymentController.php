<?php

namespace Umbrella\PayumPrzelewy24Bundle\Controller;

use FOS\RestBundle\Controller\Annotations\Route;
use Payum\Bundle\PayumBundle\Controller\PayumController;
use Payum\Core\Request\GetHumanStatus;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Umbrella\PayumPrzelewy24Bundle\Entity\Payment;

/**
 * @Route("/payment")
 */
class PaymentController extends PayumController
{
    /**
     * @Route("/create", name="create_payment")
     * @Method("GET")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createPayment(Request $request)
    {

        $storage = $this->get('payum')->getStorage(Payment::class);

        /** @var Payment $payment */
        $payment = $storage->create();
        $payment->setNumber(uniqid());
        $payment->setCurrencyCode('PLN');
        $payment->setTotalAmount(100);
        $payment->setDescription('Description');
        $payment->setClientId($this->getUser()->getId());
        $payment->setClientEmail($this->getUser()->getEmail());

        $payment->setDetails([
            'p24_session_id' => $payment->getNumber(),
            'p24_opis' => $payment->getDescription(),
            'p24_kwota' => $payment->getTotalAmount(),
            'p24_email' => $payment->getClientEmail()
        ]);

        $storage->update($payment);

        $captureToken = $this->get('payum')->getTokenFactory()->createCaptureToken(
            'przelewy24',
            $payment,
            'payment_done'
        );

        return $this->redirect($captureToken->getTargetUrl());
    }

    /**
     * @Route("/done", name="payment_done")
     * @param Request $request
     * @return Response
     */
    public function captureDoneAction(Request $request)
    {
        $token = $this->getPayum()->getHttpRequestVerifier()->verify($request);

        $identity = $token->getDetails();
        /** @var Payment $model */
        $model = $this->get('payum')->getStorage($identity->getClass())->find($identity);

        return new JsonResponse([
           'status' => $model->getStatus()
        ]);
    }
}
