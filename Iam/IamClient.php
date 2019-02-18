<?php


namespace Iam;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Iam\Internal\TokenTrait;
use Iam\Internal\CredentialTrait;
use Iam\Internal\CommonTrait;

class IamClient{
	const SDK_VERSION = '1.0.0';
	
	private $factorys;
	
	private $endpoint = '';
	
	private $sslVerify = false;
	
	private $timeout = 0;
	
	private $socketTimeout = 60;
	
	private $connectTimeout = 60;
	
	private $httpClient;
	
	use CommonTrait;
	use TokenTrait;
	use CredentialTrait;
	
	
	public function __construct(array $config = []){
		$this ->factorys = [];
		
		
		if(isset($config['Endpoint'])){
			$this -> endpoint = trim(strval($config['Endpoint']));
		}
		
		if($this -> endpoint === ''){
			throw new \RuntimeException('endpoint is not set');
		}
		
		while($this -> endpoint[strlen($this -> endpoint)-1] === '/'){
			$this -> endpoint = substr($this -> endpoint, 0, strlen($this -> endpoint)-1);
		}
		
		if(strpos($this-> endpoint, 'http') !== 0){
			$this -> endpoint = 'https://' . $this -> endpoint;
		}
		
		if(isset($config['SslVerify'])){
			$this -> sslVerify = $config['SslVerify'];
		}
		
		if(isset($config['Timeout'])){
			$this -> timeout = intval($config['Timeout']);
		}
		
		if(isset($config['SocketTimeout'])){
			$this -> socketTimeout = intval($config['SocketTimeout']);
		}
		
		if(isset($config['ConnectTimeout'])){
			$this -> connectTimeout = intval($config['ConnectTimeout']);
		}
		
		$handler = self::choose_handler($this);
		
		$this -> httpClient = new Client(
			[
				'timeout' => 0,
				'read_timeout' => $this -> socketTimeout,
				'connect_timeout' => $this -> connectTimeout,
				'allow_redirects' => false,
				'verify' => $this -> sslVerify,
				'expect' => false,
				'handler' => HandlerStack::create($handler),
				'curl' => [
						CURLOPT_BUFFERSIZE => 65536
				]
			]
		);
		
	}
	
	public function __destruct(){
		$this-> close();
	}
	
	public function close(){
		if($this->factorys){
			foreach ($this->factorys as $factory){
				$factory->close();
			}
		}
	}
	
}