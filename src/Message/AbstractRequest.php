<?php
/**
 * NetPay Abstract Request
 */

namespace Omnipay\NetPay\Message;


abstract class AbstractRequest extends \Omnipay\Common\Message\AbstractRequest
{

    public function getLiveEndpoint()
    {
        return $this->getParameter('liveEndpoint');
    }

    public function setLiveEndpoint($value)
    {
        return $this->setParameter('liveEndpoint', $value);
    }

    public function getTestEndpoint()
    {
        return $this->getParameter('testEndpoint');
    }

    public function setTestEndpoint($value)
    {
        return $this->setParameter('testEndpoint', $value);
    }
    
    public function getMerchantId()
    {
        return $this->getParameter('merchantId');
    }

    public function setMerchantId($value)
    {
        return $this->setParameter('merchantId', $value);
    }

    public function getUsername()
    {
        return $this->getParameter('username');
    }

    public function setUsername($value)
    {
        return $this->setParameter('username', $value);
    }

    public function getPassword()
    {
        return $this->getParameter('password');
    }

    public function setPassword($value)
    {
        return $this->setParameter('password', $value);
    }

    public function getCertificatePath()
    {
        return $this->getParameter('certificatePath');
    }

    public function setCertificatePath($value)
    {
        return $this->setParameter('certificatePath', $value);
    }

    public function getCertificateKeyPath()
    {
        return $this->getParameter('certificateKeyPath');
    }

    public function setCertificateKeyPath($value)
    {
        return $this->setParameter('certificateKeyPath', $value);
    }

    public function getCertificatePassword()
    {
        return $this->getParameter('certificatePassword');
    }

    public function setCertificatePassword($value)
    {
        return $this->setParameter('certificatePassword', $value);
    }

    public function getContentType()
    {
        return $this->getParameter('contentType');
    }

    public function setContentType($value)
    {
        return $this->setParameter('contentType', $value);
    }

    public function getApiMethod()
    {
        return $this->getParameter('apiMethod');
    }

    public function setApiMethod($value)
    {
        return $this->setParameter('apiMethod', $value);
    }

    protected function getBaseData()
    {
        $data = array();
        
        $data['merchant'] = [
            'merchant_id' => $this->getMerchantId(),
            'operation_mode' => (($this->getTestMode())?'2':'1')
        ];
        
        return $data;
    }

    public function sendData($data)
    {
        $url = $this->getEndpoint();
        $this->httpClient->setSslVerification(true, true, 2);
        $httpRequest = $this->httpClient->put($url.$this->getApiMethod(), null, null, ['exceptions' => FALSE, 
//                                                                                        'verify' => FALSE, 
//                                                                                        'debug' => TRUE
                                                                                        ]);
        
        //Set Content Type of request as requested
        if($this->getContentType() === 'JSON') {
            $httpRequest->setBody(json_encode($data), 'application/json');
        }
        elseif($this->getContentType() === 'XML') {
            $httpRequest->setBody($this->formatToXml($data), 'application/xml');
        }
        
//        $httpRequest->getCurlOptions()->set(CURLOPT_SSLVERSION, 6); // CURL_SSLVERSION_TLSv1_2 for libcurl < 7.35
        $httpRequest->setAuth($this->getUsername(), $this->getPassword());
        
        //Set SSL Authentication if provided
        if($this->getCertificatePath() !== '' && $this->getCertificateKeyPath() !== '') {
            $httpRequest->getCurlOptions()->set(CURLOPT_SSL_VERIFYPEER, false);
            $httpRequest->getCurlOptions()->set(CURLOPT_SSL_VERIFYHOST, 2);
            $httpRequest->getCurlOptions()->set(CURLOPT_SSLCERT, $this->getCertificatePath());
            $httpRequest->getCurlOptions()->set(CURLOPT_SSLKEY, $this->getCertificateKeyPath());

            // If there is password
            if ($this->getCertificatePassword()!='') {
                $httpRequest->getCurlOptions()->set(CURLOPT_SSLCERTPASSWD, $this->getCertificatePassword());
            }
        }
        
        try {
            $httpResponse = $httpRequest->send();
//            echo $httpResponse->getBody();
        }
        catch (\Guzzle\Http\Exception\BadResponseException $exc) {
//            print_r($httpRequest);
        }

        return $this->createResponse($httpResponse->getBody());
    }

