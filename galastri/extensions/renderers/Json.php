<?php
/**
 * - Json.php -
 * 
 * Arquivo que contém os comandos do renderizador json. Este renderizador exibe os dados processados
 * pelo controller codificado em formato JSON. Todo renderizador deve ser configurado na rota da
 * URL, no arquivo config\routes.php.
 */
namespace galastri\extensions\renderers;

trait Json {
    private $jsonController = TRUE;
    private $json;

    /**
     * Método principal que um único teste; verifica se o controller retorna um objeto.
     * 
     * Estando tudo correto, é verificado se a página foi configurada como sendo restrita, ou seja,
     * acessível apenas caso esteja com uma sessão configurada.
     */
    private function json(){
        $this->debug->trace =  debug_backtrace()[0];
        
        $this->json       = new \StdClass;
        $this->json->data = NULL;

        $this->jsonCheckObject();
        
        $this->json->data->data = $this->checkAuth($this->json->data);

        header('Content-Type: application/json');
        $this->printContent(json_encode($this->json->data->data));
    }
    
    /**
     * Verifica se o controller é um objeto. Caso seja, então é chamado o método getRendererData()
     * que trás uma StdClass com uma série de atributos que incluem os dados processados e
     * retornados pelo controller. Estes dados serão codificados e exibidos na tela em formato
     * JSON.
     */
    private function jsonCheckObject(){
        $controller = $this->controller;
        $json = $this->json->data;

        if($json === NULL){
            if(is_object($controller)){
                $this->json->data = $controller->getRendererData();
            } else {
                $this->debug->error("CONTROLLER003", gettype($controller))->print();
            }
        }
        return $this;
    }
    
    /**
     * Este método é usado no arquivo Galastri.php para verificar se a rota foi configurada com
     * o status de offline.
     */
    private function jsonCheckOffline(){
        $offline = $this->route->offline;
        
        if($offline){
            header('Content-Type: application/json');
            
            $this->printContent(json_encode([
                "pass" => FALSE,
                "message" => "maintenance",
            ]));
        }
        return $this;
    }
}