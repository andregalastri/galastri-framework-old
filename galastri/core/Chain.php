<?php
/**
 * - Chain.php -
 * 
 * Classe que faz a criação e resolução de funções encadeadas em um sistema de pilhas. Todas as
 * funções encadeadas não são executadas até que a resolução seja chamada. Assim que a resolução
 * se iniciar, ela é executada de forma inversa, ou seja, última função é executada primeiro,
 * seguido da penúltima e assim seguindo até chegar na primeira função armazenada.
 */
namespace galastri\core;

class Chain
{
    public static $links = [];
    private static $message;
    private static $format;
    private static $spacer;
    
    /** Classe que trabalha sob o padrão Singleton, por isso, não poderá ser instanciada. */
    private function __construct(){}
    
    /**
     * Método que cria um elo na corrente.
     * 
     * @param string $name             Nome dado para a função armazenada.
     * 
     * @param mixed $data              Parâmetros da função que será armazenada.
     * 
     * @param function $function       A função que será armazenada.
     */
    public static function create($name, $data, $function)
    {
        self::$links[]     =  [
            'name'        => $name,
            'data'        => $data,
            'function'    => $function,
        ];
    }
    /**
     * Método que resolve uma corrente. A resolução ocorre na ordem de uma pilha, ou seja, a
     * primeira função executada é a que está no último elo e assim sucessivamente até chegar à
     * função do primeiro elo.
     * 
     * @param array $chainData         Parâmetros da corrente, que são repassados de um elo para
     *                                 outro.
     * 
     * @param array $data              Parâmetros da própria função, para serem usados dentro da
     *                                 função a qual pertencem e, se necessário, para serem
     *                                 repassados para os parâmetros da corrente.
     */
    public static function resolve($chainData = false, $data = false)
    {
        $chain = self::pop();

        if($chain !== null){
            if($chain['name'] == 'onError'){
                self::$message = $chain['data']['message'];
                self::$format  = $chain['data']['format'];
                self::$spacer  = $chain['data']['spacer'];
            }
            
            $chain['data']['message'] = self::$message;
            $chain['data']['format']  = self::$format;
            $chain['data']['spacer']  = self::$spacer;
            
            if($chain['data']['attach']){
                $chainData[] = $chain['data'];
            }
            
            
            return $chain['function']($chainData, $chain['data']);
        }
    }
    
    /**
     * Método que checa se existem elos na corrente.
     */
    public static function hasLinks()
    {
        return !empty(self::$links);
    }
    
    /**
     * Método que desempilha um elo da corrente.
     */
    private static function pop()
    {
        $last = array_pop(self::$links);
        return $last;
    }
}
