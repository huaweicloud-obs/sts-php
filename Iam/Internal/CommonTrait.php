<?php

namespace Iam\Internal;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\Handler\CurlMultiHandler;
use GuzzleHttp\Handler\Proxy;
use Iam\IamException;


trait CommonTrait{
	
	protected function checkArgs(array $args){
		if(isset($args['UserName']) && isset($args['Password']) && isset($args['DomainName'])){
			return 0;
		}
		
		if(isset($args['AK']) && isset($args['SK'])){
			return 1;
		}
		
		if(isset($args['AuthToken'])){
			return 2;
		}
		
		throw new \RuntimeException('invalid args');
	}
	
	protected function doRequest($httpMethod, $requestUri, $headers, $body, $headerAdapter=null)
	{
		$model = new Model();
		$request = $this -> makeRequest($httpMethod, $requestUri, $headers, $body);
		$this->sendRequest($model, $request, 1, $headerAdapter);
		return $model;
	}
	
	protected function makeRequest($httpMethod, $requestUri, $headers, $body)
	{
		$headers['User-Agent'] = self::default_user_agent();
		$requestUrl = $this->endpoint . '/' . $requestUri;
		return new Request($httpMethod, $requestUrl, $headers, $body);
	}
	
	protected function sendRequest($model, $request, $requestCount = 1, $headerAdapter=null)
	{
		$promise = $this->httpClient->sendAsync($request, ['stream' => false])->then(
				function(Response $response) use ($model, $request, $headerAdapter){
					$this -> parseResponse($model, $request, $response, $headerAdapter);
				},
				function (RequestException $exception) use ($model, $request, $requestCount) {
					$this -> parseException($request, $exception);
				});
		$promise -> wait();
	}
	
	protected function parseException(Request $request, RequestException $exception, $message=null)
	{
		$message = $message ? $message : $exception-> getMessage();
		if($exception->hasResponse()){
			throw $this->buildException($request, $exception-> getResponse(), $message);
		}
		$iamException= new IamException($message);
		$iamException-> setExceptionType('client');
		$iamException-> setRequest($request);
		throw $iamException;
	}
	
	protected function buildException(Request $request, Response $response, $message=null){
		$expectedLength = $response -> getHeaderLine('Content-Length');
		$expectedLength = is_numeric($expectedLength) ? floatval($expectedLength) : null;
		$body = new CheckoutStream($response->getBody(), $expectedLength);
		
		$iamException= new IamException($message ? $message : '');
		if($expectedLength > 0){
			$contents = $body->getContents();
			$json = json_decode($contents, true);
			if(isset($json['error'])){
				if(isset($json['error']['code'])){
					$iamException ->setExceptionCode($json['error']['code']);
				}
				if(isset($json['error']['message'])){
					$iamException ->setExceptionMessage($json['error']['message']);
				}
				if(isset($json['error']['title'])){
					$iamException ->setExceptionTitle($json['error']['title']);
				}
			}else if(isset($json['message'])){
				$iamException ->setExceptionMessage($json['message']);
				if(isset($json['request_id'])){
					$iamException ->setRequestId($json['request_id']);
				}
			}else{
			    $iamException ->setExceptionMessage('Unexpected error');
			}
		}
		$iamException-> setExceptionType('client');
		$iamException-> setRequest($request);
		$iamException-> setResponse($response);
		$iamException-> setExceptionType($this->isClientError($response) ? 'client' : 'server');
		return $iamException;
	}
	
	protected function isClientError(Response $response)
	{
		return $response -> getStatusCode() >= 400 && $response -> getStatusCode() < 500;
	}
	
	protected function parseResponse(Model $model, Request $request, Response $response, $headerAdapter=null)
	{
		$statusCode = $response -> getStatusCode();
		
		if($statusCode >= 300){
			throw $this->buildException($request, $response);
		}
		
		$expectedLength = $response -> getHeaderLine('Content-Length');
		$expectedLength = is_numeric($expectedLength) ? floatval($expectedLength) : null;
		$body = new CheckoutStream($response->getBody(), $expectedLength);
		$model['HttpStatusCode'] = $statusCode;
		$model['Reason'] = $response -> getReasonPhrase();
		$model['Headers'] = $response->getHeaders();
		if($expectedLength > 0){
			$model['Body'] = json_decode($body->getContents(), true);
		}
		if($headerAdapter !== null){
			foreach ($headerAdapter as $key => $value){
				$model[$value] = $response->getHeaderLine($key);
			}
		}
	}
	
	
	private static function default_user_agent()
	{
	    static $defaultAgent = '';
	    if (!$defaultAgent) {
	        $defaultAgent = 'iam-sdk-php/' . self::SDK_VERSION;
	    }
	    return $defaultAgent;
	}
	
	private static function choose_handler($iamClient)
	{
	    $handler = null;
	    if (function_exists('curl_multi_exec') && function_exists('curl_exec')) {
	        $f1 = new SdkCurlFactory(50);
	        $f2 = new SdkCurlFactory(3);
	        $iamClient->factorys[] = $f1;
	        $iamClient->factorys[] = $f2;
	        $handler = Proxy::wrapSync(new CurlMultiHandler(['handle_factory' => $f1]), new CurlHandler(['handle_factory' => $f2]));
	    } elseif (function_exists('curl_exec')) {
	        $f = new SdkCurlFactory(3);
	        $iamClient->factorys[] = $f;
	        $handler = new CurlHandler(['handle_factory' => $f]);
	    } elseif (function_exists('curl_multi_exec')) {
	        $f = new SdkCurlFactory(50);
	        $iamClient->factorys[] = $f;
	        $handler = new CurlMultiHandler(['handle_factory' => $f1]);
	    }
	    
	    if (ini_get('allow_url_fopen')) {
	        $handler = $handler
	        ? Proxy::wrapStreaming($handler, new SdkStreamHandler())
	        : new SdkStreamHandler();
	    } elseif (!$handler) {
	        throw new \RuntimeException('GuzzleHttp requires cURL, the '
	            . 'allow_url_fopen ini setting, or a custom HTTP handler.');
	    }
	    
	    return $handler;
	}
}