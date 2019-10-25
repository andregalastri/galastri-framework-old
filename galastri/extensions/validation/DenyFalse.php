<?php
/**
 * - DenyFalse.php -
 * 
 * Validador usado pela classe Validation que verifica se o dado é FALSE. Caso seja, então será
 * retornado erro.
 * 
 * Forma de uso (o comando abaixo não se trata de um exemplo):
 * 
 *     $validation
 *         ->validate(<dado>, <rótulo>)
 *             ->denyFalse()
 *         ->execute();
 */
namespace galastri\extensions\validation;
use       galastri\core\Chain;
use       galastri\core\Debug;

trait DenyFalse {
    /**
     * Método que verifica se o dado é FALSE.
     */
    public function denyFalse(){
        $this->beforeTest();
        
        Chain::create(
            "denyFalse",
            [
                "name"   => "denyFalse",
                "attach" => TRUE,
            ],
            (
                function($chainData, $data){
                    Debug::trace(debug_backtrace()[0]);

                    $data  = end($chainData);
                    $error = $this->error->status;

                    if(!$error){
                        $testValue = $this->validation->value;
                        $error     = $testValue === FALSE ? TRUE : FALSE;

                        if($error){
                            $errorLog["error"]       = $error;
                            $errorLog["testName"]    = "denyFalse";
                            $errorLog["invalidData"] = $testValue;
                            $errorLog["reason"]      = "false_is_denied";

                            $this->setValidationError($errorLog);
                        }

                        return Chain::resolve($chainData, $data);
                    }
                }
            )
        );
        return $this;
    }
}