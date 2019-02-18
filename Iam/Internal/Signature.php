<?php

namespace Iam\Internal;

class Signature
{
	const CONTENT_SHA256 = 'e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855';
	
	private $ak;
	
	private $sk;
	
	private $endpoint;
	
	private $region;
	
	public function __construct($ak, $sk, $endpoint, $region='china')
	{
	    $this->ak = $ak;
	    $this->sk = $sk;
	    $this->endpoint = $endpoint;
	    $this->region = $region;
	}
	
	public function doAuth(array $headers, $method, $canonicalUri, $pathArgs, $body)
	{
		$longDate = gmdate('Ymd\THis\Z', time());
		
		$longDate = '20181231T151529Z';
		
		$headers['X-Sdk-Date'] = $longDate;
		
		$shortDate = substr($longDate, 0, 8);
		$credential = $this-> getCredential($shortDate);
		
		$headers['Host'] = parse_url($this->endpoint)['host'];
		
		$signedHeaders = $this->getSignedHeaders($headers);
		
		$canonicalstring = $this-> makeCanonicalstring($method, $headers, $canonicalUri, $pathArgs, $signedHeaders, $body);
		
		$signature = $this -> getSignature($canonicalstring, $longDate, $shortDate);
		
		$authorization = 'SDK-HMAC-SHA256 ' . 'Credential=' . $credential. ', ' . 'SignedHeaders=' . $signedHeaders . ', ' . 'Signature=' . $signature;
		$headers['Authorization'] = $authorization;
		return $headers;
	}
	
	public function getSignature($canonicalstring, $longDate, $shortDate)
	{
		$stringToSign = [];
		$stringToSign[] = 'SDK-HMAC-SHA256';
		$stringToSign[] = "\n";
		$stringToSign[] = $longDate;
		$stringToSign[] = "\n";
		$stringToSign[] = $this -> getScope($shortDate);
		$stringToSign[] = "\n";
		$stringToSign[] = hash('sha256', $canonicalstring);
		
		$dateKey = hash_hmac('sha256', $shortDate, 'SDK' . $this -> sk, true);
		$regionKey = hash_hmac('sha256', $this->region, $dateKey, true);
		$serviceKey = hash_hmac('sha256', 'AG', $regionKey, true);
		$signingKey = hash_hmac('sha256', 'sdk_request', $serviceKey, true);
		
		$signature = hash_hmac('sha256', implode('', $stringToSign), $signingKey);
		return $signature;
	}
	
	public function getCredential($shortDate)
	{
		return $this->ak . '/' . $this->getScope($shortDate);
	}

	public function getScope($shortDate)
	{
		return $shortDate . '/' . $this->region . '/AG/sdk_request';
	}
	
	public function getCanonicalQueryString($pathArgs)
	{
		$queryStr = '';
		ksort($pathArgs);
		$index = 0;
		foreach ($pathArgs as $key => $value){
			$queryStr .=  $key . '=' . $value;
			if($index++ !== count($pathArgs) - 1){
				$queryStr .= '&';
			}
		}
		return $queryStr;
	}
	
	public function getCanonicalHeaders($headers)
	{
		$_headers = [];
		foreach ($headers as $key => $value) {
			$_headers[strtolower($key)] = $value;
		}
		ksort($_headers);
		
		$canonicalHeaderStr = '';
		
		foreach ($_headers as $key => $value){
			$value = is_array($value) ? implode(',', $value) : $value; 
			$canonicalHeaderStr .= $key . ':' . $value;
			$canonicalHeaderStr .= "\n";
		}
		return $canonicalHeaderStr;
	}
	
	public function makeCanonicalstring($method, $headers, $canonicalUri, $pathArgs, $signedHeaders, $payload=null)
	{
		$buffer = [];
		$buffer[] = $method;
		$buffer[] = "\n";
		$buffer[] = $canonicalUri;
		$buffer[] = "\n";
		$buffer[] = $this->getCanonicalQueryString($pathArgs);
		$buffer[] = "\n";
		$buffer[] = $this->getCanonicalHeaders($headers);
		$buffer[] = "\n";
		$buffer[] = $signedHeaders;
		$buffer[] = "\n";
		$buffer[] = $payload ? hash('sha256', $payload): self::CONTENT_SHA256;
		
		return implode('', $buffer);
	}
	
	public function getSignedHeaders($headers)
	{
		$_headers = [];
		
		foreach ($headers as $key => $value) {
			$_headers[] = strtolower($key);
		}
		
		sort($_headers);
		
		$signedHeaders = '';
		
		foreach ($_headers as $key => $value){
			$signedHeaders .= $value;
			if($key !== count($_headers) - 1){
				$signedHeaders .= ';';
			}
		}
		return $signedHeaders;
	}
	
}
