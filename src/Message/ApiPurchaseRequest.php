<?php

namespace Omnipay\NetPay\Message;

/**
 * NetPay API Purchase Request
 */
class ApiPurchaseRequest extends AbstractRequest
{
    public function getCvv()
    {
        return $this->getParameter('cvv');
    }

    public function setCvv($value)
    {
        return $this->setParameter('cvv', $value);
    }
    
    public function getData()
    {
        $this->setApiMethod('gateway/transaction');
        
        $this->validate('amount', 'currency');

        $data = $this->getBaseData();

        $data['merchant']['operation_type'] = 'PURCHASE';

        $data['transaction']['transaction_id'] = $this->createUniqueTransactionId($this->getTransactionId());
        $data['transaction']['amount'] = $this->getAmount();
        $data['transaction']['currency'] = $this->getCurrency();
        $data['transaction']['source'] = 'INTERNET';
        $description = trim(substr($this->getDescription(), 0, 100));
        if(strlen($description) === 0) {
            $description = substr("New order with transaction id ".$this->getTransactionId()." and amount ".$this->getCurrency()." ".$this->getAmount()." has been placed.", 0, 100);
        }
        $data['transaction']['description'] = $description;
        
        $uagent = $this->getBrowser();
        $customer = array();
        
        if(!is_null($this->getToken())) {
            $this->validate('token', 'cvv');
            $data['payment_source']['type'] = 'TOKEN';
            $data['payment_source']['token'] = $this->getToken();
            $data['payment_source']['card'] = array(
                'security_code' => $this->getCvv(),
            );
        }
        else {
            $this->validate('card');
            $this->getCard()->validate();

            $data['payment_source']['type'] = 'CARD';
            $data['payment_source']['card'] = array(
                'number' => $this->getCard()->getNumber(),
                'expiry_month' => $this->getCard()->getExpiryDate('m'),
                'expiry_year' => $this->getCard()->getExpiryDate('y'),
                'security_code' => $this->getCard()->getCvv(),
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

            $billing = array();
            $billing['bill_to_company']         = trim(substr(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', strip_tags($this->getCard()->getBillingCompany())), 0, 100));
            $billing['bill_to_address']         = trim(substr(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', strip_tags($this->getCard()->getBillingAddress1().' '.$this->getCard()->getBillingAddress2())), 0, 100));
            $billing['bill_to_town_city']       = trim(substr(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', strip_tags($this->getCard()->getBillingCity())), 0, 50));
            $billing['bill_to_county']          = trim(substr(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', strip_tags($this->getCard()->getBillingState())), 0, 50));
            $billing['bill_to_postcode']        = trim(substr(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', strip_tags($this->getCard()->getBillingPostcode())), 0, 10));
            $billing['bill_to_country']         = trim(substr(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', strip_tags($this->getValidCountryCode($this->getCard()->getBillingCountry()))), 0, 3));
            $billing = array_filter($billing);

            if(count($billing) > 0) {
                $data['billing'] = $billing;
            }


            $shipping = array();
            $shipping['ship_to_title']          = trim(substr(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', strip_tags($this->getCard()->getShippingTitle())), 0, 20));
            $shipping['ship_to_firstname']      = trim(substr(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', strip_tags($this->getCard()->getShippingFirstName())), 0, 50));
            $shipping['ship_to_lastname']       = trim(substr(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', strip_tags($this->getCard()->getShippingLastName())), 0, 50));
            $shipping['ship_to_fullname']       = trim(substr(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', strip_tags($this->getCard()->getShippingName())), 0, 100));
            $shipping['ship_to_company']        = trim(substr(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', strip_tags($this->getCard()->getShippingCompany())), 0, 100));
            $shipping['ship_to_address']        = trim(substr(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', strip_tags($this->getCard()->getShippingAddress1().' '.$this->getCard()->getShippingAddress2())), 0, 100));
            $shipping['ship_to_town_city']      = trim(substr(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', strip_tags($this->getCard()->getShippingCity())), 0, 50));
            $shipping['ship_to_county']         = trim(substr(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', strip_tags($this->getCard()->getShippingState())), 0, 50));
            $shipping['ship_to_postcode']       = trim(substr(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', strip_tags($this->getCard()->getShippingPostcode())), 0, 10));
            $shipping['ship_to_country']        = trim(substr(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', strip_tags($this->getValidCountryCode($this->getCard()->getShippingCountry()))), 0, 3));
            $shipping['ship_to_phone']          = trim(substr(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', strip_tags($this->getCard()->getShippingPhone())), 0, 50));
            $shipping = array_filter($shipping);

            if(count($shipping) > 0) {
                $data['shipping'] = $shipping;
            }
            
            $customer['customer_email']         = trim(substr(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', strip_tags($this->getCard()->getEmail())), 0, 50));
            $customer['customer_phone']         = trim(substr(preg_replace('/[^0-9]/', '', strip_tags($this->getCard()->getPhone())), 0, 20));
        }
        
        $customer['customer_ip_address']    = trim(substr($_SERVER['REMOTE_ADDR'], 0, 15));
        $customer['customer_hostname']      = trim(substr($_SERVER['HTTP_HOST'], 0, 60));
        $customer['customer_browser']       = trim(substr($uagent['name'], 0, 200));
        $customer = array_filter($customer);
        
        if(count($customer) > 0) {
            $data['customer'] = $customer;
        }

        return $data;
    }

    protected function createResponse($data)
    {
        return $this->response = new ApiPurchaseResponse($this, $data);
    }
}