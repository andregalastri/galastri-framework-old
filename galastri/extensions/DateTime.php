<?php
/**
 * - DateTime.php -
 * 
 * Classe que faz a extensão da classe DateTime padrão do PHP. Esta extensão apenas cria um atalho
 * do método createFromFormat().
 */
namespace galastri\extensions;

use \galastri\core\Debug;
use \galastri\extensions\Exception;

class DateTime extends \DateTime
{
    /**
     * Método encurtado do método createFromFormat(). A ordem dos parâmetros também foi invertida,
     * para que o formato seja opcional.
     * 
     * @param  string $value      Data/hora em formato de string.
     * 
     * @param  string $format     Opcional: formato da data/hora informado no parâmetro anterior.
     *                            Caso o formato não coincida com a data/hora informada, haverá
     *                            erro.
     * 
     * @return \DateTime
     */
    public static function create($value, $format = 'Y-m-d H:i:s')
    {
        Debug::trace(debug_backtrace()[0]);

        try {
            $datetime = parent::createFromFormat('!'.$format, $value);
            $errors = array_filter(parent::getLastErrors());
            if(!empty($errors)){
                $message[0] = implode('. ', $errors['warnings']) ?? '';
                $message[1] = implode('. ', $errors['errors']) ?? '';
                
                throw new Exception(implode('. ', $message), 'dateTimeFail');
            }

            return $datetime;
            
        } catch(Exception $e){
            Debug::error('DATETIME005', $e->getMessage())::print();
        }
    }
        
    /**
     * Método que retorna a data e a hora do momento em que ele é executado.
     *
     * @param  string $modify   Opcional: Permite modificar a data/hora adicionando strings
     *                          modificadoras padrão da classe DateTime, como "+10 minutes"
     *                          "-2 days", etc.
     * @return \DateTime
     */
    public static function now(string $modify = '')
    {
        $date = self::create(date('Y-m-d H:i:s'));

        if(!empty($modify))
            $date->modify($modify);
        
        return $date;
    }
    
    /**
     * Método que retorna apenas a data do momento em que ele é executado. A hora retornada é
     * sempre 00:00:00.
     *
     * @param  string $modify   Opcional: Permite modificar a data/hora adicionando strings
     *                          modificadoras padrão da classe DateTime, como "+10 minutes"
     *                          "-2 days", etc.
     * @return \DateTime
     */
    public static function today(string $modify = '')
    {
        $date = self::create(date('Y-m-d 00:00:00'));

        if(!empty($modify))
            $date->modify($modify);
        
        return $date;
    }
}
