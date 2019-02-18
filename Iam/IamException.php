<?php

namespace Iam;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class IamException extends \RuntimeException
{
	const CLIENT = 'client';
	
	const SERVER = 'server';
	
	/**
	 * @var Response
	 */
	protected $response;
	
	/**
	 * @var Request
	 */
	protected $request;
	
	/**
	 * @var string Exception type (client / server)
	 */
	protected $exceptionType;
	
	protected $exceptionCode;
	
	protected $exceptionMessage;
	
	protected $exceptionTitle;
	
	protected $requestId;
	
	public function __construct ($message = null, $code = null, $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
	
	/**
	 * Set the exception code
	 *
	 * @param string $code Exception code
	 */
	public function setExceptionCode($code)
	{
		$this->exceptionCode = $code;
	}
	/**
	 * Get the exception code
	 *
	 * @return string|null
	 */
	public function getExceptionCode()
	{
		return $this->exceptionCode;
	}
	
	public function setExceptionMessage($message)
	{
		$this->exceptionMessage = $message;
	}
	
	public function getExceptionMessage()
	{
		return $this->exceptionMessage ? $this->exceptionMessage : $this->message;
	}
	
	public function setExceptionTitle($exceptionTitle){
		$this->exceptionTitle= $exceptionTitle;
	}
	
	public function getExceptionTitle(){
		return $this->exceptionTitle;
	}
	
	public function setRequestId($requestId){
		$this->requestId = $requestId;
	}
	
	public function getRequestId(){
		return $this->requestId;
	}
	
	/**
	 * Set the exception type
	 *
	 * @param string $type Exception type
	 */
	public function setExceptionType($type)
	{
		$this->exceptionType = $type;
	}
	
	/**
	 * Get the exception type (one of client or server)
	 *
	 * @return string|null
	 */
	public function getExceptionType()
	{
		return $this->exceptionType;
	}
	
	
	/**
	 * Set the associated response
	 *
	 * @param Response $response Response
	 */
	public function setResponse(Response $response)
	{
		$this->response = $response;
	}
	
	/**
	 * Get the associated response object
	 *
	 * @return Response|null
	 */
	public function getResponse()
	{
		return $this->response;
	}
	
	/**
	 * Set the associated request
	 *
	 * @param Request $request
	 */
	public function setRequest(Request $request)
	{
		$this->request = $request;
	}
	
	/**
	 * Get the associated request object
	 *
	 * @return RequestInterface|null
	 */
	public function getRequest()
	{
		return $this->request;
	}
	
	/**
	 * Get the status code of the response
	 *
	 * @return int|null
	 */
	public function getStatusCode()
	{
		return $this->response ? $this->response->getStatusCode() : -1;
	}
	
	/**
	 * Cast to a string
	 *
	 * @return string
	 */
	public function __toString()
	{
		$message = get_class($this) . ': '
		. 'Exception Code: ' . $this->getExceptionCode() . ', '
		. 'Status Code: ' . $this->getStatusCode() . ', '
		. 'Exception Type: ' . $this->getExceptionType() . ', '
		. 'Exception Message: ' . ($this->getExceptionMessage() ? $this->getExceptionMessage():$this->getMessage());
		
		// Add the User-Agent if available
		if ($this->request) {
			$message .= ', ' . 'User-Agent: ' . $this->request->getHeaderLine('User-Agent');
		}
		$message .= "\n";
		
		return $message;
	}
	
}