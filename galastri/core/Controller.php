<?php
/**
 * - Controller.php -
 * 
 * Classe com as configurações básicas de um controller. Esta classe precisa ser herdada por
 * todas as controllers.
 */
namespace galastri\core;

class Controller
{
    private $authStatus;
    private $data;
    private $title;
    private $view;
    private $cache;
    private $template;
    private $import;
    private $downloadable;
    private $parameters;

    /**
     * Método que executa algumas configurações básicas do controller. Uma das execuções é a de
     * verificar se a página tem acesso restrito a usuários logados. Neste caso, quando a
     * autenticação é obrigatória mas estiver inativa, o controller da área sequer é chamado.
     * 
     * Já quando a autenticação estiver ativa ou ainda quando a autenticação não for obrigatória,
     * este método verifica se existe um método chamado construct(). Mas qual o motivo de haver
     * um método chamado construct() sendo que no PHP existe o __construct()? Simples, as chamadas
     * de métodos devem ocorrer dentro de uma ordem, conforme abaixo:
     * 
     *  | 1. A classe é criada;
     *  | 2. O método startController() é chamado e faz as configurações iniciais;
     *  | 3. Caso exista um método construct() na controller, ela é chamada;
     *  | 4. O método que representa a requisição é chamada.
     * 
     * O método construct() deve ser chamado depois do startController() ter feito algumas
     * configurações iniciais, do contrário, as configurações não estarão preparadas para serem
     * utilizadas. Além disso foram reportados alguns erros que podem acontecer, principalmente
     * relacionados ao método Authentication() que utiliza sessões. E este é o problema de se
     * utilizar o método __construct() do PHP nas controllers, pois esse método é chamado antes
     * de todos.
     * 
     * De qualquer forma, é perfeitamente possível utilizar o método __construct() caso se deseje
     * executar comandos antes das configurações iniciais, mas o indicado é utilizar sempre o
     * método interno construct().
     * 
     *  | IMPORTANTE:
     *  | As orientações para o uso do construct() no lugar do __construct() servem APENAS para
     *  | as controllers, não servem para outras classes, tais como models, extensões, etc.
     * 
     * Após isso, a controller é chamada e deverá retornar os dados que serão utilizados pelo
     * renderizador.
     */
    public function startController()
    {
        $this->title        = Route::title();
        $this->view         = Route::view();
        $this->cache        = Route::cache();
        $this->template     = Route::template();
        $this->import       = Route::import();
        $this->downloadable = Route::downloadable();
        $this->authStatus   = true;
        
        $this->parameters   = $this->resolveParameters();

        if(Route::authTag()){
            $this->authStatus = Authentication::validate(Route::authTag());
        }
        
        if($this->authStatus){
            if(method_exists($this, 'construct')){
                $this->construct();
            }
            
            $method     = Route::method();
            $this->data = $this->$method();
        }
    }
    
    /**
     * Método que retorna um objeto StdClass contendo atributos que armazenam dados processados
     * pelo controller.
     */
    public function getRendererData()
    {
        $data               = new \StdClass;
        
        $data->title        = $this->title;
        $data->view         = $this->view;
        $data->cache        = $this->cache;
        $data->template     = $this->template;
        $data->import       = $this->import;
        $data->parameters   = $this->parameters;
        $data->downloadable = $this->downloadable;
        $data->authStatus   = $this->authStatus;
        $data->data         = $this->data;
        
        $data->path         = Route::path();
        $data->method       = Route::method();
        $data->urlString    = Route::urlString();

        return $data;
    }
    
