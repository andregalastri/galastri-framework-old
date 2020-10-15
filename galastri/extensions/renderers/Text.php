<?php
/**
 * - Text.php -
 * 
 * Arquivo que contém os comandos do renderizador text. Este renderizador exibe os dados processados
 * pelo controller codificado em um texto sem formatação. É ideal para se utilizar em execuções CLI
 * (via linha de comando) para se retornar mensagens de sucesso ou falha em uma requisição.
 * Trabalhos CRON, por exemplo, podem ser executados desta forma:
 * 
 * Exemplo de execução via linha de comando:
 *   
 *   REQUEST_URI=<rota> /usr/bin/php <localização da public_html/index.php>
 *   
 *   - <rota> se refere à rota que se deseja executar;
 *   - /usr/bin/php é o interpretador php. Em alguns servidores pode ser necessário executar de outras
 *     maneiras, como apenas 'php' (sem aspas);
 *   - <localização da public_html/index.php> é a pasta onde o arquivo public_html/index.php está.
 *   
 * Todo renderizador deve ser configurado na rota da URL, no arquivo config\routes.php.
 */
namespace galastri\extensions\renderers;

use galastri\core\Debug;
use galastri\core\Route;

trait Text
{
    private static $text;

    private static function textController()
    {
        return true;
    }

    /**
     * Método principal que um único teste; verifica se o controller retorna um objeto.
     * 
     * Estando tudo correto, é verificado se a página foi configurada como sendo restrita, ou seja,
     * acessível apenas caso esteja com uma sessão configurada.
     */
    private static function text()
    {
        Debug::trace(debug_backtrace()[0]);
        
        self::$text = new \StdClass;

        self::textCheckObject();
        
        self::$text = self::checkAuth(self::$text);

        // header('Content-Type: text/plain');
            self::printContent(implode(' | ', self::$text->data));
    }
    
    /**
     * Verifica se o controller é um objeto. Caso seja, então é chamado o método getRendererData()
     * que trás uma StdClass com uma série de atributos que incluem os dados processados e
     * retornados pelo controller. Estes dados serão exibidos em formato de texto plano.
     */
    private static function textCheckObject()
    {
        $controller = self::$controller;

        if(is_object($controller)){
            self::$text = $controller->getRendererData();
        } else {
            Debug::error('CONTROLLER003', gettype($controller))::print();
        }
        return __CLASS__;
    }
    
    /**
     * Este método é usado no arquivo Galastri.php para verificar se a rota foi configurada com
     * o status de offline.
     */
    private static function textCheckOffline()
    {
        $offline = Route::offline();
        
        if($offline){
            header('Content-Type: text/plain');
            self::printContent($offline['message'] ?? 'offline');
        }
        return __CLASS__;
    }
}
