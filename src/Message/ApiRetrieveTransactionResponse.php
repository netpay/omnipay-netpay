<?php

namespace Omnipay\NetPay\Message;

use Omnipay\Common\Message\RequestInterface;

/**
 * NetPay API Retrieve Transaction Response
 */
class ApiRetrieveTransactionResponse extends ApiPurchaseResponse
{
    public function __construct(RequestInterface $request, $data)
    {
        parent::__construct($request, $data);
    }
}
