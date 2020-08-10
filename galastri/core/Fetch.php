<?php
/**
 * - Fetch.php -
 * 
 * Classe que recupera dados de requisições. O PHP, por padrão, reconhece requisições padrão do
 * navegador (através do acesso via URL) e as requisições por XMLHttpRequest. Nestes casos, as
 * as chaves passadas pela requisição através de método GET ficam armazenadas na global $_GET,
 * enquanto que as requisições POST ficam na global $_POST
 * 
 * O problema é que em Javascript moderno existe um novo jeito de envio de requisições, que se
 * dá através de Promises. Neste, a requisição envia um objeto JSON que o PHP não armazena nas
 * variáveis globais, precisando ser tratado de forma diferente. Esta classe busca resolver este
 * problema.
 */
namespace galastri\core;

class Fetch
{
    private static $key;
    private static $defaultValue = null;
    private static $phpGlobalVar;

    /**
     * Método que faz o direcionamento da requisição. O padrão atualmente é permitir quatro tipos
     * de requisição: GET, POST, PUT e DELETE. Este método recebe o tipo da requisição e faz o
     * direcionamento para o método seguinte.
     * 
     * @param string $key              Chave contendo os dados armazenados na requisição.
     * 
     * @param string $defaultValue     Valor padrão para caso a chave não exista.
     */
    public static function key($key, $defaultValue = null)
    {
        Debug::trace(debug_backtrace()[0]);
        self::$key = $key;
        self::$defaultValue = $defaultValue;

        $requestMethod = lower($_SERVER['REQUEST_METHOD']);
        
        return self::$requestMethod();
    }

    /**
     * Método que verifica o tipo da requisição (POST ou GET) para testar se a chave existe na
     * variável global $_POST ou $_GET. Caso a chave não exista, é testado então se os dados
     * recebidos através de JSON devido ao envio de requisições por Promises, que não são
     * reconhecidos por padrão no PHP e, portanto, não ficam armazenados nas globais.
     * 
     * Caso a chave também não exista no JSON, então é retornado o valor padrão.
     */
    private static function resolve()
    {
        Debug::trace(debug_backtrace()[0]);
        $phpGlobalVar = self::$phpGlobalVar === 'post' ? $_POST : $_GET;

        $data = array_key_exists(self::$key, $phpGlobalVar) === false ? json_decode(file_get_contents('php://input'), true) : $phpGlobalVar;
        
        return $data[self::$key] ?? self::$defaultValue;
    }

    /**
     * Método que direciona o método resolve() de que a requisição é do tipo POST.
     */
    private static function post()
    {
        Debug::trace(debug_backtrace()[0]);
        self::$phpGlobalVar = 'post';

        return self::resolve();
    }

    /**
     * Método que direciona o método resolve() de que a requisição é do tipo GET.
     */
    private static function get()
    {
        Debug::trace(debug_backtrace()[0]);
        self::$phpGlobalVar = 'get';

        return self::resolve();
    }

    /**
     * Método que direciona o método resolve() de que a requisição é do tipo PUT.
     */
    private static function put()
    {
        Debug::trace(debug_backtrace()[0]);
        return self::post();
    }

    /**
     * Método que direciona o método resolve() de que a requisição é do tipo DELETE.
     */
    private static function delete()
    {
        Debug::trace(debug_backtrace()[0]);
        return self::post();
    }
}
