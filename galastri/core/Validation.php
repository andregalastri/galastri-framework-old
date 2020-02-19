<?php
/**
 * - Validation.php -
 * 
 * Classe que permite os mais variados tipo de validação de dados. Tal validação permite que se
 * saiba exatamente qual o dado não passou no teste, em qual teste a validação encontrou dados
 * inválidos e ainda quais são os dados inválidos.
 * 
 * A validação é subdivida entre 3 elementos:
 * 1. O dado a ser validado;
 * 2. O validador que executará o teste;
 * 3. Modificadores do validador.
 * 
 * O dado a ser validado é informado no método validate(), em que se informa o dado propriamente
 * dito e opcionalmente um rótulo de identificação, que pode ser uma string que facilite a
 * identificação a qual dado pertence aquela validação. Por exemplo:
 * 
 *     $validation->validate($minhaString, 'Nome do cliente')
 * 
 * No caso, o dado a ser validado é o conteúdo de $minhaString enquanto que 'Nome do cliente' é
 * o rótulo que permite que se identifique esta validação. Caso não se informe um rótulo, então
 * é atribuído um número referente à posição da validação. Se esta for a primeira validação, então
 * o o rótulo será o número 1, e assim por diante.
 * 
 * Um validador é usado logo seguido do dado informado. Por exemplo:
 * 
 *     $validation->validate($minhaString, 'Nome do cliente')
 *                ->charSet('Letters', 'Numbers)
 *                ->execute();
 * 
 * No caso, o validador CharSet irá verificar se $minhaString possui apenas letras ou números. Caso
 * positivo, a validação retorna true, do contrário, retornará false.
 * 
 * Um validador pode conter modificadores. O validador CharSet possui um modificador chamado
 * charException(), para caso existam caracteres que são excessão à permissão dada. Por exemplo:
 * 
 *     $validation->validate($minhaString, 'Nome do cliente')
 *                ->charSet('Letters', 'Numbers)
 *                ->charException('1', '2')
 *                ->execute();
 * 
 * No caso, o validador CharSet irá permitir todas as letras e números, mas o modificador
 * charException() irá configurar que, apesar de todos os números serem permitidos, os números 1
 * e 2 são uma exceção à regra, logo, caso o dado contenha ou o número 1, ou o número 2, então a
 * validação retornará que existem caracteres inválidos.
 * 
 * Mais detalhes sobre o funcionamento de cada validador dentro das explicações de cada validador
 * localizado na pasta extensions/validation.
 * 
 * Ainda é possível configurar mensagens de erros específicas para caso haja dados inválidos através
 * do método onError(). Por exemplo:
 * 
 *     $validation->validate($minhaString, 'Nome do cliente')
 *                ->charSet('Letters', 'Numbers)
 *                ->onError('Apenas letras ou números são permitidos')
 *                ->execute();
 * 
 * Neste caso a validação irá retornar a mensagem 'Apenas letras ou números são permitidos' caso
 * o dado contenha caracteres diferentes de letras e números.
 * 
 * IMPORTANTE:
 * Esta classe utiliza de Exception, portanto, ela deve ser executada dentro do comando try/catch,
 * pois caso ocorra uma falha, o PHP irá declarar que houve um Exception, mas que não havia um
 * catch preparado para recuperá-lo. Por isso, execute a validação dentro dos comandos try/catch,
 * por exemplo:
 * 
 *     try {
 *         $validation->validate($minhaString, 'Nome do cliente')
 *                    ->charSet('Letters', 'Numbers')
 *                    ->execute();
 *     } catch(Exception $e){}
 *     
 * Caso não compreenda como funciona o try/catch, basta usár a validação exatamente desta forma,
 * e tudo funcionará conforme o esperado, sem erros do PHP.
 */
namespace galastri\core;

use \galastri\extensions\Exception;

class Validation
{
    /**
     * Importação dos validadores.
     * Todos eles foram escritos como sendo traits ao invés de classes.
     * 
     * Os renderizadores padrão são:
     * 
     * charSet          Cria uma lista de quais são os caracteres permitidos no dado validado.
     * 
     * dataType         Cria uma lista de quais são os tipos de dados que o dado pode ser.
     * 
     * dateTime         Verifica se o campo é do tipo data e permite aplicar comparações da data
     *                  informada com outras datas.
     * 
     * denyEmpty        Impede que o dado seja vazio.
     * 
     * denyFalse        Impede que o dado seja false.
     * 
     * length           Verifica se o dado possui uma determinada quantidade de caracteres.
     * 
     * number           Verifica se o dado é do tipo número e permite aplicar comparações do
     *                  número informado com outros valores.
     * 
     * shortList        Cria uma lista de valores estritamente permitidos, impedindo que valores
     *                  diferentes dos informados na lista sejam válidos.
     */
    use \galastri\extensions\validation\CharSet;
    use \galastri\extensions\validation\Restriction;
    use \galastri\extensions\validation\Operators;
    use \galastri\extensions\validation\DataType;
    use \galastri\extensions\validation\DenyFalse;
    use \galastri\extensions\validation\Length;
    use \galastri\extensions\validation\Number;
    use \galastri\extensions\validation\Datetime;
    use \galastri\extensions\validation\ShortList;
    use \galastri\extensions\validation\DenyEmpty;

    private $charSet;
    private $onError;
    private $validation;
    private $validator;
    private $error;
    private $result;

