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
    private $returnedData;

    /**
     * Construtor da classe Exception que sobrepõe a classe padrão do PHP.
     *
     * @param string $message          Mensagem de exceção.
     *
     * @param mixed $code              Código da exceção.
     *
     * @param mixed $returnedData      Retorno de dados repassados à exceção, para quando se quer
     *                                 que dados antes da exceção sejam transmitidos junto ao throw.
     *
     * @param Exception $previous      Armazena a exceção anterior.
     */
    public function __construct($message = null, $code = 0, $returnedData = [], Exception $previous = null){
        parent::__construct($message, 0, $previous);
        $this->code = $code;
        $this->returnedData = $returnedData;
    }

    /**
     * Método que recupera os dados armazenados que são recebidos junto ao objeto da exceção.
     */
    public function getReturnedData()
    {
        return $this->returnedData;
    }
}
