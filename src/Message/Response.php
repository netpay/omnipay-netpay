<?php

namespace Omnipay\NetPay\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;

/**
 * NetPay Response
 */
class Response extends AbstractResponse
{
    public function __construct(RequestInterface $request, $data)
    {
        $this->request = $request;
        if($this->request->getContentType() === 'JSON') {
            $this->data = json_decode($data);
        }
        elseif($this->request->getContentType() === 'XML') {
            $obj = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
            $this->data = @json_decode(@json_encode($obj), 1);
        }
    }

    public function isSuccessful()
    {
        return (isset($this->data->result) && $this->data->result === 'SUCCESS');
    }
    
    public function getMessage()
    {
        if(isset($this->data->error) && isset($this->data->error->explanation)) {
            return $this->data->error->explanation;
        }
        
        return null;
    }
}