    /**
     * O método contruct() define vários atributos padrão e principalmente alguns objetos StdClass
     * que servirão para retornar os dados.
     */
    public function __construct()
    {
        Debug::trace(debug_backtrace()[0]);

        $this->charSet             = new \StdClass();

        $this->onError             = new \StdClass();

        $this->validation          = new \StdClass();
        $this->validation->value   = null;
        $this->validation->counter = 0;
        $this->validation->label   = null;

        $this->validator           = null;

        $this->error               = new \StdClass();
        $this->error->status       = false;
        $this->error->data         = null;
        $this->error->reason       = null;
        $this->error->delimiter    = null;
        $this->error->format       = null;
        $this->error->spacer       = null;

        $this->result              = new \StdClass();
        $this->result->error       = false;
        $this->result->label       = null;
        $this->result->message     = null;
        $this->result->testValue   = null;
        $this->result->testName    = null;
        $this->result->invalidData = null;
        $this->result->reason      = null;
    }

    /**
     * Método que armazena o valor a ser validado e seu rótulo. Caso o rótulo não seja definido,
     * então é especificado um rótulo padrão que armazena o número com a posição do teste.
     * 
     * Este método é sempre o início de uma corrente, portanto, sempre que ele for colocado em
     * sequência, toda a cadeia anterior será resolvida para só então uma nova corrente seja
     * gerada. Essa resolução significará que as configurações serão reiniciadas caso não tenha
     * havido erro nos testes anteriores.
     * 
     * @param mixed $testValue         Armazena o valor a ser testado.
     * 
     * @param int|string $label        Armazena o rótulo identificador do teste.
     */
    public function validate($testValue, $label = false)
    {
        if(Chain::hasLinks()){
            $this->execute();
        }

        if(!$this->error->status){
            $this->charSet->case    = 'all';
            $this->onError->message = null;

            $this->validation->counter++;
            $this->validation->value = $testValue;
            $this->validation->label = $label === false ? 'default'.$this->validation->counter : $label;
        }

        return $this;
    }

    /**
     * Método que deve ser usado no final da cadeia de testes com o objetivo de executar a última
     * corrente que estiver em aberto.
     */
    public function execute()
    {
        return Chain::resolve();
    }

    /**
     * Método que armazena um elo na corrente com uma mensagem de erro para o teste anterior caso
     * este não seja validado.
     * 
     * Este método deve ser colocado logo à frente do validador ou do modificador que se deseja
     * que o erro seja verificado. Ele é reiniciado a cada método validate().
     * 
     * @param string $message          Mensagem de texto que será armazenada caso haja falha em
     *                                 algum dos testes.
     */
    public function onError($message, $format = 'invalidData', $spacer = ', ')
    {
        Chain::create(
            'onError',
            [
                'name'    => 'onError',
                'message' => $message,
                'format'  => $format,
                'spacer'  => $spacer,
                'attach'  => false,
            ],
            (function($chainData, $data){ return Chain::resolve($chainData, $data); })
        );
        return $this;
    }

    /**
     * Método que recupera o resultado da validação.
     */
    public function getResult()
    {
        $result        = new \StdClass();
        $result->error = $this->error->status;

        $result->invalidData = null;
        $result->reason      = null;
        $result->message     = null;
        $result->label       = null;
        $result->value       = null;
        $result->delimiter   = null;
        $result->validator   = null;

        if($this->error->status){
            $result->invalidData = $this->error->data;
            $result->reason      = $this->error->reason;
            $result->delimiter   = $this->error->delimiter;
            $result->label       = $this->validation->label;
            $result->value       = $this->validation->value;
            $result->validator   = $this->validator;

            $result->message     = $this->onError->message;

            if($this->error->format){
                $printf = $result->{$this->error->format} ?? false;
                if($printf !== false)
                    $result->message = sprintf($result->message, is_array($printf) ? implode($this->error->spacer, array_unique($printf)) : $printf);
            }
        }

        return $result;
    }

    /**
     * Método que define alguns valores caso a validação tenha encontrado algum erro.
     * 
     * @param array $testResult        Array contendo informações sobre o erro.
     */
    private function setValidationError($testResult)
    {
        $this->error->status    = $testResult['error'] ?? null;
        $this->validator        = $testResult['testName'] ?? null;
        $this->error->data      = $testResult['invalidData'] ?? null;
        $this->error->reason    = $testResult['reason'] ?? null;
        $this->error->delimiter = $testResult['delimiter'] ?? null;
        $this->error->format    = $testResult['format'] ?? false;
        $this->error->spacer    = $testResult['spacer'] ?? null;
        $this->onError->message = $testResult['message'] ?? null;

        $result = $this->getResult();

        throw new Exception($result->message, $result->reason);
    }

    /**
     * Método usado antes de cada validador para verificar se o método validade() foi usado antes.
     */
    private function beforeTest()
    {
        if($this->validation->counter === 0){
            Debug::error('VALIDATION001')::print(); 
        }
    }

    /**
     * Método que faz a comparação entre o valor e o delimitador. É usado em valores numéricos e
     * datas.
     * 
     * @param mixed $value             Valor do dado testado.
     * 
     * @param string $operator         Símbolo do operador a ser utilizado.
     * 
     * @param mixed $delimiter         Delimitador que será testado junto ao valor.
     */
    private function compare($value, $operator, $delimiter)
    {
        switch($operator){
            case '=='    :    return    $value == $delimiter;
            case '>'    :    return    $value >  $delimiter;
            case '<'    :    return    $value <  $delimiter;
            case '>='    :    return    $value >= $delimiter;
            case '<='    :    return    $value <= $delimiter;
            case '!='    :    return    $value != $delimiter;
        }
    }
}
