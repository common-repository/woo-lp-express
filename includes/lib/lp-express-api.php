<?php
/**
 * A simple client for LP Express SOAP Web Service.
 * The client class has been tested with 5.4 and 5.5 versions of PHP
 * 
 * Copyright (c) 2015, NFQ Technolgies
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *  * Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 *  * Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *  * Neither the name of the NFQ Technologies nor the
 *    names of its contributors may be used to endorse or promote products
 *    derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL NFQ Technologies BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * 
 * @version 1.0
 */
class LpExpressApi
{
    /**
     * Available layouts of labels document
     */
    const LFL_A4_1  = 'lfl_a4_1';
    const LFL_A4_3  = 'lfl_a4_3';
    const LFL_10x15 = 'lfl_10x15';
    
    /**
     * Hostname of the web service
     * 
     * @var string
     */
    protected $hostname;
    
    /**
     * Partner ID which is used to authenticate a request
     * @var string
     */
    protected $partnerId;
    
    /**
     * Partner password which is used to authenticate a request
     * @var string
     */
    protected $partnerPassword;

    /**
     * List of options to pass directly to \SoapClient constructor.
     * 
     * @var array
     */
    protected $rawOptions = array();
     
    /**
     * 
     * @var \SoapClient
     */
    protected $connector;

    /**
     * 
     * @param null|array $options
     */
    public function __construct(array $options = null)
    {
        if ($options !== null) {
            $this->setOptions($options);
        }
    }
    
    /**
     * Makes actual connection to web service
     * 
     * @return \SoapClient
     */
    public function connect()
    {
        if ($this->connector === null) {
            $options = array(
                'features' => SOAP_SINGLE_ELEMENT_ARRAYS,
            );
            $options = array_merge($this->getRawOptions(), $options);
            
            $connector = new \SoapClient($this->getWsdlUri(), $options);
            $connector->__setSoapHeaders($this->getSoapHeaders());
            $this->connector = $connector;
        }
    
        return $this->connector;
    }
    
    /**
     * Returns SOAP client
     * 
     * @return \SoapClient
     */
    public function getConnector()
    {
        $this->connect();
        return $this->connector;
    }
    
    /**
     * Calls API method with parameters
     *
     * @param string $method
     * @param mixed $params
     * @return mixed
     */
    public function call($method, $params = null)
    {
        $this->connect();
        return $this->connector->__soapCall($method, $params);
    }
    
    /**
     * Returns the list of SOAP headers of each web service request
     * 
     * @return SoapHeader[]
     */
    protected function getSoapHeaders()
    {
        $credentials = new \stdClass();
        $credentials->userid = new \SoapVar($this->getPartnerId(), XSD_STRING);
        $credentials->password = new \SoapVar($this->getPartnerPassword(), XSD_STRING);
        
        $userAuth = new \SoapVar($credentials, SOAP_ENC_OBJECT);

        return array(
            new \SoapHeader('bpdcws', 'UserAuth', $userAuth)
        );

    }
    
    /**
     * Sets several options at once
     * 
     * @param array $options Array of key/value pairs of option name/value
     * @throws \Exception
     * @return LpExpressApi
     */
    public function setOptions(array $options)
    {
        foreach ($options as $name => $value) {
            $setter = 'set' . ucfirst($name);
            if (!method_exists($this, $setter)) {
                throw new \Exception("Unknown configuration option '{$name}'");
            }
            
            $this->$setter($value);
        }
        
        return $this;
    }
    
    /**
     * Sets hostname of the web service
     * 
     * @param string $uri
     * @return LpExpressApi
     */
    public function setHostname($uri)
    {
        $this->hostname = $uri;
        return $this;
    }
    
    /**
     * Returns hostname of the web service
     * 
     * @return string
     */
    public function getHostname()
    {
        return $this->hostname;
    }
    
    /**
     * Returns URI of the wsdl document of the SOAP web service
     * 
     * @return string
     */
    public function getWsdlUri()
    {
        return 'http://' . $this->hostname . '/bpdcws/wsdl';
    }
    
    /**
     * Returns URI of PDF document of the labels
     * 
     * @param string $orderpdfid
     * @param null|string $lfl
     * @return string
     */
    public function getLabelsUri($orderpdfid, $lfl = null)
    {
        $uri = 'http://' . $this->hostname . '/getpdf/label/' . $orderpdfid;
        
        if ($lfl !== null) {
            $uri .= '/?lfl=' . $lfl;
        }
        
        return $uri;
    }
    
    /**
     * 
     * @param string $manifestid
     * @return string
     */
    public function getManifestUri($manifestid)
    {
        return 'http://' . $this->hostname . '/getpdf/manifest/' . $manifestid;
    }

    /**
     * Sets partner ID which is used to authenticate a request
     * 
     * @param string $id
     * @return LpExpressApi
     */
    public function setPartnerId($id)
    {
        $this->partnerId = $id;
        return $this;
    }
    
    /**
     * Returns partner ID which is used to authenticate a request
     * @return string
     */
    public function getPartnerId()
    {
        return $this->partnerId;
    }

    /**
     * Sets partner password which is used to authenticate a request
     * 
     * @param string $password
     * @return LpExpressApi
     */
    public function setPartnerPassword($password)
    {
        $this->partnerPassword = $password;
        return $this;
    }
    
    /**
     * Returns partner password which is used to authenticate a request
     * 
     * @return string
     */
    public function getPartnerPassword()
    {
        return $this->partnerPassword;
    }

    /**
     * Sets list of options to pass directly to \SoapClient constructor.
     * See list of available options at @link http://php.net/manual/en/soapclient.soapclient.php
     * 
     * @param array $options
     * @return LpExpressApi
     */
    public function setRawOptions(array $options = array())
    {
        $this->rawOptions = $options;
        return $this;
    }
    
    /**
     * Returns list of options to pass directly to \SoapClient constructor.
     * 
     * @return array
     */
    public function getRawOptions()
    {
        return $this->rawOptions;
    }
    
}