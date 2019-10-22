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

class Chain {
    private $links;
    
    /**
     * Método que cria um elo na corrente.
     * 
     * @param string $name             Nome dado para a função armazenada.
     * 
     * @param mixed $data              Parâmetros da função que será armazenada.
     * 
     * @param function $function       A função que será armazenada.
     */
    public function create($name, $data, $function){
        $this->links[]     =  [
            "name"        => $name,
            "data"        => $data,
            "function"    => $function,
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
    public function resolve($chainData = FALSE, $data = FALSE){
        $chain = $this->pop();

        if($chain !== NULL){
            if($chain["data"]["attach"]){
                $chainData[] = $chain["data"];
            }
            return     $chain["function"]($chainData, $chain["data"]);
        }
    }
    
    /**
     * Método que checa se existem elos na corrente.
     */
    public    function hasLinks(){
        return !empty($this->links);
    }
    
    /**
     * Método que desempilha um elo da corrente.
     */
    private function pop(){
        $last = array_pop($this->links);
        return $last;
    }
}