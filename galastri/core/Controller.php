<?php
/**
 * - Controller.php -
 * 
 * Classe com as configurações básicas de um controller. Esta classe precisa ser herdada por
 * todas as controllers.
 */
namespace galastri\core;

class Controller extends Composition{
    protected $route;
    protected $authStatus;
    protected $data;

    /**
     * Este microframework se utiliza de composição como forma de trabalhar com reutilização de
     * códigos, já que o PHP não permite heranças múltiplas. Mais informações no arquivo
     * core\Composition.php.
     */
    private function composition(){
        $this->authentication();
        $this->permission();
        $this->redirect();
    }
    
    public function __construct(){
        $this->debug();
        $this->debug->trace = debug_backtrace()[0];
    }
    
    /**
     * Método que executa algumas configurações básicas do controller. Uma das execuções é a de
     * verificar se a página tem acesso restrito a usuários logados. Neste caso, quando a
     * autenticaçãp é obrigatória mas estiver inativa, o controller da área sequer é chamado.
     * 
     * Já quando a autenticação estiver ativa ou ainda quando a autenticação não for obrigatória,
     * a controller é chamada e deverá retornar os dados que forem usados pelo renderizador.
     * 
     * @param object $route            Recebe uma instância da classe Route.
     */
    public function startController($route){
        $this->composition();

        $this->route = $route;
        $this->authStatus = TRUE;

        if($route->authTag){
            $this->authStatus = $this->authentication->validate($route->authTag);
        }
        
        if($this->authStatus){
            $method     = $route->method;
            $this->data = $this->$method();
        }
    }
    
    /**
     * Método que retorna um objeto StdClass contendo atributos que armazenam dados processados
     * pelo controller.
     */
    public function getRendererData(){
        $data               = new \StdClass;
        $data->title        = $this->route->title;
        $data->view         = $this->route->view;
        $data->path         = $this->route->path;
        $data->cache        = $this->route->cache;
        $data->template     = $this->route->template;
        $data->import       = $this->route->import;
        $data->parameters   = $this->route->parameters;
        $data->method       = $this->route->method;
        $data->urlString    = $this->route->urlString;
        $data->downloadable = $this->route->downloadable;
        $data->authStatus   = $this->authStatus;
        $data->data         = $this->data;
        return $data;
    }
    
    /**
     * Métodos setters para armazenar dados da rota. Foi escolhido assim para que os atributos
     * da rota estejam protegidos e para melhor legibilidade dos códigos da controller.
     */
    protected function setTitle($title)               { $this->route->title        = $title; }
    protected function setView($view)                 { $this->route->view         = $view; }
    protected function setCache($cache)               { $this->route->cache        = $cache; }
    protected function setTemplate($template)         { $this->route->template     = $template; }
    protected function setImport($import)             { $this->route->import       = $import; }
    protected function setDownloadable($downloadable) { $this->route->downloadable = $downloadable; }
    protected function setAuthStatus($authStatus)     { $this->authStatus          = $authStatus; }
    
    /**
     * Métodos getters para recuperar dados da rota. Foi escolhido assim para que os atributos
     * da rota estejam protegidos e para melhor legibilidade dos códigos da controller.
     */
    protected function getTitle()        { return $this->route->title; }
    protected function getView()         { return $this->route->view; }
    protected function getCache()        { return $this->route->cache; }
    protected function getTemplate()     { return $this->route->template; }
    protected function getImport()       { return $this->route->import; }
    protected function getDownloadable() { return $this->route->downloadable; }
    protected function getParameters()   { return $this->route->parameters; }
    protected function getAuthStatus()   { return $this->authStatus; }
    
    /**
     * Método exclusivo para o renderizador file, usado para quando se quer alterar o caminho do
     * arquivo para um caminho específico. Ideal para arquivos restritos ou que não podem ter o
     * seu caminho visível pela URL.
     * 
     * @param string $path             Endereço que irá substituir o endereço dos parâmetros.
     */
    protected function filePath($path){
        $renderer = $this->route->renderer;
        
        if($renderer === "file"){
            $this->route->path       = "";
            $this->route->parameters = $path;
        } else {
            $this->debug->error("FILE005")->print();
        }
    }
}