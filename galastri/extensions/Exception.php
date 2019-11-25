<?php
/**
 * - Exception.php -
 * 
 * Classe que faz a extensão da classes Exception padrão do PHP. Por padrão, a propriedade $code
 * só aceita valores inteiros. Aqui, esta extensão força a classe Exception a admitir valores
 * quaisquer.
 */
namespace galastri\extensions;

class Exception extends \Exception
{
    /**
     * Construtor da classe Exception que sobrepõe a classe padrão do PHP.
     * 
     * @param string $message          Mensagem de exceção.
     * 
     * @param mixed $code              Código da exceção.
     * 
     * @param Exception $previous      Armazena a exceção anterior.
     */
    public function __construct($message = null, $code = 0, Exception $previous = null){
        parent::__construct($message, 0, $previous);
        $this->code = $code;
    }
}
