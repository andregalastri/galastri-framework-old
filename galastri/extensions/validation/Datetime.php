<?php
/**
 * - Datetime.php -
 * 
 * Validador usado pela classe Validation que verifica se o dado é uma data válida. Também permite
 * o uso de configurações de comparação, para testar se a data, quando válida, tem valor maior,
 * menor, igual ou diferente a uma outra data.
 * 
 * É necessário informar como parâmetro o formato da data que está sendo testada, pois este método
 * se utiliza da classe/método DateTime::createFromFormat, que requer que o formato da string de
 * data seja informado. Por exemplo:
 * 
 *     $data = '01/01/2019';
 *     $validation->validate($data, 'Data venda')
 *                ->dateTime('d/m/Y')
 *                ->execute();
 * 
 * Os métodos de configuração de comparação podem ser strings como o dado testado. Caso este seja
 * do mesmo formato do dado, não é necessário especificar o formato novamente. Caso o formato seja
 * diferente, então é necessário informar, como segundo parâmetro, o formato da data delimitadora.
 * Por exemplo:
 * 
 *     $data = '01/01/2019';
 *     $validation->validate($data, 'Data venda')
 *                ->dateTime('d/m/Y')
 *                ->max('2019-02-01', 'Y-m-d')
 *                ->execute();
 * 
 * Este método permite também o uso de objetos da classe DateTime como dados de teste.
 * 
 * Por fim, o uso de configurações de comparação permite o uso de strings como as usadas na classe
 * DateTime para especificar datas baseadas na data atual.
 * 
 *     $data = '01/01/2019';
 *     $validation->validate($data, 'Data venda')
 *                ->dateTime('d/m/Y')
 *                ->min('Today')
 *                ->max('Today +5 days')
 *                ->execute();
 * 
 * Forma de uso (o comando abaixo não se trata de um exemplo):
 * 
 *     $validation
 *         ->validate(<dado>, <rótulo>)
 *             ->dateTime(<formato da data do dado>)
 *                 ->min(<Data mínima>, <formato>)
 *                 ->max(<Data máxima>, <formato>)
 *                 ->diff(<Data diferente de>, <formato>)
 *                 ->equal(<Data igual a>, <formato>)
 *                 ->smaller(<Data menor que>, <formato>)
 *                 ->greater(<Data maior que>, <formato>)
 *             ->execute();
 */
namespace galastri\extensions\validation;

use galastri\core\Chain;
use galastri\core\Debug;

