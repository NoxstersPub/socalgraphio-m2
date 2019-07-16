<?php
namespace Blackbox\Soap\Model;

class Api extends \Magento\Framework\Model\AbstractModel
{
    const LOG_TYPE_REQUEST = 'request';
    const LOG_TYPE_RESPONSE = 'response';

    protected $baseUrl;
    protected $actionBaseUrl;
    protected $namespace = 'soap';
    protected $rootNode = '';
    protected $logCallback = null;

    protected $proxy;

   /* public function __construct(array $params = null)
    {
        if (!isset($params['namespace'])) {
            $params['namespace'] = $this->namespace;
        }
        $this->init($params['baseUrl'], $params['actionBaseUrl'], $params['namespace'], $params['namespaces']);
    }
*/
    public function init($baseUrl, $actionBaseUrl, $namespace = 'soap', $namespaces = array())
    {

        $this->baseUrl = $baseUrl;
        $this->actionBaseUrl = $actionBaseUrl;
        $this->namespace = $namespace;

        $defaultNamespaces = array(
            $this->namespace => 'http://schemas.xmlsoap.org/soap/envelope/',
            'xsd' => 'http://www.w3.org/2001/XMLSchema',
            'xsi' => 'http://www.w3.org/2001/XMLSchema-instance'
        );

        if ($namespaces) {
            $namespaces = array_merge($defaultNamespaces, $namespaces);
        } else {
            $namespaces = $defaultNamespaces;
        }

        $this->rootNode = '<?xml version=\'1.0\' encoding=\'utf-8\'?><' . $this->namespace . ':Envelope ';

        foreach ($namespaces as $name => $space)
        {
            $this->rootNode .= 'xmlns:' . $name . '="' . $space . '" ';
        }

        $this->rootNode .= '/>';
    }

    /**
     * @param $proxy
     * string "ip:port"
     * array(
     *  'proxy' => "ip:port",
     *  'auth' => "user:password"
     * )
     * array(
     *  'ip' => "ip",
     *  'port' => "port",
     *  'user' => "user",
     *  'password' => "password"
     * )
     */
    public function setProxy($proxy)
    {
        $this->proxy = $proxy;
        return $this;
    }

    public function sendParamsToServer($headerData, $bodyData, $action, $url = null, $headers = null) {

        $xml = $this->paramsToXml($headerData, $bodyData, (bool)$this->logCallback);
        $i = 0;
        $lastException = null;

        do {
            try {
                return $this->sendXmlToServer($xml, $action, $url, $headers);
            } catch (Blackbox_Soap_Model_Exception $e) {
                Mage::logException($e);
                echo $e->getMessage() . PHP_EOL;
                $lastException = $e;
            }
        } while (++$i < 3);

        throw $lastException;
    }

    public function sendXmlToServer($xml, $action, $url = null, $headers = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3000);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        $_headers = array("Content-Type: text/xml", "SOAPAction: \"{$this->actionBaseUrl}{$action}\"", 'Connection: close');
        if ($headers) {
            $_headers = array_merge($_headers, $headers);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $_headers);

        $this->_applyProxy($ch);

        $requestInfo = $this->logRequest($xml, $action, $url, $_headers);

        $result = curl_exec($ch);

        if($error = curl_error($ch)) {
            $this->logResponse($requestInfo, $error, $action, $url, $_headers);
            curl_close($ch);
            throw new \Blackbox_Soap_Model_Exception($error);
        }

        curl_close($ch);

        $this->logResponse($requestInfo, $result, $action, $url, $_headers);

