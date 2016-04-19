<?php

namespace Omnipay\NetPay\Message;

/**
 * NetPay API Delete Token Request
 */
class ApiTokenDeleteRequest extends AbstractRequest
{
    public function getData()
    {
        $this->setApiMethod('gateway/token');
        
        $this->validate('token');

        $data = $this->getBaseData();
        
        $data['merchant']['operation_type'] = 'DELETE_TOKEN';
        
        $data['transaction']['source'] = 'INTERNET';
        
        $data['payment_source']['type'] = 'TOKEN';
        $data['payment_source']['token'] = $this->getToken();

        return $data;
    }

    protected function createResponse($data)
    {
        return $this->response = new ApiTokenDeleteResponse($this, $data);
    }
}