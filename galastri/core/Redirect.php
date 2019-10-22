<?php
/**
 * - Redirect.php -
 * 
 * Classe que efetua o redirecionamento de uma URL para outra.
 */
namespace galastri\core;

class Redirect extends Composition {
    /**
     * Este microframework se utiliza de composição como forma de trabalhar com reutilização de
     * códigos, já que o PHP não permite heranças múltiplas. Mais informações no arquivo
     * core\Composition.php.
     */
    private function composition(){
        $this->debug();
    }
    
    /**
     * Método que faz o redirecionamento para uma URL específica ou para um atalho configurado
     * nas configurações do arquivo config/default.php.
     * 
     * @param string $to               URL para redirecionamento ou o nome da chave com o atalho
     *                                 configurado.
     */
    public function location($to = FALSE){
        $this->composition();

        $this->debug->trace = debug_backtrace()[0];

        if($to === FALSE){
            $this->debug->error("REDIRECT000")->print();
        } else {
            if(array_key_exists(lower($to), GALASTRI["urls"])){
                exit(header("Location: ".GALASTRI["urls"][$to]));
            } else {
                exit(header("Location: ".$to));
            }
        }
    }
}