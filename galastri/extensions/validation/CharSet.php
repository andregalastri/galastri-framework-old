<?php
/**
 * - CharSet.php -
 * 
 * Validador usado pela classe Validation que verifica os caracteres do dado testado. O validador
 * trabalha em modo de permissão, ou seja, cada caractere ou grupo de caracteres que estiver
 * definido nos parâmetros é permitido e todos os que não estiverem definidos são caracteres
 * bloqueados.
 * 
 * Trata-se de restringir os caracteres permitidos aos caracteres definidos.
 * 
 * É possível adicionar modificadores ao validador, sendo eles:
 * 
 * charException        Permite informar exceções. Suponha que o dado testado é válido desde
 *                      que ele contenha quaisquer letras do alfabeto com exceção das letras
 *                      'a' e 'b'. Neste caso, é possível definir todas as letras no método
 *                      charSet() e usar o modificador charException() para configurar as
 *                      exceções.
 * 
 * charRequired         Permite informar um conjunto de caracteres obrigatórios. Por exemplo,
 *                      suponha que o dado a ser testado tenha que conter ao menos uma letra
 *                      'a' ou 'b'. Neste caso é possível usar o charRequired para definir que
 *                      o dado testado deva conter ao menos uma letra 'a' ou 'b'.
 *                             
 *                      Este modificar permite ainda configurações de restrição como o uso do
 *                      método any(), que define que o dado deve conter qualquer um dos
 *                      caracteres definidos no conjunto, ou o método all() que define que o
 *                      o dado deve conter ao menos um de cada um dos caracteres do conjunto.
 *                      O padrão é any().
 *                            
 *                      Também é possível utilizar configurações de comparação de quantidade,
 *                      definindo uma quantidade mínima ou máxima de caracteres obrigatórios,
 *                      por exemplo.
 *                      O padrão é min(1).
 * 
 * caseUpper,           Permite definir se as letras permitidas devem ser maíusculas, minísculas
 * caseLower,           ou se isto é indiferente para o teste. O padrão é caseAll().
 * caseAll              
 * 
 * Forma de uso (o comando abaixo não se trata de um exemplo):
 * 
 *     $objeto
 *         ->validate(<dado>, <rótulo>)
 *             ->charSet(<lista de caracteres permitidos>)
 *                 ->charException(<lista de exceção>)
 *                 ->charRequired(<lista de caracteres obrigatórios>)
 *                     ->any()
 *                     ->all()
 *                     ->min(<quantidade mínima>)
 *                     ->max(<quantidade máxima>)
 *                     ->diff(<diferente de>)
 *                     ->equal(<igual a>)
 *                     ->smaller(<menor do que>)
 *                     ->greater(<maior do que>)
 *             ->execute();
 */
namespace galastri\extensions\validation;

use galastri\core\Chain;
use galastri\core\Debug;

