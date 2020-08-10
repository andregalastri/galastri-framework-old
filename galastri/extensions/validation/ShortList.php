<?php
/**
 * - ShortList.php -
 * 
 * Validador usado pela classe Validation que verifica se o dado pertence a uma lista restrita de
 * valores possíveis. Esta lista representa os únicos valores possíveis que o dado pode conter.
 * Caso o dado não seja igual a nenhum dos valores da lista, o dado é considerado inválido.
 * 
 * A comparação entre os dados pode ser estrita ou não. Uma comparação estrita significa que tanto
 * o valor quanto o tipo de dado é verificado. Já quando a comparação não é estrita, significa
 * que mesmo dados de tipos diferentes, mas cujo valor é comparável, serão considerados iguais.
 * 
 * Por exemplo: (int)0 e (string)'0' são de tipos diferentes, mas armazenam um valor comparativamente
 * semelhante. Numa comparação estrita, estes dois dados são considerados diferentes. Já numa
 * comparação não estrita, estes dados são iguais.
 * 
 * O mesmo ocorre numa comparação entre 0 e false, ou 1 e true.
 * 
 * Para se definir que a comparação é estrita ou não, basta utilizar um dos métodos de restrição
 * logo após o método shortList(): o método strict() configura a comparação para ser estrita; o
 * método noStrict() configura a comparação para ser não estrita. Por exemplo:
 * 
 *     $status = '3';
 *     $validation->validate($estado)
 *                ->shortList('0', '1', '2', '3')
 *                ->strict()
 *                ->execute();
 * 
 * A comparação acima será estrita, ou seja, caso $status seja igual a (int)3, haverá erro, já que
 * os valores possíveis são apenas strings (mesmo que haja ali um número 3)
 * 
 * A lista pode ser definida item a item, separando cada parâmetro por vírgulas, ou uma array contendo
 * todos os dados possíveis de uma vez. Por exemplo:
 * 
 *     $status = '3';
 *     $list = ['0', '1', '2', '3'];
 * 
 *     $validation->validate($estado)
 *                ->shortList($list)
 *                ->strict()
 *                ->execute();
 * 
 * Forma de uso (o comando abaixo não se trata de um exemplo):
 * 
 *     $validation
 *         ->validate(<dado>, <rótulo>)
 *             ->shortList(<lista de valores permitidos>...)
 *                 ->strict()
 *                 ->noStrict()
 *             ->execute();
 */
namespace galastri\extensions\validation;

use galastri\core\Chain;
use galastri\core\Debug;

trait ShortList
{
    /**
     * Método que verifica se o dado corresponde a um dos itens da lista, não sendo validado caso
     * ele tenha outro valor.
     * 
     * @param mixed $shortList         Lista de valores possíveis que o dado pode possuir.
     */
    public function shortList(...$shortList)
    {
        $this->beforeTest();
        
        Chain::create(
            'shortList',
            [
                'name'      => 'shortList',
                'shortList' => is_array($shortList[0]) ? $shortList[0] : $shortList,
                'attach'    => true,
            ],
            (
                function($chainData, $data)
                {
                    Debug::trace(debug_backtrace()[0]);

                    $error     = $this->error->status;
                    $shortList = $data['shortList'];

                    if(!$error) {
                        $testValue = $this->validation->value;
                        $strict    = 'notStrict';

                        if($testValue === '' or $testValue === null)
                            return Chain::resolve($chainData, $data);
                        
                        foreach($chainData as $parameter) {
                            switch($parameter['name']) {
                                case 'shortList':
                                    $found = false;
                                    
                                    foreach($shortList as $delimiter){
                                        switch($strict){
                                            case 'notStrict':
                                                if($testValue ==  $delimiter){
                                                    $found = true;
                                                    break 2;
                                                }
                                                break;
                                                
                                            case 'strict':
                                                if($testValue ===  $delimiter){
                                                    $found = true;
                                                    break 2;
                                                }
                                                break;
                                        }
                                    }
                                    
                                    if(!$found){
                                        $error = true;
                                        $errorLog['message'] = $parameter['message'];
                                    }
                                    
                                    break;

                                case 'strict':
                                    $strict = 'strict';
                                    break;
                                    
                                case 'notStrict':
                                    $strict = 'notStrict';
                                    break;
                            }
                        }

                        if($error){
                            $errorLog['error']       = $error;
                            $errorLog['testName']    = 'shortList';
                            $errorLog['invalidData'] = $testValue;
                            $errorLog['reason']      = 'option_not_found';
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