    protected function getEndpoint()
    {
        return $this->getTestMode() ? $this->getTestEndpoint() : $this->getLiveEndpoint();
    }

    protected function createResponse($data)
    {
        return $this->response = new Response($this, $data);
    }
    
    /**
     * Creates XML string from passed data
     * 
     * @param array $data Array of data to be converted to XML
     * @param SimpleXMLElement $structure SimpleXMLElement to start XML from
     * @param string $basenode Name of main node of created XML
     * @return string XML string created from data
     */
    private function formatToXml($data = null, $structure = null, $basenode = 'xml') 
    {
        //Turn off compatibility mode as simple xml throws a wobbly if you don't.
        if (ini_get('zend.ze1_compatibility_mode') == 1) {
            ini_set('zend.ze1_compatibility_mode', 0);
        }

        if ($structure === null) {
            $structure = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><$basenode />");
        }

        //Force it to be something useful
        if (!is_array($data) AND !is_object($data)) {
            $data = (array) $data;
        }

        foreach ($data as $key => $value) {

            //Change false/true to 0/1
            if (is_bool($value)) {
                $value = (int) $value;
            }

            //No numeric keys in our xml please!
            if (is_numeric($key)) {
                //Make string key
                $key = ($this->singular($basenode) != $basenode) ? $this->singular($basenode) : 'item';
            }

            //Replace anything not alpha numeric
            $key = preg_replace('/[^a-z_\-0-9]/i', '', $key);

            //If there is another array found recursively call this function
            if (is_array($value) || is_object($value)) {
                $node = $structure->addChild($key);

                //Recursive call.
                $this->format_to_xml($value, $node, $key);
            }
            else {
                //Add single node.
                $value = htmlspecialchars(html_entity_decode($value, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, "UTF-8");

                $structure->addChild($key, $value);
            }
        }

        return $structure->asXML();
    }

    /**
     * Returns singular form of string
     * 
     * @param string $str String to make singular
     * @return string Word in singular form
     */
    public function singular($str) 
    {
        $result = strval($str);

        $singular_rules = array(
            '/(matr)ices$/' => '\1ix',
            '/(vert|ind)ices$/' => '\1ex',
            '/^(ox)en/' => '\1',
            '/(alias)es$/' => '\1',
            '/([octop|vir])i$/' => '\1us',
            '/(cris|ax|test)es$/' => '\1is',
            '/(shoe)s$/' => '\1',
            '/(o)es$/' => '\1',
            '/(bus|campus)es$/' => '\1',
            '/([m|l])ice$/' => '\1ouse',
            '/(x|ch|ss|sh)es$/' => '\1',
            '/(m)ovies$/' => '\1\2ovie',
            '/(s)eries$/' => '\1\2eries',
            '/([^aeiouy]|qu)ies$/' => '\1y',
            '/([lr])ves$/' => '\1f',
            '/(tive)s$/' => '\1',
            '/(hive)s$/' => '\1',
            '/([^f])ves$/' => '\1fe',
            '/(^analy)ses$/' => '\1sis',
            '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/' => '\1\2sis',
            '/([ti])a$/' => '\1um',
            '/(p)eople$/' => '\1\2erson',
            '/(m)en$/' => '\1an',
            '/(s)tatuses$/' => '\1\2tatus',
            '/(c)hildren$/' => '\1\2hild',
            '/(n)ews$/' => '\1\2ews',
            '/([^u])s$/' => '\1',
        );

        foreach ($singular_rules as $rule => $replacement) {
            if (preg_match($rule, $result)) {
                $result = preg_replace($rule, $replacement, $result);
                break;
            }
        }

        return $result;
    }

    /**
     * Add timestamp to transaction id
     */
    public function createUniqueTransactionId($transactionId)
    {
        //Get current time
        $time = time();
        //Split time in half and get both halves
        $time1 = substr($time, 0, floor(strlen($time)/2));
        $time2 = substr($time, floor(strlen($time)/2));
        //Get random 3 length string based on selected characters
        $rand = '';
        $seed = str_split('abcdefghijklmnopqrstuvwxyz'
             .'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
        foreach (array_rand($seed, 3) as $k) $rand .= $seed[$k];
        //Return unique transaction id with random characters included in the middle
        return strtolower($transactionId) . $time1 . $rand . $time2;
    }
    
    public function getCardType($brand_name)
    {
        switch ($brand_name) {
            case 'visa':
                return 'VISA';
                break;
            
            case 'mastercard':
                return 'MCRD';
                break;
            
            case 'amex':
                return 'AMEX';
                break;
            
            case 'diners_club':
                return 'DINE';
                break;
            
            case 'maestro':
                return 'MSTO';
                break;

            default:
                return '';
                break;
        }
    }

    // Function to get ISO Country Code for the 2 character country code
    function getValidCountryCode($code){
        if(strlen($code) === 3) {
            return $code;
        }
        $countries = array(
                'AF' => 'AFG',
                'AL' => 'ALB',
                'DZ' => 'DZA',
                'AD' => 'AND',
                'AO' => 'AGO',
                'AI' => 'AIA',
                'AQ' => 'ATA',
                'AG' => 'ATG',
                'AR' => 'ARG',
                'AM' => 'ARM',
                'AW' => 'ABW',
                'AU' => 'AUS',
                'AT' => 'AUT',
                'AZ' => 'AZE',
                'BS' => 'BHS',
                'BH' => 'BHR',
                'BD' => 'BGD',
                'BB' => 'BRB',
                'BY' => 'BLR',
                'BE' => 'BEL',
                'BZ' => 'BLZ',
                'BJ' => 'BEN',
                'BM' => 'BMU',
                'BT' => 'BTN',
                'BO' => 'BOL',
                'BA' => 'BIH',
                'BW' => 'BWA',
                'BV' => 'BVT',
                'BR' => 'BRA',
                'IO' => 'IOT',
                'VG' => 'VGB',
                'BN' => 'BRN',
                'BG' => 'BGR',
                'BF' => 'BFA',
                'BI' => 'BDI',
                'KH' => 'KHM',
                'CM' => 'CMR',
                'CA' => 'CAN',
                'CV' => 'CPV',
                'KY' => 'CYM',
                'CF' => 'CAF',
                'TD' => 'TCD',
                'CL' => 'CHL',
                'CN' => 'CHN',
                'CX' => 'CXR',
                'CC' => 'CCK',
                'CO' => 'COL',
                'KM' => 'COM',
                'CG' => 'COG',
                'CD' => 'COD',
                'CK' => 'COK',
                'CR' => 'CRI',
                'HR' => 'HRV',
                'CU' => 'CUB',
                'CY' => 'CYP',
                'CZ' => 'CZE',
                'DK' => 'DNK',
                'DJ' => 'DJI',
                'DM' => 'DMA',
                'DO' => 'DOM',
                'EC' => 'ECU',
                'EG' => 'EGY',
                'SV' => 'SLV',
                'GQ' => 'GNQ',
                'ER' => 'ERI',
                'EE' => 'EST',
                'ET' => 'ETH ',
                'FK' => 'FLK',
                'FO' => 'FRO',
                'FJ' => 'FJI',
                'FI' => 'FIN',
                'FR' => 'FRA',
                'GF' => 'GUF',
                'PF' => 'PYF',
                'TF' => 'ATF',
                'GA' => 'GAB',
                'GM' => 'GMB',
                'GE' => 'GEO',
                'DE' => 'DEU',
                'GH' => 'GHA',
                'GI' => 'GIB',
                'GR' => 'GRC',
                'GL' => 'GRL',
                'GD' => 'GRD',
                'GP' => 'GLP',
                'GT' => 'GTM',
                'GN' => 'GIN',
                'GW' => 'GNB',
                'GY' => 'GUY',
                'HT' => 'HTI',
                'HM' => 'HMD',
                'HN' => 'VAT',
                'HK' => 'HKG',
                'HU' => 'HUN',
                'IS' => 'ISL',
                'IN' => 'IND',
                'ID' => 'IDN',
                'IR' => 'IRN',
                'IQ' => 'IRQ',
                'IE' => 'IRL',
                'IL' => 'ISR',
                'IT' => 'ITA',
                'CI' => 'CIV',
                'JM' => 'JAM',
                'JP' => 'JPN',
                'JO' => 'JOR',
                'KZ' => 'KAZ',
                'KE' => 'KEN',
                'KI' => 'KIR',
                'KW' => 'KWT',
                'KG' => 'KGZ',
                'LA' => 'LAO',
                'LV' => 'LVA',
                'LB' => 'LBN',
                'LS' => 'LSO',
                'LR' => 'LBR',
                'LY' => 'LBY',
                'LI' => 'LIE',
                'LT' => 'LTU',
                'LU' => 'LUX',
                'MO' => 'MAC',
                'MK' => 'MKD',
                'MG' => 'MDG',
                'MW' => 'MWI',
                'MY' => 'MYS',
                'MV' => 'MDV',
                'ML' => 'MLI',
                'MT' => 'MLT',
                'MH' => 'MHL',
                'MQ' => 'MTQ',
                'MR' => 'MRT',
                'MU' => 'MUS',
                'YT' => 'MYT',
                'MX' => 'MEX',
                'FM' => 'FSM',
                'MD' => 'MDA',
                'MC' => 'MCO',
                'MN' => 'MNG',
                'ME' => 'MNE',
                'MS' => 'MSR',
                'MA' => 'MAR',
                'MZ' => 'MOZ',
                'MM' => 'MMR',
                'NA' => 'NAM',
                'NR' => 'NRU',
                'NP' => 'NPL',
                'NL' => 'NLD',
                'AN' => 'ANT',
                'NC' => 'NCL',
                'NZ' => 'NZL',
                'NI' => 'NIC',
                'NE' => 'NER',
                'NG' => 'NGA',
                'NU' => 'NIU',
                'NF' => 'NFK',
                'KP' => 'PRK',
                'NO' => 'NOR',
                'OM' => 'OMN',
                'PK' => 'PAK',
                'PS' => 'PSE',
                'PA' => 'PAN',
                'PG' => 'PNG',
                'PY' => 'PRY',
                'PE' => 'PER',
                'PH' => 'PHL',
                'PN' => 'PCN',
                'PL' => 'POL',
                'PT' => 'PRT',
                'QA' => 'QAT',
                'RE' => 'REU',
                'RO' => 'ROM',
                'RU' => 'RUS',
                'RW' => 'RWA',
                'BL' => 'BLM',
                'SH' => 'SHN',
                'KN' => 'KNA',
                'LC' => 'LCA',
                'MF' => 'MAF',
                'PM' => 'SPM',
                'VC' => 'VCT',
                'SM' => 'SMR',
                'ST' => 'STP',
                'SA' => 'SAU',
                'SN' => 'SEN',
                'RS' => 'SRB',
                'SC' => 'SYC',
                'SL' => 'SLE',
                'SG' => 'SGP',
                'SK' => 'SVK',
                'SI' => 'SVN',
                'SB' => 'SLB',
                'SO' => 'SOM',
                'ZA' => 'ZAF',
                'GS' => 'SGS',
                'KR' => 'KOR',
                'SS' => 'SSD',
                'ES' => 'ESP',
                'LK' => 'LKA',
                'SD' => 'SDN',
                'SR' => 'SUR',
                'SJ' => 'SJM',
                'SZ' => 'SWZ',
                'SE' => 'SWE',
                'CH' => 'CHE',
                'SY' => 'SYR',
                'TW' => 'TWN',
                'TJ' => 'TJK',
                'TZ' => 'TZA',
                'TH' => 'THA',
                'TL' => 'TLS',
                'TG' => 'TGO',
                'TK' => 'TKL',
                'TO' => 'TON',
                'TT' => 'TTO',
                'TN' => 'TUN',
                'TR' => 'TUR',
                'TM' => 'TKM',
                'TC' => 'TCA',
                'TV' => 'TUV',
                'UG' => 'UGA',
                'UA' => 'UKR',
                'AE' => 'ARE',
                'GB' => 'GBR',
                'US' => 'USA',
                'UY' => 'URY',
                'UZ' => 'UZB',
                'VU' => 'VUT',
                'VA' => 'VAT',
                'VE' => 'VEN',
                'VN' => 'VNM',
                'WF' => 'WLF',
                'EH' => 'ESH',
                'WS' => 'WSM',
                'YE' => 'YEM',
                'ZM' => 'ZMB',
                'ZW' => 'ZWE',
                'PW' => 'PLW',
                'BQ' => 'BES',
                'CW' => 'CUW',
                'GG' => 'GGY',
                'IM' => 'IMN',
                'JE' => 'JEY',
                'SX' => 'SXM'
                );

        if(isset($countries[$code])) {
            return $countries[$code];
        }
        else {
            return '';
        }
    }
    
    /* Get User Browser */
    function getBrowser(){
        $u_agent = $_SERVER['HTTP_USER_AGENT'];
        $bname = 'Unknown';
        $platform = 'Unknown';
        $version= "";

        //First get the platform?
        if (preg_match('/linux/i', $u_agent)) {
            $platform = 'linux';
        } elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
            $platform = 'mac';
        } elseif (preg_match('/windows|win32/i', $u_agent)) {
            $platform = 'windows';
        }

        // Next get the name of the useragent yes seperately and for good reason
        if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)){
            $bname = 'Internet Explorer';
            $ub = "MSIE";
        } elseif(preg_match('/Firefox/i',$u_agent)) {
            $bname = 'Mozilla Firefox';
            $ub = "Firefox";
        } elseif(preg_match('/Chrome/i',$u_agent)){
            $bname = 'Google Chrome';
            $ub = "Chrome";
        } elseif (preg_match('/Safari/i',$u_agent)){
            $bname = 'Apple Safari';
            $ub = "Safari";
        } elseif(preg_match('/Opera/i',$u_agent)){
            $bname = 'Opera';
            $ub = "Opera";
        } elseif(preg_match('/Netscape/i',$u_agent)) {
            $bname = 'Netscape';
            $ub = "Netscape";
        }

        // finally get the correct version number
        $known = array('Version', $ub, 'other');
        $pattern = '#(?<browser>' . join('|', $known) .
        ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        if (!preg_match_all($pattern, $u_agent, $matches)) {
            // we have no matching number just continue
        }

        // see how many we have
        $i = count($matches['browser']);
        if ($i != 1) {
            //we will have two since we are not using 'other' argument yet
            //see if version is before or after the name
            if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
                $version= $matches['version'][0];
            } else {
                $version= $matches['version'][1];
            }
        } else {
            $version= $matches['version'][0];
        }

        // check if we have a number
        if ($version==null || $version=="") {$version="?";}

        return array(
                'userAgent' => $u_agent,
                'name'      => $bname,
                'version'   => $version,
                'platform'  => $platform,
                'pattern'    => $pattern
        );
    }
}
