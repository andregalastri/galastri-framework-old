<?php
/**
 * - Redirect.php -
 * 
 * Classe que efetua o redirecionamento de uma URL para outra.
 */
namespace galastri\core;

class Redirect
{
    /**
     * Método que faz o redirecionamento para uma URL específica ou para um atalho configurado
     * nas configurações do arquivo config/default.php.
     * 
     * @param string $to               URL para redirecionamento ou o nome da chave com o atalho
     *                                 configurado.
     */
    public static function location($to = false)
    {
        Debug::trace(debug_backtrace()[0]);

        if($to === false){
            Debug::error('REDIRECT000')::print();
        } else {
            if(array_key_exists($to, GALASTRI['urlAlias'])){
                exit(header('Location: '.GALASTRI['urlAlias'][$to]));
            } else {
                exit(header('Location: '.$to));
            }
        }
    }
}
