<?php

namespace Omnipay\NetPay\Message;

/**
 * NetPay API Retrieve Transaction Request
 */
class ApiRetrieveTransactionRequest extends AbstractRequest
{
    public function getTransactionId()
    {
        return $this->getParameter('transactionId');
    }

    public function setTransactionId($value)
    {
        return $this->setParameter('transactionId', $value);
    }
    
    public function getOrderId()
    {
        return $this->getParameter('orderId');
    }

    public function setOrderId($value)
    {
        return $this->setParameter('orderId', $value);
    }
    
    public function getData()
    {
        $this->setApiMethod('gateway/transaction');
        
        $this->validate('transactionId', 'orderId');

        $data = $this->getBaseData();
        
        $data['merchant']['operation_type'] = 'RETRIEVE';
        
        $data['transaction']['transaction_id'] = $this->getTransactionId();
        $data['transaction']['source'] = 'INTERNET';
        
        $data['order']['order_id'] = $this->getOrderId();

        return $data;
    }

    protected function createResponse($data)
    {
        return $this->response = new ApiRetrieveTransactionResponse($this, $data);
    }
}