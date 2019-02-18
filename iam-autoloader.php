<?php

$mapping = [
	'Iam\IamClient' => __DIR__.'/Iam/IamClient.php',
	'Iam\IamException' => __DIR__.'/Iam/IamException.php',
	'Iam\Internal\CheckoutStream' => __DIR__.'/Iam/Internal/CheckoutStream.php',
	'Iam\Internal\CommonTrait' => __DIR__.'/Iam/Internal/CommonTrait.php',
	'Iam\Internal\CredentialTrait' => __DIR__.'/Iam/Internal/CredentialTrait.php',
	'Iam\Internal\Model' => __DIR__.'/Iam/Internal/Model.php',
	'Iam\Internal\SchemaFormatter' => __DIR__.'/Iam/Internal/SchemaFormatter.php',
	'Iam\Internal\SdkCurlFactory' => __DIR__.'/Iam/Internal/SdkCurlFactory.php',
	'Iam\Internal\SdkStreamHandler' => __DIR__.'/Iam/Internal/SdkStreamHandler.php',
	'Iam\Internal\Signature' => __DIR__.'/Iam/Internal/Signature.php',
	'Iam\Internal\ToArrayInterface' => __DIR__.'/Iam/Internal/ToArrayInterface.php',
	'Iam\Internal\TokenTrait' => __DIR__.'/Iam/Internal/TokenTrait.php',
	'iam-autoloader' => __DIR__.'/iam-autoloader.php',
	'TestDemo' => __DIR__.'/TestDemo.php',
];

spl_autoload_register(function ($class) use ($mapping) {
    if (isset($mapping[$class])) {
        require $mapping[$class];
    }
}, true);
