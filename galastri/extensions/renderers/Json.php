<?php
/**
 * - Json.php -
 * 
 * Arquivo que contém os comandos do renderizador json. Este renderizador exibe os dados processados
 * pelo controller codificado em formato JSON. Todo renderizador deve ser configurado na rota da
 * URL, no arquivo config\routes.php.
 */
namespace galastri\extensions\renderers;

use galastri\core\Debug;
use galastri\core\Route;

trait Json
{
    private static $json;

    private static function jsonController()
    {
        return true;
    }

    /**
     * Método principal que um único teste; verifica se o controller retorna um objeto.
     * 
     * Estando tudo correto, é verificado se a página foi configurada como sendo restrita, ou seja,
     * acessível apenas caso esteja com uma sessão configurada.
     */
    private static function json()
    {
        Debug::trace(debug_backtrace()[0]);
        
        self::$json = new \StdClass;

        self::jsonCheckObject();
        
        self::$json = self::checkAuth(self::$json);

//        header('Content-Type: application/json');
        self::printContent(json_encode(self::$json->data));
    }
    
    /**
     * Verifica se o controller é um objeto. Caso seja, então é chamado o método getRendererData()
     * que trás uma StdClass com uma série de atributos que incluem os dados processados e
     * retornados pelo controller. Estes dados serão codificados e exibidos na tela em formato
     * JSON.
     */
    private static function jsonCheckObject()
    {
        $controller = self::$controller;

        if(is_object($controller)){
            self::$json = $controller->getRendererData();
        } else {
            Debug::error('CONTROLLER003', gettype($controller))::print();
        }
        return __CLASS__;
    }
    
    /**
     * Este método é usado no arquivo Galastri.php para verificar se a rota foi configurada com
     * o status de offline.
     */
    private static function jsonCheckOffline()
    {
        $offline = Route::offline();
        
        if($offline){
            header('Content-Type: application/json');
            
            self::printContent(
                json_encode([
                    'pass' => false,
                    'message' => $offline['message'] ?? 'offline',
                ])
            );
        }
        return __CLASS__;
    }
}
