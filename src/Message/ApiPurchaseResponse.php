<?php

namespace Omnipay\NetPay\Message;

use Omnipay\Common\Message\RequestInterface;

/**
 * NetPay API Purchase Response
 */
class ApiPurchaseResponse extends Response
{
    public function __construct(RequestInterface $request, $data)
    {
        parent::__construct($request, $data);
    }

    public function getTransactionReference()
    {
        return ((isset($this->data['result']) && $this->data['result'] === 'SUCCESS')?$this->data['transaction']['transaction_id']:null);
    }

    public function getMessage()
    {
        if(isset($this->data->error)) {
            return $this->data->error->explanation;
        }
        return ((isset($this->data->result) && $this->data->result === 'SUCCESS' && isset($this->data->response->acquirer_message))?$this->data->response->acquirer_message:null);
    }
    
    public function getCode()
    {
        return ((isset($this->data->result) && $this->data->result === 'SUCCESS')?$this->data->response->gateway_code:null);
    }
    
    public function getAuthorizationCode()
    {
        return ((isset($this->data->transaction) && isset($this->data->transaction->authorization_code))?$this->data->transaction->authorization_code:null);
    }
    
    public function getReceipt()
    {
        return ((isset($this->data->transaction) && isset($this->data->transaction->receipt))?$this->data->transaction->receipt:null);
    }
    
    public function getOrderId()
    {
        return ((isset($this->data->order) && isset($this->data->order->order_id))?$this->data->order->order_id:null);
    }
    
    public function getTotalAuthorizedAmount()
    {
        return ((isset($this->data->order) && isset($this->data->order->total_authorized_amount))?$this->data->order->total_authorized_amount:null);
    }
    
    public function getTotalCapturedAmount()
    {
        return ((isset($this->data->order) && isset($this->data->order->total_captured_amount))?$this->data->order->total_captured_amount:null);
    }
    
    public function getRefundedAmount()
    {
        return ((isset($this->data->order) && isset($this->data->order->total_refunded_amount))?$this->data->order->total_refunded_amount:null);
    }
    
    public function getGatewayCode()
    {
        return ((isset($this->data->order) && isset($this->data->response->gateway_code))?$this->data->response->gateway_code:null);
    }
    
    public function getCSCCode()
    {
        return ((isset($this->data->order) && isset($this->data->response->cardsecurity) && isset($this->data->response->cardsecurity->acquirer_code))?$this->data->response->cardsecurity->acquirer_code:null);
    }
    
    public function getCSCGatewayCode()
    {
        return ((isset($this->data->order) && isset($this->data->response->cardsecurity) && isset($this->data->response->cardsecurity->gateway_code))?$this->data->response->cardsecurity->gateway_code:null);
    }
    
    public function getAVSCode()
    {
        return ((isset($this->data->order) && isset($this->data->response->cardholder_verification) && isset($this->data->response->cardholder_verification->avs_acquirer_code))?$this->data->response->cardholder_verification->avs_acquirer_code:null);
    }
    
    public function getAVSGatewayCode()
    {
        return ((isset($this->data->order) && isset($this->data->response->cardholder_verification) && isset($this->data->response->cardholder_verification->avs_gateway_code))?$this->data->response->cardholder_verification->avs_gateway_code:null);
    }
}
