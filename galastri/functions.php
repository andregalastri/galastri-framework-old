<?php
/**
 * - functions.php -
 * 
 * Contém funções globais com o intuito de facilitar determinados comandos de uso comum sem a
 * exigência de se criar objetos para seu uso.
 */

/**
 * Função que reformata a função var_dump() para algo mais legível. Esta função não cancela a
 * execução de códigos que vierem após a sua chamada, permitindo ser usada em loops, por exemplo.
 * 
 * @param mixed $variable          Valor que será impresso pelo var_dump().
 */
function vdump(...$variable)
{
    $debug = debug_backtrace()[0];
    $format = dump($variable);

    printDump('var_dump', $format, $debug);
}

/**
 * Função que reformata a função var_dump() para algo mais legível. Esta função cancela a
 * execução de códigos que vierem após a sua chamada, ou seja, após sua chamada o script é
 * encerrado. É ideal pra locais onde se deseja impedir que comandos posteriores sejam executados
 * após o var_dump().
 * 
 * @param mixed $variable          Valor que será impresso pelo var_dump().
 */
function ddump(...$variable)
{
    $debug = debug_backtrace()[0];
    $format = dump($variable);

    printDump('exit_dump', $format, $debug);
    exit();
}

/**
 * Formava o resultado do var_dump. Esta função não é para ser chamada. Ela só é utilizada pelas
 * funções vdump() e ddump().
 */
function dump($variable)
{
    $varDump = (function($variable) {
        ob_start();
        foreach($variable as $key => $value) var_dump($value);
        $content = ob_get_contents();
        ob_end_clean();
        $content = preg_replace('/([a|s|b|i|f|N|o].*?)({\n)/', '<b>$1</b>\n$2', $content, 1);
        $content = preg_replace('/=>\n/', '=>', $content);
        $content = preg_replace('/(])(=>).*?(a|s|b|i|f|N|o)/', '$1 $2 $3', $content);
        $content = preg_replace('/(\[.*?\])/', '<small><b>$1</b></small>', $content);
        $content = preg_replace('/(=> )(.*?)( {\n|\n\s|\n})/', '$1$2$3', $content);
        return $content;
    })($variable);

    return $varDump;
}

/**
 * Imprime o var_dump() formatado. Esta função não é para ser chamada. Ela só é utilizada pelas
 * funções vdump() e ddump().
 */
function printDump($name, $format, $debug)
{
    echo
        <<<DDUMP
<pre style='white-space:pre-wrap;'>
<big><b>$name DEBUG</b></big>
<div style='padding-left: 12px;margin-left: 3px;border-left: 1px dashed #000;'>
<b>ORIGIN: </b>$debug[file]
<b>LINE  : </b>$debug[line]

<b>RETURNED DATA:</b>

$format
</div>
</pre>
DDUMP;
}

/**
 * Função que faz um 'replace' apenas da primeira ocorrência.
 * 
 * @param string $search           Termo que deverá ser procurado para ser substituídos.
 * 
 * @param string $replace          Termo que substituirá o valor procurado, caso seja encontrado.
 * 
 * @param string $string           Texto onde o termo será procurado e substituído.
 * 
 * @param bool $reverse            Quando TRUE indica se a busca deve acontecer do final do texto
 */
function replaceOnce($search, $replace, $string, $reverse = FALSE)
{
    if(empty($string) or empty($search)){
        return $string;
    } else {
        $callFunction = $reverse ? 'strrpos' : 'strpos';
        $position = $callFunction($string, $search);

        return $position !== false ? substr_replace($string, $replace, $position, strlen($search)) : $string;    
    }
}

/**
 * Função que faz o implode() de múltiplas arrays.
 * 
 * @param string $glue             Termo que irá dividir cada um valores de cada chave.
 * 
 * @param array $arrays            Arrays que sofrerão o implode();
 */
function implodeMultiple($glue, $arrays)
{
    $string = '';

    foreach($arrays as $array){
        $string .= implode($glue, $array);
    }
    return $string;
}

/**
 * Função que retorna apenas a primeira letra maiúscula de uma string. Esta função não é para ser
 * chamada. Ela só é utilizada pela função capitalize().
 */
