<?php

namespace Omnipay\NetPay\Message;

/**
 * NetPay API Create Token Request
 */
class ApiTokenCreateRequest extends AbstractRequest
{
    public function getTokenPermanent()
    {
        return $this->getParameter('tokenPermanent');
    }

    public function setTokenPermanent($value)
    {
        if($value) {
            return $this->setParameter('tokenPermanent', TRUE);
        }
        else {
            return $this->setParameter('tokenPermanent', FALSE);
        }
    }
    
    public function getData()
    {
        $this->setApiMethod('gateway/token');
        
        $this->validate('card');
        $this->getCard()->validate();

        $data = $this->getBaseData();
        
        $data['merchant']['operation_type'] = 'CREATE_TOKEN';
        
        $data['transaction']['source'] = 'INTERNET';
        
        $data['payment_source']['type'] = 'CARD';
        $data['payment_source']['card'] = array(
            'number' => $this->getCard()->getNumber(),
            'expiry_month' => $this->getCard()->getExpiryDate('m'),
            'expiry_year' => $this->getCard()->getExpiryDate('y'),
        );
        
        $card_type = $this->getCardType($this->getCard()->getBrand());
        if($card_type !== '') {
            $data['payment_source']['card']['card_type'] = $card_type;
        }
        
        if(strlen($this->getCard()->getFirstName()) === 0) {
            throw new \Omnipay\Common\Exception\InvalidCreditCardException("The First Name parameter is required");
        }
        
        if(strlen($this->getCard()->getLastName()) === 0) {
            throw new \Omnipay\Common\Exception\InvalidCreditCardException("The Last Name parameter is required");
        }
        
        $data['payment_source']['card']['holder'] = array(
            'firstname' => trim(substr($this->getCard()->getFirstName(), 0, 50)),
            'lastname' => trim(substr($this->getCard()->getLastName(), 0, 50)),
            'fullname' => trim(substr($this->getCard()->getFirstName() . ' ' . $this->getCard()->getLastName(), 0, 100)),
        );
        
        $title = $this->getCard()->getTitle();
        if(strlen($title) !== 0) {
            $data['payment_source']['card']['holder']['title'] = trim(substr($title, 0, 20));
        }
        
        $data['token_mode'] = ($this->getTokenPermanent())?'PERMANENT':'TEMPORARY';

        return $data;
    }

    protected function createResponse($data)
    {
        return $this->response = new ApiTokenCreateResponse($this, $data);
    }
}