trait Datetime
{
    /**
     * Método que verifica se o dado contém uma data válida baseada no formato informado.
     * 
     * @param string $dateFormat       Formato da data. Utiliza-se dos parâmetros de data comuns
     *                                 da classe DateTime do PHP.
     */
    public function dateTime($dateFormat)
    {
        $this->beforeTest();
        Chain::create(
            'dateTime',
            [
                'name'       => 'dateTime',
                'dateformat' => $dateFormat,
                'attach'     => true,
            ],
            (
                function($chainData, $data)
                {
                    Debug::trace(debug_backtrace()[0]);
                    
                    $error = $this->error->status;

                    if(!$error){
                        $testValue  = $this->validation->value;
                        $dateFormat = $data['dateformat'];

                        if(empty($testValue))
                            return Chain::resolve($chainData, $data);

                        /** Verifica se o dado informado é um objeto do tipo DateTime. Caso seja,
                         * então ele será utilizado. Caso seja apenas uma string, então ele deverá
                         * ser convertido em um objeto do tipo DateTime.
                         * 
                         * Caso a conversão da data dê errado, ocorrerá um erro. */
                        if(is_object($testValue)){
                            if(get_class($testValue) !== 'DateTime'){
                                Debug::error('DATETIME002');
                            } else {
                                $testDatetime = $testValue;
                                $testDatetime->format($dateFormat);
                            }
                        } else {
                            $testDatetime = \DateTime::createFromFormat("!$dateFormat", $testValue);
                            
                            if(!empty(array_filter(\DateTime::getLastErrors()))){
                                $error = true;
                                $errorLog['reason']  = 'invalid_datetime';
                                $errorLog['message'] = $data['message'];
                                $errorLog['format']  = $data['format'];
                            }
                        }

                        if(!$error){
                            foreach($chainData as $parameter){
                                switch($parameter['name']){
                                    /** Caso a data tenha formato válido, então é verificado se
                                     * existem operações configuradas nos métodos de comparação.
                                     * Caso existam, então o delimitador sofre a mesma verificação
                                     * que o dado testado, para se garantir que o delimitador
                                     * também seja uma data válida.
                                     * 
                                     * Caso ela seja um objeto do tipo DateTime, então ela é apenas
                                     * usada como está. Caso o delimitador for uma flag como 'Now',
                                     * 'Today', etc, esta será convertida para um objeto DateTime
                                     * válido baseado nestas flags.
                                     * 
                                     * As flags possíveis são:
                                     * Now        Cria um objeto DateTime com o dia e hora atuais.
                                     * Today      Cria um objeto DateTime com o dia atual.
                                     * Yesterday  Cria um objeto DateTime com o dia anterior ao
                                     *            atual.
                                     * Tomorrow   Cria um objeto DateTime com o dia seguinte ao
                                     *            atual.
                                     * 
                                     * Juntamente com flags é possível usar incrementos ou decrementos,
                                     * adicionando ou removendo dias, horas, da flag usada.
                                     * 
                                     * Exemplo: ->min('Today + 2 Days')
                                     *              ->max('Yesterday + 5 Years')
                                     * */
                                    case 'dateTime':
                                        if(isset($operation)){
                                            foreach($operation as $operator){
                                                $delimiterValue  = $operator['delimiterValue'];
                                                $delimiterFormat = $operator['delimiterFormat'] === false ? $dateFormat : $operator['delimiterFormat'];
                                                
                                                if(is_object($delimiterValue)){
                                                    if(get_class($delimiterValue) !== 'DateTime'){
                                                        Debug::error('DATETIME004');
                                                    } else {
                                                        $delimiterDatetime = $delimiterValue;
                                                    }
                                                } else {
                                                    $flags          = ['Now','Yesterday','Today','Tomorrow'];
                                                    $increment      = ['Millisecond','Second','Minute','Hour','Day','Week','Month','Year'];
                                                    $regexDatetime  = '.*(?=[\+|\-]?)';
                                                    $regexFlag      = implode('|',$flags);
                                                    $regexIncrement = '[\+|\-][\s]?[0-9].+['.implode('|',$increment).']';

                                                    preg_match("/$regexDatetime/u", $delimiterValue, $matchDatetime);
                                                    preg_match("/$regexFlag/u", $delimiterValue, $matchFlag);
                                                    preg_match("/$regexIncrement/u", $delimiterValue, $matchIncrement);

                                                    $matchDatetime  = empty($matchDatetime)  ? false : trim($matchDatetime[0]);
                                                    $matchFlag      = empty($matchFlag)      ? false : $matchFlag[0];
                                                    $matchIncrement = empty($matchIncrement) ? false : $matchIncrement[0];

                                                    if($matchFlag){
                                                        $delimiterDatetime = new \DateTime($delimiterValue);
                                                        $delimiterDatetime->format($delimiterFormat);
                                                    } else {
                                                        $delimiterDatetime = \DateTime::createFromFormat("!$delimiterFormat", $matchDatetime);

                                                        if(!empty(array_filter(\DateTime::getLastErrors()))){
                                                            $error = true;
                                                            $errorLog['reason'] = 'invalid_delimiter_datetime';
                                                            $errorLog['message'] = $parameter['message'];
                                                            $errorLog['format'] = $parameter['format'];
                                                            break 3;
                                                        } else {
                                                            if($matchIncrement){
                                                                $delimiterDatetime->modify($delimiterFormat);
                                                            }
                                                        }
                                                    }
                                                }

                                                /** Estando o delimitador configurado corretamente,
                                                 * a comparação entre as datas é executada,
                                                 * retornando seu resultado. */
                                                if(!$error){
                                                    if(!$this->compare($testDatetime, $operator['operator'], $delimiterDatetime)){
                                                        $error = true;
                                                        $errorLog['reason']    = 'datetime_doesnt_passed_the_test';
                                                        $errorLog['message']   = $operator['message'];
                                                        $errorLog['format']    = $operator['format'];
                                                        $errorLog['delimiter'] = $delimiterDatetime->format($delimiterFormat);
                                                        break 3;
                                                    }
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
                                            'operator'        => $parameter['operator'],
                                            'delimiterValue'  => $parameter['delimiter'],
                                            'delimiterFormat' => $parameter['optional'],
                                            'message'         => $parameter['message'],
                                            'format'          => $parameter['format'],
                                        ];
                                        break;
                                }
                            }
                        }

                        if($error){
                            $errorLog['error']       = $error;
                            $errorLog['testName']    = 'dateTime';
                            $errorLog['invalidData'] = $testValue;

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
