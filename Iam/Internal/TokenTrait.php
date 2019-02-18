<?php

namespace Iam\Internal;

trait TokenTrait{
	
	public function createAuthToken(array $args){
		$type = $this->checkArgs($args);
		if($type !== 0){
			throw new \RuntimeException('invalid args');
		}
		
		$body = [
			'auth' => [
				'identity' => [
					'methods' => ['password'],
					'password' => [
						'user' => [
							'name' => $args['UserName'],
							'password' => $args['Password'],
							'domain' => ['name' => $args['DomainName']]
						]
					]
				],
				'scope' => [
					'domain' => ['name' => $args['DomainName']]
				]
			]
		];
		
		$body = json_encode($body);
		$headers = ['Content-Type' => 'application/json;charset=utf8'];
		$adapter = ['X-Subject-Token' => 'AuthToken'];
		return $this->doRequest('POST', 'v3/auth/tokens', $headers, $body, $adapter);
	}
}