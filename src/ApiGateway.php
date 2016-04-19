<?php

namespace Omnipay\NetPay;

use Omnipay\Common\AbstractGateway;

/**
 * NetPay API Connection Class
 */
class ApiGateway extends AbstractGateway
{
    public function getName()
    {
        return 'NetPay API Payment Gateway';
    }

    public function getDefaultParameters()
    {
        return array(
            'liveEndpoint' => 'https://integration.revolution.netpay.co.uk/v1/',
            'testEndpoint' => 'https://integrationtest.revolution.netpay.co.uk/v1/',
            'apiMethod' => '',
            'merchantId' => '',
            'username' => '',
            'password' => '',
            'testMode' => false,
            'certificatePath' => '',
            'certificateKeyPath' => '',
            'certificatePassword' => '',
            'contentType' => 'JSON', //XML or JSON
        );
    }

    public function getLiveEndpoint()
    {
        return $this->getParameter('liveEndpoint');
    }

    public function setLiveEndpoint($value)
    {
        return $this->setParameter('liveEndpoint', $value);
    }

    public function getTestEndpoint()
    {
        return $this->getParameter('testEndpoint');
    }

    public function setTestEndpoint($value)
    {
        return $this->setParameter('testEndpoint', $value);
    }

    public function getMerchantId()
    {
        return $this->getParameter('merchantId');
    }

    public function setMerchantId($value)
    {
        return $this->setParameter('merchantId', $value);
    }

    public function getUsername()
    {
        return $this->getParameter('username');
    }

    public function setUsername($value)
    {
        return $this->setParameter('username', $value);
    }

    public function getPassword()
    {
        return $this->getParameter('password');
    }

    public function setPassword($value)
    {
        return $this->setParameter('password', $value);
    }

    public function getCertificatePath()
    {
        return $this->getParameter('certificatePath');
    }

    public function setCertificatePath($value)
    {
        return $this->setParameter('certificatePath', $value);
    }

    public function getCertificateKeyPath()
    {
        return $this->getParameter('certificateKeyPath');
    }

    public function setCertificateKeyPath($value)
    {
        return $this->setParameter('certificateKeyPath', $value);
    }

    public function getCertificatePassword()
    {
        return $this->getParameter('certificatePassword');
    }

    public function setCertificatePassword($value)
    {
        return $this->setParameter('certificatePassword', $value);
    }

    public function getContentType()
    {
        return $this->getParameter('contentType');
    }

    public function setContentType($value)
    {
        return $this->setParameter('contentType', $value);
    }

    public function purchase(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\NetPay\Message\ApiPurchaseRequest', $parameters);
    }

    public function authorize(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\NetPay\Message\ApiAuthorizeRequest', $parameters);
    }

    public function capture(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\NetPay\Message\ApiCaptureRequest', $parameters);
    }

    public function refund(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\NetPay\Message\ApiRefundRequest', $parameters);
    }

    public function retrieveTransaction(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\NetPay\Message\ApiRetrieveTransactionRequest', $parameters);
    }

    public function void(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\NetPay\Message\ApiVoidRequest', $parameters);
    }

    public function createCard(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\NetPay\Message\ApiTokenCreateRequest', $parameters);
    }

    public function deleteCard(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\NetPay\Message\ApiTokenDeleteRequest', $parameters);
    }

    public function retrieveCard(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\NetPay\Message\ApiTokenRetrieveRequest', $parameters);
    }
}
