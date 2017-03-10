# README #

1. Create services in services.yml
```
    em.payment.przelewy24.capture_offsite:
        class: Umbrella\PayumPrzelewy24Bundle\Action\CaptureOffsite

    em.payment.przelewy24.status:
        class: Umbrella\PayumPrzelewy24Bundle\Action\Status

    em.payment.przelewy24.notify:
        class: Umbrella\PayumPrzelewy24Bundle\Action\Notify
        arguments:
          - '@repository.payment'
          - '@doctrine.orm.default_entity_manager'
```

2. Create przelewy24 gateway

```
    em.payment.przelewy24.gateway_factory:
        class: Payum\Core\Bridge\Symfony\Builder\GatewayFactoryBuilder
        arguments: ['Umbrella\PayumPrzelewy24Bundle\Factory\Przelewy24OffsiteGatewayFactory']
        tags:
          - { name: payum.gateway_factory_builder, factory: przelewy24 }
```

3. Add configuration for payum to config.yml
```
payum:
    security:
        token_storage:
            AppBundle\Entity\PaymentToken: { doctrine: orm }

    storages:
        AppBundle\Entity\Payment: { doctrine: orm }

    gateways:
        przelewy24:
            factory: przelewy24
            sandbox: true
            clientId: %clientId%
            clientSecret: %clientSecret% 
            returnUrl: %returnUrl% #http://localhost
            payum.action.status: '@em.payment.przelewy24.status'
            payum.action.capture_offsite: '@em.payment.przelewy24.capture_offsite'
            payum.action.notify: '@em.payment.przelewy24.notify'
```