    /**
     * Método que resolve os parâmetros atribuindo nomes de rótulos baseado na definição em
     * config/routes.php.
     * 
     * !!MELHORAR ESTA PARTE!!
     */
    private function resolveParameters(){
        $routes        = Route::routes();
        $routePath     = Route::path();
        $preParameters = Route::parameters();
        $method        = Route::method('noCaseConvert');
        $parameters    = [];

        /** DOCUMENTAR AQUI */
        if($method === $preParameters[0] or empty($preParameters[0]))
            array_shift($preParameters);
        
        $methodParameter = $routes["@$method"]['parameters'] ?? [];

        if($routePath === '/' and !empty($preParameters) and empty($methodParameter) and GALASTRI['forceParameters']['status']){
            Redirect::location(GALASTRI['forceParameters']['redirectOnFail']);
        } else {
            $requiredLabel = [];
            
            foreach($methodParameter as $label){
                if($label[0] !== '?')
                    $requiredLabel[] = $label;
            }
            
            /* verifica se a quantidade de parametros. Não pode ser menor que a requerida e nem maior do que as definidas */
            if((count($preParameters) < count($requiredLabel) or count($preParameters) > count($methodParameter)) and GALASTRI['forceParameters']['status'])
                Redirect::location(GALASTRI['forceParameters']['redirectOnFail']);
            
            foreach($methodParameter as $key => $label){
                if($label[0] === '?'){
                    $label = substr($label, 1, strlen($label));
                    $parameters[$label] = $preParameters[$key] ?? null;
                } else {
                    if(empty($preParameters[$key])){
                        if(GALASTRI['forceParameters']['status'])
                            Redirect::location(GALASTRI['forceParameters']['redirectOnFail']);
                        
                        $parameters[$label] = false;
                    } else {
                        $parameters[$label] = $preParameters[$key];
                    }
                }
            }
        }
        
        return $parameters;
    }
    
    /**
     * Métodos setters para armazenar dados da rota. Foi escolhido assim para que os atributos
     * da rota estejam protegidos e para melhor legibilidade dos códigos da controller.
     */
    protected function setTitle($title)               { $this->title        = $title; }
    protected function setView($view)                 { $this->view         = $view; }
    protected function setCache($cache)               { $this->cache        = $cache; }
    protected function setTemplate($template)         { $this->template     = $template; }
    protected function setImport($import)             { $this->import       = $import; }
    protected function setDownloadable($downloadable) { $this->downloadable = $downloadable; }
    protected function setAuthStatus($authStatus)     { $this->authStatus   = $authStatus; }
    
    /**
     * Métodos getters para recuperar dados da rota. Foi escolhido assim para que os atributos
     * da rota estejam protegidos e para melhor legibilidade dos códigos da controller.
     */
    protected function getTitle()        { return $this->title; }
    protected function getView()         { return $this->view; }
    protected function getCache()        { return $this->cache; }
    protected function getTemplate()     { return $this->template; }
    protected function getImport()       { return $this->import; }
    protected function getDownloadable() { return $this->downloadable; }
    protected function getParameters()   { return $this->parameters; }
    protected function getAuthStatus()   { return $this->authStatus; }
    
    protected function getParameter($parameter)
    {
        Debug::trace(debug_backtrace()[0]);

        if(!isset($this->parameters[$parameter])){
            Debug::error('CONTROLLER004', $parameter)::print();
        }
        return $this->parameters[$parameter];
    }
    
    /**
     * Método que verifica se parâmetros obrigatórios, definidos em config/routes.php, não foram
     * preenchidos. Caso existam parâmetros não preenchidos, o método retorna false ou, opcionalmente
     * redireciona para outra URL.
     * 
     * @param string $redirect         URL ou alias para onde o usuário será redirecionado.
     */
    protected function checkRequiredParameters($redirect = false)
    {
        $parameters = $this->parameters;
        
        if(array_search(false, $parameters, true)){
            if($redirect){
                Redirect::location($redirect);
            } else {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Método exclusivo para o renderizador file, usado para quando se quer alterar o caminho do
     * arquivo para um caminho específico. Ideal para arquivos restritos ou que não podem ter o
     * seu caminho visível pela URL.
     * 
     * @param string $path             Endereço que irá substituir o endereço dos parâmetros.
     */
    protected function filePath($path)
    {
        if(Route::renderer() === 'file'){
            $this->path       = '';
            $this->parameters = $path;
        } else {
            Debug::error('FILE005')->print();
        }
    }
}
