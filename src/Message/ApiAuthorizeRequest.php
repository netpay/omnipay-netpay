<?php

namespace Omnipay\NetPay\Message;

/**
 * NetPay API Authorize Request
 */
class ApiAuthorizeRequest extends ApiPurchaseRequest
{
    public function getData()
    {
        $data = parent::getData();

        $data['merchant']['operation_type'] = 'AUTHORIZE';
        
        return $data;
    }
}