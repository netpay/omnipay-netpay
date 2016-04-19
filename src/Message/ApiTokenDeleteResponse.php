<?php

namespace Omnipay\NetPay\Message;

use Omnipay\Common\Message\RequestInterface;

/**
 * NetPay API Delete Token Response
 */
class ApiTokenDeleteResponse extends Response
{
    public function __construct(RequestInterface $request, $data)
    {
        parent::__construct($request, $data);
    }
}
