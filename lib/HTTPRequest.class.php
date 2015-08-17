<?php
/**
 * HTTP.class.php
 */
 
/**
 * This class is used to send HTTP request and get the result
 * @package Utils
 */
class HTTPrequest{
	use Utils;

	const METHOD_POST = 'POST';
	const METHOD_GET = 'GET';
	const METHOD_PUT = 'PUT';
	const METHOD_DELETE = 'DELETE';
	const METHOD_PATCH = 'PATCH';

	/**
	 * The URL to call
	 * @var string
	 */
	private $url,

	/**
	 * The HTTP method
	 * @var string
	 */
	$method = self::METHOD_GET,

	/**
	 * The data to send in the request body
	 * @var array
	 */
	$body = array(),

	/**
	 * The request headers
	 * @var array
	 */
	$headers = array(),

	/**
	 * The response
	 * @var string
	 */
	$response = '',

	
	/**
	 * The request content type
	 */
	$contentType = 'urlencoded',

	
	/**
	 * The response content type
	 */
	$dataType = 'html',

	/**
	 * The response headers
	 */
	$responseHeaders = array(),

	/**
	 * The HTTP status code of the response
	 */
	$status = 0;


	/**
	 * Standard data types
	 * @static
	 * @var array
	 */
	private static $dataTypes = array(
		'text' => 'text/plain',
		'html' => 'text/html',
		'json' => 'application/json',
		'xml' => 'application/xml',
		'urlencoded' => 'application/x-www-form-urlencoded'
	);


	/**
	 * Create a HTTP request
	 * @param array $options The request parameters. This array can have the following data :
	 *						- 'url' (required): The url to call
	 *						- 'method' (optionnal, default 'GET') : The HTTP method
	 *						- 'body' (optionnal) : The request content
	 *						- 'headers' (optionnal) : The request headers
	 *						- 'dataType' (optionnal, default 'html') : The wanted response type
	 *						- 'contentType' (optionnal) : The request content type	 
	 */
	public function __construct($options){
		$this->map($options);

		$this->setDataType($this->dataType);
		$this->setContentType($this->contentType);
	}


	/**
	 * Set further headers
	 * @param array headers The headers to add
	 */
	public function setHeaders($headers){
		$this->headers = array_merge($this->headers, $headers);
	}

	/**
	 * Set the request body
	 * @param mixed $body The body to set
	 */
	public function setBody($body){
		$this->body = $body;
	}

	/**
	 * Set the expected data type
	 * @param string $type The expected type of response data. Can be 'text', 'html', 'json', 'xml' or the wanted mime type
	 */
	public function setDataType($type){
		if(self::$dataTypes[$type]){
			$value = self::$dataTypes[$type];			
		}
		else{
			$value = $type;
		}
		$this->setHeaders(array(
			'Accept' => $value
		));
		$this->dataType = $type;
	}


	/**
	 * Set the request Content type
	 * @param string $type The expected type of response data. Can be 'text', 'html', 'json', 'xml' or the wanted mime type
	 */
	public function setContentType($type){
		if(self::$dataTypes[$type]){
			$value = self::$dataTypes[$type];			
		}
		else{
			$value = $type;
		}
		$this->setHeaders(array(
			'Content-Type' => $value
		));

		$this->contentType = $type;
	}
	

	/**
	 * Send the request and get the result
	 */
	public function send(){
		$data = '';
		switch($this->contentType){
			case 'urlencoded' :
				$data = http_build_query($this->body);
				break;

			case 'json' :
				$data = json_encode($this->body);
				break;

			default :
				$data = $this->body;
				break;
		}
		
		$opts = array('http' =>
			array(
				'method'  => strtoupper($this->method),
				'header'  => implode(PHP_EOL, array_map(function($key, $value){
					return "$key: $value";
				}, array_keys($this->headers), $this->headers)),
				'content' => $data
			)
		);

		$context  = stream_context_create($opts);
		
		$result = @file_get_contents($this->url, false, $context);

		foreach($http_response_header as $header){
			if(preg_match('/^(.*?)\:\s+(.*)$/', $header, $match)){
				$this->responseHeaders[$match[1]] = $match[2];
			}
			elseif(preg_match('/^HTTP\/[^\s]+\s+(\d+)/', $header, $match)){
				$this->status = $match[1];
			}
		}

		$this->response = $result;		
	}


	/**
	 * Get the response headers. If $name is set, then this function will return the specific header value, else it will return an array containing all headers
	 * @param string $name The name of a specific
	 * @return mixed The array containing all headers, or the value of the headers specified by $name
	 */
	public function getResponseHeaders($name = null){
		if(!$name){
			return $this->responseHeaders;
		}
		else{
			return isset($this->responseHeaders[$name]) ? $this->responseHeaders[$name] : null;
		}

	}


	/**
	 * Get the response status Code
	 * @return int the value of the response status code
	 */
	public function getStatusCode(){
		return $this->status;
	}


	/**
	 * Get the response body
	 * @return mixed The HTTP response body, formatted following the dataType requested
	 */
	public function getResponse(){
		switch($this->dataType){
			case 'json' :
				return json_decode($this->response, true);

			default :
				return $this->response;
		}
	}
}