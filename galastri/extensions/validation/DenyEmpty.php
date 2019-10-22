<?php
/**
 * - DenyEmpty.php -
 * 
 * Validador usado pela classe Validation que verifica se o dado é vazio. Caso seja, então será
 * retornado erro.
 * 
 * Forma de uso (o comando abaixo não se trata de um exemplo):
 * 
 *     $validation
 *         ->validate(<dado>, <rótulo>)
 *             ->denyEmpty()
 *         ->execute();
 */
namespace galastri\extensions\validation;

trait DenyEmpty {
    /**
     * Método que verifica se o dado é vazio.
     */
    public function denyEmpty(){
        $this->beforeTest();
        $this->chain->create(
            "denyEmpty",
            [
                "name"   => "denyEmpty",
                "attach" => TRUE,
            ],
            (
                function($chainData, $data){
                    $error              = $this->error->status;
                    $this->debug->trace = debug_backtrace()[0];

                    if(!$error){
                        $testValue = $this->validation->value;

                        if(!is_bool($testValue) and ($testValue === "" or $testValue === NULL)){
                            $error = TRUE;
                        } elseif(is_array($testValue) === 0){
                            if(count([$testValue])) $error = TRUE;
                        }

                        if($error){
                            $errorLog["error"]       = $error;
                            $errorLog["testName"]    = "denyEmpty";
                            $errorLog["invalidData"] = NULL;
                            $errorLog["reason"]      = "empty_is_denied";

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