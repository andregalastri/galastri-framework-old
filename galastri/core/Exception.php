<?php
namespace galastri\core;

class Exception extends \Exception
{
    public function __construct($message = null, $code = 0, Exception $previous = null){
        parent::__construct($message, 0, $previous);
        $this->code = $code;
    }
}