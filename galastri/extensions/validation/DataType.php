<?php
/**
 * - DataType.php -
 * 
 * Validador usado pela classe Validation que verifica o tipo de dado do dado testado e se ele
 * faz parte da lista de tipos de dados permitidos.
 * 
 * Forma de uso (o comando abaixo não se trata de um exemplo):
 * 
 *     $validation
 *         ->validate(<dado>, <rótulo>)
 *             ->dataType(<tipo de dado 1>, <tipo de dado 2>, ...)
 *         ->execute();
 */
namespace galastri\extensions\validation;

use galastri\core\Chain;
use galastri\core\Debug;

trait DataType
{
    /**
     * Método que realiza o teste verificando se o dado testado é do tipo permitido.
     * 
     * @param string $allowedTypes     Tipos de dados permitidos. Caso o dado não seja de nenhum
     *                                 dos tipos que fazem parte da lista, a validação retorna
     *                                 erro.
     */
    public function dataType(...$allowedTypes)
    {
        $this->beforeTest();

        Chain::create(
            'dataType',
            [
                'name'         => 'dataType',
                'allowedTypes' => is_array($allowedTypes[0]) ? $allowedTypes[0] : $allowedTypes,
                'attach'       => true,
            ],
            (
                function($chainData, $data)
                {
                    Debug::trace(debug_backtrace()[0]);

                    $error = $this->error->status;

                    if(!$error){
                        $testValue    = $this->validation->value;
                        $allowedTypes = $data['allowedTypes'];
                        
                        if(!(!($testValue === '0' or $testValue === 0) and ($testValue === '' or $testValue === null or empty($testValue)))){

                            /** Uma observação importante:
                             * A função usada para verificar o tipo de dado é a gettype(). Esta
                             * função do PHP retorna 'double' mesmo quando o tipo de dado for
                             * 'float', já que para o PHP ambos são equivalentes. */
                            $dataTypes = [
                                'bool'     => 'boolean',
                                'boolean'  => 'boolean',
                                'int'      => 'integer',
                                'integer'  => 'integer',
                                'float'    => 'double',
                                'double'   => 'double',
                                'decimal'  => 'double',
                                'str'      => 'string',
                                'string'   => 'string',
                                'arr'      => 'array',
                                'array'    => 'array',
                                'obj'      => 'object',
                                'object'   => 'object',
                                'res'      => 'resource',
                                'resource' => 'resource',
                                'null'     => 'null',
                            ];

                            foreach($allowedTypes as &$type) $type = $dataTypes[$type] ?? $type;
                            unset($type);

                            $search = array_search(gettype($testValue), $allowedTypes, true);
                            $error  = $search === false ? true : false;

                            if($error){
                                $errorLog['error']       = $error;
                                $errorLog['testName']    = 'dataType';
                                $errorLog['invalidData'] = $testValue;
                                $errorLog['reason']      = 'invalid_type_'.gettype($testValue);
                                $errorLog['message']     = $data['message'];

                                $this->setValidationError($errorLog);
                            }
                        }

                        return Chain::resolve($chainData, $data);
                    }
                }
            )
        );
        return $this;
    }
}
