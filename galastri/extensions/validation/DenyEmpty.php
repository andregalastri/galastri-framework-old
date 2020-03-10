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

use galastri\core\Chain;
use galastri\core\Debug;

trait DenyEmpty
{
    /**
     * Método que verifica se o dado é vazio.
     */
    public function denyEmpty()
    {
        $this->beforeTest();

        Chain::create(
            'denyEmpty',
            [
                'name'   => 'denyEmpty',
                'attach' => true,
            ],
            (
                function($chainData, $data)
                {
                    Debug::trace(debug_backtrace()[0]);

                    $error = $this->error->status;

                    if(!$error){
                        $testValue = $this->validation->value;

                        if(!(is_bool($testValue) or $testValue === '0' or $testValue === 0) and ($testValue === '' or $testValue === null or empty($testValue)))
                            $error = true;

                        if($error){
                            $errorLog['error']       = $error;
                            $errorLog['testName']    = 'denyEmpty';
                            $errorLog['invalidData'] = null;
                            $errorLog['reason']      = 'empty_is_denied';
                            $errorLog['message']     = $data['message'];

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