trait CharSet
{
    /**
     * Método validador que cria um elo na corrente de verificações que testa se o dado testado
     * possui um caractere ou conjunto de caracteres configurado. Caso o dado possua algum
     * caractere que não foi definido como permitido, então a validação retorna erro.
     * 
     * Pode-se definir os caracteres separadamente, em conjunto ou em predefinições. Por exemplo:
     * 
     *     ->charSet('a', 'b', 'c', '0', '1', '2')     // Caracteres separados.
     *     ->charSet('a-c', '0-2')                     // Em conjunto.
     *     ->charSet('Letters', 'Numbers')             // Predefinições.
     * 
     * As predefinições permitem que se utilize conjuntos prontos ou caracteres que não são
     * reconhecidos sem o uso de sinais adicionais. São configurados para serem usados através de
     * nomes de tags, sendo elas:
     * 
     * Numbers             Permite números de 0 a 9.
     * 
     * NumbersUtf8         Permite quaisquer números, desde os mais comuns (0 a 9) até caracteres
     *                     romanos, japoneses, persas, em formato unicode.
     *                        
     * Letters             Permite letras de A a Z.
     *  
     * LettersUtf8         Permite quaiquer letras, desde letras comuns (A a Z) até caracteres
     *                     com acentuação, caracteres árabes, japoneses, em formato unicode.
     *  
     * Article             Permite caracteres que geralmente são usados em artigos, como letras,
     *                     números, caracteres especiais e espaços.
     * 
     * Namefield           Permite caracteres que geralmente fazem parte de campos de nome, como
     *                     letras quaisquer e espaços.
     *
     * SpecialChars        Permite todos os caracteres especiais como !, @, #, etc.
     *  
     * Accents             Permite caracteres acentuados mais comuns.
     * 
     * ExtendedAccents     Permite caracteres acentuados mais incomuns.
     * 
     * Spaces              Permite espaços.
     * 
     * Backslashes         Permite barras invertidas \.
     * 
     * DoubleQuotes        Permite aspas duplas ".
     * 
     * SingleQuotes        Permite aspas simples '.
     * 
     * @param string $charSet          Caractere, conjunto de caracteres ou tag de predefinição.
     */
    public function charSet(...$charSet)
    {
        $this->beforeTest();

        Chain::create(
            'charSet',
            [
                'name'    => 'charSet',
                'charSet' => is_array($charSet[0]) ? $charSet[0] : $charSet,
                'attach'  => true,
            ],
            (
                function($chainData, $data)
                {
                    Debug::trace(debug_backtrace()[0]);

                    $error   = $this->error->status;
                    $charSet = $data['charSet'];

                    if(!$error){
                        $testValue  = $this->validation->value;
                        $require    = 'any';
                        $exception  = [];

                        /** O dado é testado logo de início, verificando os caracteres que
                         * correspondem e os que não correspondem com a definição.
                         * 
                         * Os caracteres que correspondem com a definição são definidos como sendo
                         * os que estão DENTRO do grupo permitido, enquanto que os que não
                         * correspondem com a definição são considerados os que estão FORA do
                         * grupo permitido. */
                        $mainMatch   = $this->charMatch($charSet);

                        $mainInside  = $mainMatch[0];
                        $mainOutside = $mainMatch[1];

                        /** Variáveis que conterão a correspondência e a não correspondência final
                         * do teste. Por princípio, elas têm o mesmo valor das variáveis acima, mas
                         * isso poderá mudar caso existam modificadores charException(). */
                        $finalInside  = $mainInside;
                        $finalOutside = $mainOutside;

                        foreach($chainData as $parameter){
                            switch($parameter['name']){

                                /** Verifica o resultado final. Caso existam dados fora do grupo
                                 * de permissão, significa que o dado informado é inválido. */
                                case 'charSet':
                                    if(!empty($finalOutside)){
                                        $error = true;
                                        $errorLog['invalidData'] = $finalOutside;
                                        $errorLog['reason']      = 'invalid_char';
                                        $errorLog['message']     = $parameter['message'];
                                        $errorLog['format']      = $parameter['format'];
                                        $errorLog['spacer']      = $parameter['spacer'];
                                        break 2;
                                    }
                                    break;

                                /** Verifica o modificador charRequired que, se estiver definido,
                                 * então fará um novo teste de correspondência dos caracteres.
                                 * Neste novo teste, é levado em conta se existem caracteres fora
                                 * do grupo de permitidos.
                                 * 
                                 * Caso não exista, significa que dentro do grupo de permitidos
                                 * deverão existir os caracteres definidos no conjunto de caracteres
                                 * obrigatórios.
                                 * 
                                 * Caso haja configuração de restrição para o tipo 'all', então
                                 * cada um dos caracteres requeridos deverá estar contido ao menos
                                 * uma vez no dado testado. Caso seja do tipo 'any', então a
                                 * quantidade será a soma destes caracteres quando encontrados.
                                 * 
                                 * É realizado o teste através do comparador. Caso haja configuração
                                 * de comparação, então as quantidades são definidas pela operação.
                                 * */
                                case 'charRequired':
                                    if($testValue === '' or $testValue === null)
                                        break;
                                    
                                    $requiredMatch = $this->charMatch($parameter['charSet'])[0];

                                    if(!empty($requiredMatch)){
                                        $matchCount = array_count_values($finalInside);

                                        foreach($parameter['charSet'] as $requiredChar){
                                            $matchSum[$requiredChar] = $matchCount[$requiredChar] ?? 0;
                                        }

                                        $compareValues = $require === 'all' ? $matchSum : [array_sum($matchSum)];

                                        if(!isset($operation)){
                                            $operation[] = [
                                                'operator'  => '>=',
                                                'delimiter' => 1,
                                                'message'   => $parameter['message'],
                                                'format'    => $parameter['format'],
                                                'spacer'    => $parameter['spacer'],
                                            ];
                                        }

                                        foreach($compareValues as $key => $sum){
                                            foreach($operation as $operator){

                                                if(!$this->compare($sum, $operator['operator'], $operator['delimiter'])){
                                                    $error = true;
                                                    $errorLog['invalidData'] = $matchSum;
                                                    $errorLog['reason']      = 'requiared_char_qty_'.$operator['delimiter'];
                                                    $errorLog['message']     = $operator['message'];
                                                    $errorLog['format']      = $operator['format'];
                                                    $errorLog['spacer']      = $operator['spacer'];
                                                    break 4;
                                                }
                                            }
                                        }
                                        $matchSum = null;

                                    } else {
                                        $error = true;
                                        $errorLog['invalidData'] = $parameter['charSet'];
                                        $errorLog['reason']      = 'required_char';
                                        $errorLog['message']     = $parameter['message'];
                                        $errorLog['format']      = $parameter['format'];
                                        $errorLog['spacer']      = $parameter['spacer'];
                                        break 2;
                                    }

                                    $operation = null;
                                    break;

                                /** Verifica o modificador charException que, se estiver definido,
                                 * então fará um novo teste de correspondência dos caracteres.
                                 * Neste novo teste verifica-se se o dado testado possui caracteres
                                 * que fazem parte da exceção.
                                 * 
                                 * Os dados que fazem parte da exceção são removidos do grupo de
                                 * caracteres que estão dentro do grupo de permitidos e inseridos
                                 * no grupo de caracteres que estão fora do grupo de permitidos. */
                                case 'charException':
                                    $exceptMatch   = $this->charMatch($parameter['charSet']);
                                    $exceptInside  = $exceptMatch[0];
                                    $exceptOutside = $exceptMatch[1];
                                    $finalInside   = array_intersect($exceptOutside, $mainInside);
                                    $finalOutside  = array_merge($mainOutside, $exceptInside);

                                    if(!empty($exceptInside)){
                                        $error = true;
                                        $errorLog['invalidData'] = $finalOutside;
                                        $errorLog['reason']      = 'invalid_char';
                                        $errorLog['message']     = $parameter['message'];
                                        $errorLog['spacer']      = $parameter['spacer'];
                                        break 2;
                                    }
                                    break;

                                /** Definição de operações quando se usa as configurações de
                                 * comparação. */
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
                                        'format'    => $parameter['format'],
                                        'spacer'    => $parameter['spacer'],
                                    ];
                                    break;

                                /** Definição de restrição quando se usa as configurações de
                                 * restrição. */
                                case 'all':        $require    = 'all'; break;
                                case 'any':        $require    = 'any'; break;
                            }
                        }

                        if($error){
                            $errorLog['error']    = $error;
                            $errorLog['testName'] = 'charSet';
                            $this->setValidationError($errorLog);
                        }

                        return Chain::resolve($chainData, $data);
                    }
                }
            )
        );
        return $this;
    }

    /**
     * Métodos de restrição. Respectivamente definem um conjunto de caracteres que fazem parte
     * das exceção ou de caracteres que são obrigatórios.
     * 
     * @param string $charSet          Caractere, conjunto de caracteres ou tag de predefinição.
     */
    public function charException(...$charSet) { $this->modifierChain('charException', is_array($charSet[0]) ? $charSet[0] : $charSet); return $this; }
    public function charRequired(...$charSet)  { $this->modifierChain('charRequired',  is_array($charSet[0]) ? $charSet[0] : $charSet); return $this; }

    /**
     * Método usado pelos métodos de restrição. Todos eles utilizam comandos idênticos com a única
     * diferença de terem nomenclaturas diferentes, então utilizou-se um método único que pode ser
     * reaproveitado por todos.
     * 
     * @param string $name             Nome do método.
     * 
     * @param array $charSet           Conjunto de caracteres.
     */
    private function modifierChain($name, $charSet)
    {
        Chain::create(
            $name,
            [
                'name'    => $name,
                'charSet' => $charSet,
                'attach'  => true,
            ],
            (function($chainData, $data){ return Chain::resolve($chainData, $data); })
        );

        return $this;
    }

    /**
     * Métodos modificadores que definem a obrigatoriedade das letras serem, respectivamente,
     * minúsculas ou maiúsculas, ou se esta obrigatoriedade não é necessária.
     */
    public function caseLower() { $this->caseChain('charCase', 'lower'); return $this; }
    public function caseUpper() { $this->caseChain('charCase', 'upper'); return $this; }
    public function caseAll()   { $this->caseChain('charCase', 'all');   return $this; }

    /**
     * Método usado pelos métodos de modificação maiúsculo/minúsculo. Todos eles utilizam comandos
     * idênticos com a única diferença de terem nomenclaturas diferentes e configurarem um único
     * valor diferente. Por isso, utilizou-se um método único que pode ser reaproveitado por todos.
     * 
     * @param string $name             Nome do método.
     * 
     * @param array $charCase          Definição da obrigatoriedade maiúsculo/minúsculo.
     */
    private function caseChain($name, $charCase)
    {
        Chain::create(
            $name,
            [
                'name'     => $name,
                'charCase' => $charCase,
                'attach'   => false,
            ],
            (
                function($chainData, $data)
                {
                    $this->charSet->case = $data['charCase'];
                    return Chain::resolve($chainData, $data); 
                }
            )
        );
    }

    /**
     * Método que faz a verificação de correspondência do conjunto de caracteres. Este método
     * também resolve o uso de tags e a configuração de obrigatoriedade maiúsculo/minúculo.
     * 
     * @param array $charSet           Conjunto de caracteres.
     */
    private function charMatch($charSet)
    {
        $case      = $this->charSet->case;
        $testValue = $this->validation->value;

        $lowerAccents         = 'àáâãäåçèéêëìíîïñòóôõöùúûüýÿŕ';
        $upperAccents         = 'ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝŸŔ';
        $specialChars         = "¹²³£¢¬º\\\\\/\-,.!@#$%\"'&*()_°ª+=\[\]{}^~`?<>:;";
        $lowerExtendedAccents = 'ẃṕśǵḱĺźǘńḿẁỳǜǹẽỹũĩṽŵŷŝĝĥĵẑẅẗḧẍæœ';
        $upperExtendedAccents = 'ẂṔŚǴḰĹŹǗŃḾẀỲǛǸẼỸŨĨṼŴŶŜĜĤĴẐẄT̈ḦẌÆŒ';

        switch($case){
            case 'upper':
                $regexCase =  [
                    'a-z'             => 'A-Z',
                    'utf8'            => '\p{Lu}',
                    'accents'         => $upperAccents,
                    'extendedaccents' => $upperExtendedAccents,
                ];
                break;

            case 'lower':
                $regexCase =  [
                    'a-z'             => 'a-z',
                    'utf8'            => '\p{Ll}',
                    'accents'         => $lowerAccents,
                    'extendedaccents' => $lowerExtendedAccents,
                ];
                break;

            case 'all':
            default:
                $regexCase =  [
                    'a-z'             => 'a-zA-Z',
                    'utf8'            => '\p{L}',
                    'accents'         => $lowerAccents.$upperAccents,
                    'extendedaccents' => $lowerExtendedAccents.$upperExtendedAccents,
                ];
        }

        $flagList = [
            'Numbers'         => '0-9',
            'NumbersUtf8'     => '\p{Nl}',
            'Letters'         => $regexCase['a-z'],
            'LettersUtf8'     => $regexCase['utf8'],
            'Article'         => '0-9'.$regexCase['utf8'].$specialChars,
            'Namefield'       => $regexCase['utf8'].'\s',
            'SpecialChars'    => $specialChars,
            'Accents'         => $regexCase['accents'],
            'ExtendedAccents' => $regexCase['extendedaccents'],
            'Spaces'          => '\s',
            'Backslashes'     => '\\\\',
            '\\'              => '\\\\',
            "\""              => "\\\"",
            'DoubleQuotes'    => "\\\"",
            "'"               => "\\'",
            'SingleQuotes'    => "\\'",
            '/'               => '\\/',
            '-'               => '\\-',
            '['               => '\\[',
            ']'               => '\\]',
        ];

        foreach ($charSet as &$char){ $char = $flagList[$char] ?? $char; unset($char); }
        preg_match_all ('/[' .implode($charSet).']/u', (string)$testValue, $match) ;
        preg_match_all ('/[^'.implode($charSet).']/u', (string)$testValue, $unmatch);

        return [$match[0], $unmatch[0]];
    }
}
