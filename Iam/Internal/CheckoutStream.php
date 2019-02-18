<?php
namespace Iam\Internal;

use Psr\Http\Message\StreamInterface;
use GuzzleHttp\Psr7\StreamDecoratorTrait;
use Iam\IamException;

class CheckoutStream implements StreamInterface {
    use StreamDecoratorTrait;
    
    private $expectedLength;
    private $readedCount = 0;

    public function __construct(StreamInterface $stream, $expectedLength) {
        $this->stream = $stream;
        $this->expectedLength = $expectedLength;
    }

    public function getContents() {
        $contents = $this->stream->getContents();
        $length = strlen($contents);
        if ($this->expectedLength !== null && floatval($length) !== $this->expectedLength) {
        	$this -> throwIamException($this->expectedLength, $length);
        }
        return $contents;
    }

    public function read($length) {
        $string = $this->stream->read($length);
        if ($this->expectedLength !== null) {
            $this->readedCount += strlen($string);
            if ($this->stream->eof()) {
                if (floatval($this->readedCount) !== $this->expectedLength) {
                	$this -> throwIamException($this->expectedLength, $this->readedCount);
                }
            }
        }    
        return $string;
    }

    public function throwIamException($expectedLength, $reaLength) {
    	$iamException = new IamException('premature end of Content-Length delimiter message body (expected:' . $expectedLength . '; received:' . $reaLength . ')');
    	$iamException->setExceptionType('server');
    	throw $iamException;
    }
}

