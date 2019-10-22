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

trait DenyFalse {
    /**
     * Método que verifica se o dado é FALSE.
     */
    public function denyFalse(){
        $this->beforeTest();
        $this->chain->create(
            "denyFalse",
            [
                "name"   => "denyFalse",
                "attach" => TRUE,
            ],
            (
                function($chainData, $data){
                    $data               = end($chainData);
                    $error              = $this->error->status;
                    $this->debug->trace = debug_backtrace()[0];

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

                        return $this->chain->resolve($chainData, $data);
                    }
                }
            )
        );
        return $this;
    }
}