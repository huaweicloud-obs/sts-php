<?php
require 'vendor/autoload.php';
require 'iam-autoloader.php';

use Iam\IamClient;
use Iam\IamException;

//使用租户用户名密码获取临时AK/SK/STS Token
//1.获取AuthToken
//2.获取AK/SK/STS Token
function testAuthToken(){
	$client = new IamClient([
		//IAM服务地址
		'Endpoint' => 'https://iam.myhuaweicloud.com'
	]);
	try{
		$ret = $client->createAuthToken([
			'UserName' => '<用户名>',
			'Password' => '<用户密码>',
			'DomainName' => '<租户名，一般跟用户名相同>'
		]);
		
		echo $ret['AuthToken']."\n";
		
		$ret = $client->createCredential([
			'AuthToken' => 	$ret['AuthToken'],
		    'expires' => '900'
		]);
		
		//临时AK
		echo $ret['Body']['credential']['access']."\n";
		//临时SK
		echo $ret['Body']['credential']['secret']."\n";
		//临时Token
		echo $ret['Body']['credential']['securitytoken']."\n";
	}catch (IamException $exception){
	    echo $exception->getStatusCode() . "\n";
		echo $exception->getMessage()."\n";
		echo $exception->getExceptionCode()."\n";
		echo $exception->getExceptionMessage()."\n";
	}
}

//使用租户的永久AK/SK获取临时AK/SK/STS Token
function testAKSK(){
	$client = new IamClient([
		//IAM服务地址
		'Endpoint' => 'https://iam.myhuaweicloud.com'
	]);
	try{
		$ret = $client->createCredential([
			//租户的永久AK/SK,定义 临时凭证的过期时间，单位秒
			'AK' => '<Your-AK>',
			'SK' => '<Your-SK>',
            'expires' => '900'		    
		]);
		
		//临时AK
		echo $ret['Body']['credential']['access']."\n";
		//临时SK
		echo $ret['Body']['credential']['secret']."\n";
		//临时Token
		echo $ret['Body']['credential']['securitytoken']."\n";
	}catch (IamException $exception){
	    echo $exception->getStatusCode() . "\n";
		echo $exception->getMessage()."\n";
		echo $exception->getExceptionCode()."\n";
		echo $exception->getExceptionMessage()."\n";
	}
}

testAuthToken();
//testAKSK();




