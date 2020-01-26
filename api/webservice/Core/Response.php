<?php

namespace Api\Core;

/**
 * Web service response class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class Response
{
	/**
	 * Access control allow headers.
	 *
	 * @var string[]
	 */
	protected $acceptableHeaders = ['x-api-key', 'x-encrypted', 'x-token'];
	/**
	 * Access control allow methods.
	 *
	 * @var string[]
	 */
	protected $acceptableMethods = [];
	protected static $instance = false;
	protected $body;
	protected $headers = [];
	protected $status = 200;

	public static function getInstance()
	{
		if (!static::$instance) {
			static::$instance = new self();
		}
		return static::$instance;
	}

	public function addHeader($key, $value)
	{
		$this->headers[$key] = $value;
	}

	public function setStatus($status)
	{
		$this->status = $status;
	}

	public function setBody($body)
	{
		$this->body = $body;
	}

	/**
	 * Set acceptable methods.
	 *
	 * @param string[] $methods
	 *
	 * @return void
	 */
	public function setAcceptableMethods(array $methods)
	{
		$this->acceptableMethods = array_merge($this->acceptableMethods, $methods);
	}

	/**
	 * Set acceptable headers.
	 *
	 * @param string[] $headers
	 *
	 * @return void
	 */
	public function setAcceptableHeaders(array $headers)
	{
		$this->acceptableHeaders = array_merge($this->acceptableHeaders, $headers);
	}

	private function requestStatus()
	{
		$statusCodes = [
			200 => 'OK',
			401 => 'Unauthorized',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			500 => 'Internal Server Error',
		];
		return ($statusCodes[$this->status]) ? $statusCodes[$this->status] : $statusCodes[500];
	}

	public function send()
	{
		$encryptDataTransfer = \App\Config::api('ENCRYPT_DATA_TRANSFER') ? 1 : 0;
		if (200 !== $this->status) {
			$encryptDataTransfer = 0;
		}
		$requestContentType = strtolower(\App\Request::_getServer('HTTP_ACCEPT'));
		if (!headers_sent()) {
			header('access-control-allow-origin: *');
			header('access-control-allow-methods: ' . implode(', ', $this->acceptableMethods));
			header('access-control-allow-headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, ' . implode(', ', $this->acceptableHeaders));
			header("content-type: $requestContentType");
			header(\App\Request::_getServer('SERVER_PROTOCOL') . ' ' . $this->status . ' ' . $this->requestStatus());
			header('encrypted: ' . $encryptDataTransfer);
			foreach ($this->headers as $key => $header) {
				header(\strtolower($key) . ': ' . $header);
			}
		}
		if (!empty($this->body)) {
			if ($encryptDataTransfer) {
				header('content-disposition: attachment; filename="api.json"');
				$response = $this->encryptData($this->body);
			} else {
				if (false !== strpos($requestContentType, 'text/html')) {
					header('content-disposition: attachment; filename="api.html"');
					$response = $this->encodeHtml($this->body);
				} elseif (false !== strpos($requestContentType, 'application/xml')) {
					header('content-disposition: attachment; filename="api.xml"');
					$response = $this->encodeXml($this->body);
				} else {
					header('content-disposition: attachment; filename="api.json"');
					$response = $this->encodeJson($this->body);
				}
			}
			echo $response;
		}
		$this->debugResponse();
	}

	public function encryptData($data)
	{
		openssl_public_encrypt($data, $encrypted, 'file://' . ROOT_DIRECTORY . \DIRECTORY_SEPARATOR . \App\Config::api('PUBLIC_KEY'), OPENSSL_PKCS1_OAEP_PADDING);
		return $encrypted;
	}

	public function debugResponse()
	{
		if (\App\Config::debug('WEBSERVICE_DEBUG')) {
			$request = Request::init();
			$log = '-------------  Response  -----  ' . date('Y-m-d H:i:s') . "  ------\n";
			$log .= "Status: {$this->status}\n";
			$log .= 'REQUEST_METHOD: ' . $request->getRequestMethod() . PHP_EOL;
			$log .= 'REQUEST_URI: ' . $_SERVER['REQUEST_URI'] . PHP_EOL;
			$log .= 'QUERY_STRING: ' . $_SERVER['QUERY_STRING'] . PHP_EOL;
			$log .= 'PATH_INFO: ' . $_SERVER['PATH_INFO'] . PHP_EOL;
			if ($this->headers) {
				$log .= "----------- Response Headers -----------\n";
				foreach ($this->headers as $key => $header) {
					$log .= "$key : $header\n";
				}
			}
			$log .= "----------- Response data -----------\n";
			$log .= print_r($this->body, true) . PHP_EOL;
			file_put_contents('cache/logs/webserviceDebug.log', $log, FILE_APPEND);
		}
	}

	public function encodeHtml($responseData)
	{
		$htmlResponse = "<table border='1'>";
		foreach ($responseData as $key => $value) {
			$htmlResponse .= "<tr><td>$key</td><td>" . (\is_array($value) ? $this->encodeHtml($value) : nl2br($value)) . '</td></tr>';
		}
		return $htmlResponse . '</table>';
	}

	public function encodeJson($responseData)
	{
		return json_encode($responseData);
	}

	public function encodeXml($responseData)
	{
		$xml = new \SimpleXMLElement('<?xml version="1.0"?><data></data>');
		$this->toXml($responseData, $xml);

		return $xml->asXML();
	}

	public function toXml($data, \SimpleXMLElement &$xmlData)
	{
		foreach ($data as $key => $value) {
			if (is_numeric($key)) {
				$key = 'item' . $key;
			}
			if (\is_array($value)) {
				$subnode = $xmlData->addChild($key);
				$this->toXml($value, $subnode);
			} else {
				$xmlData->addChild("$key", htmlspecialchars("$value"));
			}
		}
	}
}
