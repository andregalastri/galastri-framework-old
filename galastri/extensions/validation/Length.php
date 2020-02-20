<?php
/**
 * - Length.php -
 * 
 * Validador usado pela classe Validation que verifica a quantidade de caracteres de um dado e
 * permite delimitar a quantidade desses caracteres usando métodos de configuração de comparação.
 * 
 * Forma de uso (o comando abaixo não se trata de um exemplo):
 * 
 *     $validation
 *         ->validate(<dado>, <rótulo>)
 *             ->length()
 *                 ->min(<quantidade mínima>)
 *                 ->max(<quantidade máximo>)
 *                 ->diff(<quantidade diferente de>)
 *                 ->equal(<quantidade igual a>)
 *                 ->smaller(<quantidade menor que>)
 *                 ->greater(<quantidade maior que>)
 *         ->execute();
 */
namespace galastri\extensions\validation;

trait Length
{
    /**
     * Método que verifica a quantidade de caracteres do dado.
     */
    public function length()
    {
        $this->beforeTest();
        
        Chain::create(
            'length',
            [
                'name'   => 'length',
                'attach' => true,
            ],
            (
                function($chainData, $data)
                {
                    Debug::trace(debug_backtrace()[0]);
                    
                    $error = $this->error->status;

                    if(!$error){
                        $testValue = $this->validation->value;

                        foreach($chainData as $parameter){
                            switch($parameter['name']){
                                
                                /** Compara a quantidade de caracteres do dado com a quantidade
                                 * especificada nos métodos de comparação. */
                                case 'length':
                                    if($testValue === '' or $testValue === null)
                                        break;
                                    
                                    foreach($operation as $operator){
                                        if(!$this->compare(strlen($testValue), $operator['operator'], $operator['delimiter'])){
                                            $error = true;
                                            $errorLog['invalidData'] = strlen($testValue);
                                            $errorLog['reason']      = 'length_'.strlen($testValue);
                                            $errorLog['message']     = $operator['message'];
                                            break 3;
                                        }
                                    }
                                    break;

                                case 'min':
                                case 'max':
                                case 'lesser':
                                case 'greater':
                                case 'equal':
                                case 'diff':
                                    $operation[] = [
                                        'operator'  => $parameter['operator'],
                                        'delimiter' => $parameter['delimiter'],
                                        'message'   => $parameter['message'],
                                    ];
                                    break;
                            }
                        }

                        if($error){
                            $errorLog['error']    = $error;
                            $errorLog['testName'] = 'length';

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