        return $result;
    }

    public function paramsToXml($headerParams, $bodyParams, $format = false)
    {
      //  die('qqqqqqq>>>'.$this->rootNode);
        $xml = new \SimpleXMLElement($this->rootNode);

        if ($headerParams) {
            $header = $xml->addChild($this->namespace . ':Header');

            $this->arrayToXML($headerParams, $header, 'Header');
        }

        $body = $xml->addChild($this->namespace . ':Body');

        $this->arrayToXML($bodyParams, $body, 'Body');

        if ($format) {
            $dom_sxe  = dom_import_simplexml($xml);

            $dom = new \DOMDocument('1.0');
            $dom_sxe = $dom->importNode($dom_sxe, true);
            $dom_sxe = $dom->appendChild($dom_sxe);

            $dom->formatOutput = true;
            $dom->preserveWhiteSpace = false;

            return $dom->saveXML();
        }
        return $xml->asXML();
    }

    protected function _applyProxy($ch)
    {
        if ($this->proxy) {
            if (is_array($this->proxy)) {
                if ($this->proxy['proxy']) {
                    curl_setopt($ch, CURLOPT_PROXY, $this->proxy['proxy']);
                } else {
                    $proxy = $this->proxy['ip'] . ':' . $this->proxy['port'];
                    curl_setopt($ch, CURLOPT_PROXY, $proxy);
                }

                if ($this->proxy['auth']) {
                    curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->proxy['auth']);
                } else if ($this->proxy['user'] || $this->proxy['password']) {
                    $auth = $this->proxy['user'] . ':' . $this->proxy['password'];
                    curl_setopt($ch, CURLOPT_PROXYUSERPWD, $auth);
                }
            } else {
                curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
            }
        }

        return $this;
    }

    protected function arrayToXML($array, \SimpleXMLElement $xml, $child_name)
    {
        foreach ($array as $k => $v) {
            if(is_array($v)) {
                if (isset($v['xmlns'])) {
                    $xmlns = $v['xmlns'];
                    unset($v['xmlns']);
                } else {
                    $xmlns = null;
                }

                if (isset($v['_attributes'])) {
                    $attributes = $v['_attributes'];
                    unset($v['_attributes']);
                } else {
                    $attributes = null;
                }

                if (count($v) == 1 && isset($v[0]) && !is_array($v[0])) {
                    $this->addChild($xml, $k, $v[0], $xmlns, $attributes);
                    continue;
                }

                $numerics = array_filter($v, function($key) {
                    return is_numeric($key);
                }, ARRAY_FILTER_USE_KEY);
                if (count($numerics) > 0) {
                    $this->arrayToXML($numerics, $xml, $k);
                }

                $assoc = array_filter($v, function($key) {
                    return !is_numeric($key);
                }, ARRAY_FILTER_USE_KEY);
                if (count($assoc) > 0) {
                    if (is_int($k)) {
                        $this->arrayToXML($assoc, $this->addChild($xml, $child_name, null, $xmlns, $attributes), $xml->getName());
                    } else {
                        $this->arrayToXML($assoc, $this->addChild($xml, $k, null, $xmlns, $attributes), $k);
                    }
                }
            } else {
                (is_int($k)) ? $this->addChild($xml, $child_name, $v) : $this->addChild($xml, $k, $v);
            }
        }

        return $xml->asXML();
    }

    public function xmlToArray ( $xmlObject, $out = array () )
    {
        foreach ( (array) $xmlObject as $index => $node ) {
            $out[$index] = (is_object($node)) ? $this->xmlToArray($node) : $node;
        }

        if (!$out) {
            $out = null;
        }
        return $out;
    }

    /**
     * @param callable $callback
     * @return mixed
     *
     * callback params: $type, $content, $action, $url = null, $headers = null, $requestInfo
     * callback return $requestInfo
     */
    public function setLogCallback($callback)
    {
       
        $this->logCallback = $callback;
    }

    protected function addChild($xml, $name, $value = null, $xmlns = null, $attributes = null)
    {
        $child = $xml->addChild($name, $value, $xmlns);
        if ($attributes) {
            foreach ($attributes as $name => $attribute) {
                if (is_array($attribute)) {
                    $child->addAttribute($name, $attribute['value'], $attribute['namespace']);
                } else {
                    $child->addAttribute($name, $attribute);
                }
            }
        }

        return $child;
    }

    protected function logRequest($content, $action, $url = null, $headers = null)
    {
        return $this->_log(self::LOG_TYPE_REQUEST, $content, $action, $url, $headers);
    }

    protected function logResponse($requestInfo, $content, $action, $url = null, $headers = null)
    {
        $this->_log(self::LOG_TYPE_RESPONSE, $content, $action, $url, $headers, $requestInfo);
    }

    protected function _log($type, $content, $action, $url, $headers, $requestInfo = null)
    {
        if ($this->logCallback) {
            $callback = $this->logCallback; /* @var $callback callable */
            return $callback($type, $content, $action, $url, $headers, $requestInfo);
        }
    }
}