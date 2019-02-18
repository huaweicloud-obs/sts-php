<?php

namespace Iam\Internal;


trait CredentialTrait{
	
	public function createCredential(array $args){
		$type = $this->checkArgs($args);
		if($type === 0){
			throw new \RuntimeException('invalid args');
		}
		
		$body = [
			'auth' => [
				'identity' => [
					'methods' => ['token'],
					'token' => [
						'duration-seconds' => isset($args['expires'])? $args['expires'] : '900',
					]
				]
			]
		];
		
		$body = json_encode($body);
		
		if($type === 2){
			$headers = [
				'Content-Type' => 'application/json;charset=utf8',
				'X-Auth-Token' => $args['AuthToken']
			];
			return $this->doRequest('POST', 'v3.0/OS-CREDENTIAL/securitytokens', $headers, $body);
		}
		
		$headers = [
			'Content-Type' => 'application/json;charset=utf8'
		];
		
		$signer = new Signature($args['AK'], $args['SK'], $this->endpoint);
		$headers = $signer -> doAuth($headers, 'POST', '/v3.0/OS-CREDENTIAL/securitytokens/', [], $body);
		
		return $this->doRequest('POST', 'v3.0/OS-CREDENTIAL/securitytokens', $headers, $body);
	}
}