if (!function_exists('mb_ucfirst'))
{
    function mb_ucfirst($string, $encoding = 'UTF-8', $lowerStringEnd = false) {
        $firstLetter = mb_strtoupper(mb_substr($string, 0, 1, $encoding), $encoding);
        $stringingEnd = '';
        if ($lowerStringEnd) {
            $stringingEnd = mb_strtolower(mb_substr($string, 1, mb_strlen($string, $encoding), $encoding), $encoding);
        }
        else {
            $stringingEnd = mb_substr($string, 1, mb_strlen($string, $encoding), $encoding);
        }
        $string = $firstLetter . $stringingEnd;
        return $string;
    }
}

/**
 * Função que formata um texto fazendo com que a primeira letra de cada palavra seja convertida
 * para maiúsculo, levando em conta a codificação UTF-8.
 * 
 * @param string $string           Texto que será formatado.
 * 
 * @param bool $asArticle          Quando TRUE converte apenas a primeira letra de cada frase para
 *                                 maiúscula e mantém das demais para minúscula leva em conta os
 *                                 pontos finais de exclamação e de interrogação.
 * 
 * @param bool $keepChars          Quando TRUE impede que letras que já estejam em maísculo sejam
 *                                 convertidas pra minúsculo
 */
function capitalize($string, $asArticle = FALSE, $keepChars = FALSE)
{
    if($asArticle){
        $string = preg_split('/(\.|\!|\?)/', $string, -1, PREG_SPLIT_DELIM_CAPTURE);
        $string = array_map('trim', $string);
    } else {
        $string = [$string];
    }

    foreach($string as $key => $value){
        $value = $asArticle ? mb_ucfirst($value, 'UTF-8', !$keepChars) : mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');;

        preg_match('/(\.|\!|\?)/', $value, $match);
        if(!array_key_exists(1, $match)) $match[1] = FALSE;

        $space = $value != $match[1] ? ' ' : '';
        $string[$key] = $space.$value;
    }

    $string = trim(implode('', $string));
    return $string;
}

/**
 * Função que converte todas as letras para maiúsculo levando em conta a codificação UTF-8
 * 
 * @param string $string           Texto que será formatado.
 */
function upper($string)
{
    return mb_convert_case($string, MB_CASE_UPPER, 'UTF-8');
}

/**
 * Função que converte todas as letras para minúsculo levando em conta a codificação UTF-8.
 * 
 * @param string $string           Texto que será formatado.
 */
function lower($string)
{
    return mb_convert_case($string, MB_CASE_LOWER, 'UTF-8');
}

/**
 * Converte a string em um tipo camelCase ou PascalCase.
 * 
 * @param string $string           Texto que será formatado.
 * 
 * @param string $type             Tipo da conversão, que pode ser 'camel' ou 'pascal'.
 * 
 * @param string $regex    Por padrão, cada palavra a ser convertida deve estar separada
 *                                 por traços - ou underlines _. Caso se queira usar outro tipo
 *                                 de delimitador, é necessário informá-lo aqui em formato regex.
 */
function convertCase($string, $type, $regex = '/(-|_)/')
{
    $string = preg_split($regex, $string);
    $string = array_map('trim', $string);

    foreach($string as $key => &$value){
        if(empty($value))
            continue;

        switch($type){
            case 'camel':
                if($key == 0)
                    continue 2;
                break;
            case 'pascal':
                break;
        }

        $value = capitalize($value);
    } unset($value);
    return implode($string);
}

/**
 * Cria uma string que repete um texto uma determinada quantidade de vezes. É possível inserir
 * um espaçador entre as repetições e também um contador para cada repetição.
 * 
 * @param string $string           Texto que será repetido.
 * 
 * @param int $times               Quantidade de vezes que o texto será repetido.
 * 
 * @param string $spacer           Espaçador que será inserido entre cada repetição.
 * 
 * @param bool $addCountToString   Insere o contador de zero até a quantidade de vezes na string
 *                                 repetida.
 */
function stringRepeat($string, $times, $spacer = '', $addCountToString = false)
{
    for($i = 0; $i < $times; $i++)
        $result[] = $string.($addCountToString ? $i : '');
    
    return implode($spacer, $result);
}

/**
 * Verifica se um dos valores informados é vazio.
 * 
 * @param array $values            Valores que serão testados.
 */
function isEmpty(...$values)
{
    foreach($values as $value){
        if(empty($value))
            return true;
    }

    return false;
}
