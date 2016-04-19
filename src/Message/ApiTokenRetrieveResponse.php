<?php

namespace Omnipay\NetPay\Message;

use Omnipay\Common\Message\RequestInterface;

/**
 * NetPay API Retrieve Token Response
 */
class ApiTokenRetrieveResponse extends Response
{
    public function __construct(RequestInterface $request, $data)
    {
        parent::__construct($request, $data);
    }
}
