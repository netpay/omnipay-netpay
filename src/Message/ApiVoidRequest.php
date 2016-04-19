<?php

namespace Omnipay\NetPay\Message;

/**
 * NetPay API Void Request
 */
class ApiVoidRequest extends AbstractRequest
{
    public function getTransactionId()
    {
        return $this->getParameter('transactionId');
    }

    public function setTransactionId($value)
    {
        return $this->setParameter('transactionId', $value);
    }
    
    public function getVoidTransactionId()
    {
        return $this->getParameter('voidTransactionId');
    }

    public function setVoidTransactionId($value)
    {
        return $this->setParameter('voidTransactionId', $value);
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
        
        $data['merchant']['operation_type'] = 'VOID';
        
        $data['transaction']['transaction_id'] = $this->getTransactionId();
        $data['transaction']['void_transaction_id'] = $this->getVoidTransactionId();
        $data['transaction']['source'] = 'INTERNET';
        $description = trim(substr($this->getDescription(), 0, 100));
        if(strlen($description) === 0) {
            $description = substr("Void of transaction id ".$this->getTransactionId()." has been requested.", 0, 100);
        }
        $data['transaction']['description'] = $description;
        
        $data['order']['order_id'] = $this->getOrderId();

        return $data;
    }

    protected function createResponse($data)
    {
        return $this->response = new ApiVoidResponse($this, $data);
    }
}