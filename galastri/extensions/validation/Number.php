<?php
/**
 * - Number.php -
 * 
 * Validador usado pela classe Validation que verifica se um dado é um número de um tipo específico
 * e permite permite delimitar o valor deste número usando métodos de comparação.
 * 
 * Forma de uso (o comando abaixo não se trata de um exemplo):
 * 
 *     $validation
 *     ->validate(<dado>, <rótulo>)
 *         ->number(<tipo do número>)
 *             ->min(<valor mínima>)
 *             ->max(<valor máximo>)
 *             ->diff(<valor diferente de>)
 *             ->equal(<valor igual a>)
 *             ->smaller(<valor menor que>)
 *             ->greater(<valor maior que>)
 *         ->execute();
 */
namespace galastri\extensions\validation;

use galastri\core\Chain;
use galastri\core\Debug;

trait Number
{
    /**
     * Método que verifica se o dado é um número de um tipo específico e permite fazer a comparação
     * deste dado com delimitadores através de métodos de comparação.
     * 
     * @param string $type             Nome do tipo de dado que se espera que o número seja.
     */
    public function number($type)
    {
        $this->beforeTest();
        Chain::create(
            'number',
            [
                'name'   => 'number',
                'type'   => $type,
                'attach' => true,
            ],
            (
                function($chainData, $data)
                {
                    Debug::trace(debug_backtrace()[0]);

                    $error = $this->error->status;

                    if(!$error){
                        $testValue = $this->validation->value;
                        $type      = $data['type'];

                        foreach($chainData as $parameter){
                            switch($parameter['name']){
                                case 'number':
                                    $dataTypes = [
                                        'int'     => 'integer',
                                        'integer' => 'integer',
                                        'float'   => 'double',
                                        'double'  => 'double',
                                        'decimal' => 'double',
                                    ];

                                    if(!isset($dataTypes[$type])){
                                        Debug::error('NUMBER001', $type, implode(',', array_keys($dataTypes))); 
                                    }

                                    if($dataTypes[$type] !== gettype($testValue)){
                                        if(!($dataTypes[$type] === 'double' and gettype($testValue) === 'integer')){
                                            $error = true;
                                            $errorLog['invalidData'] = $testValue;
                                            $errorLog['reason']      = 'number_type';
                                            $errorLog['message']     = $parameter['message'];
                                            break 2;
                                        }
                                    }

                                    if(isset($operation)){
                                        foreach($operation as $operator){
                                            if(!$this->compare($testValue, $operator['operator'], $operator['delimiter'])){
                                                $error = true;
                                                $errorLog['invalidData'] = $testValue;
                                                $errorLog['reason']      = 'number_size';
                                                $errorLog['message']     = $operator['message'];
                                                break 3;
                                            }
                                        }
                                    }
                                    break;

                                case 'min':
                                case 'max':
                                case 'smaller':
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
                            $errorLog['testName'] = 'number';

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
