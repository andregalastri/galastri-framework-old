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
    private $data;
    private $siteName;
    private $title;
    private $view;
    private $cache;
    private $template;
    private $import;
    private $downloadable;
    private $parameters;
    private $classAsParameter;
    private $authTag;
    private $authStatus;
    private $authFailUrl;

    /**
     * Método que executa algumas configurações básicas do controller. Uma das execuções é a de
     * verificar se a página tem acesso restrito a usuários logados. Neste caso, quando a
     * autenticação é obrigatória mas estiver inativa, o controller da área sequer é chamado.
     * 
     * Já quando a autenticação estiver ativa ou ainda quando a autenticação não for obrigatória,
     * este método verifica se existe um método chamado __build(). Mas qual o motivo de haver
     * um método chamado __build() sendo que no PHP existe o __construct()? As chamadas
     * de métodos devem ocorrer dentro de uma ordem, conforme abaixo:
     * 
     *  | 1. A classe é criada;
     *  | 2. O método startController() é chamado e faz as configurações iniciais;
     *  | 3. Caso exista um método __build() na controller, ela é chamada;
     *  | 4. O método que representa a requisição é chamada.
     * 
     * O método __build() deve ser chamado depois do startController() ter feito algumas
     * configurações iniciais, do contrário, as configurações não estarão preparadas para serem
     * utilizadas. Além disso foram reportados alguns erros que podem acontecer, principalmente
     * relacionados ao método Authentication() que utiliza sessões. E este é o problema de se
     * utilizar o método __construct() do PHP nas controllers, pois esse método é chamado antes
     * de todos.
     * 
     * De qualquer forma, é perfeitamente possível utilizar o método __construct() caso se deseje
     * executar comandos antes das configurações iniciais, mas o indicado é utilizar sempre o
     * método interno __build().
     * 
     *  | IMPORTANTE:
     *  | As orientações para o uso do __build() no lugar do __construct() servem APENAS para
     *  | as controllers, não servem para outras classes, tais como models, extensões, etc.
     * 
     * Após isso, a controller é chamada e deverá retornar os dados que serão utilizados pelo
     * renderizador.
     */
    public function startController()
    {
        $buildException = false;

        $this->siteName         = Route::siteName();
        $this->title            = Route::title();
        $this->view             = Route::view();
        $this->cache            = Route::cache();
        $this->template         = Route::template();
        $this->import           = Route::import();
        $this->downloadable     = Route::downloadable();
        $this->classAsParameter = Route::parameters()['classAsParameter'];
        $this->authTag          = Route::authTag();
        $this->authStatus       = Route::authStatus();
        $this->authBlock        = Route::authBlock();
        $this->authFailUrl      = Route::authFailUrl() ?? false;
        $this->parameters = $this->resolveParameters();

        if(!$this->authBlock or !$this->authTag){
            if(method_exists($this, '__build')){
                $this->data = $this->__build();
                
                if($this->data != null and array_key_exists('error', $this->data))
                    $buildException = true;
            }

            if(!$buildException){
                $method = Route::method();
                $this->data = $this->$method();
            }
        }
    }

    /**
     * Método que retorna um objeto StdClass contendo atributos que armazenam dados processados
     * pelo controller.
     */
    public function getRendererData()
    {
        $data               = new \StdClass;
        
        $data->siteName     = $this->siteName;
        $data->title        = $this->title;
        $data->view         = $this->view;
        $data->cache        = $this->cache;
        $data->template     = $this->template;
        $data->import       = $this->import;
        $data->parameters   = $this->parameters;
        $data->downloadable = $this->downloadable;
        $data->authStatus   = $this->authStatus;
        $data->authFailUrl  = $this->authFailUrl;
        $data->authBlock    = $this->authBlock;
        $data->data         = $this->data;
        $data->rootRoute    = GALASTRI['routes']['root'] === '/' ? '' : GALASTRI['routes']['root'];
        
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
        $routes           = Route::routes();
        $routePath        = Route::path();
        $afterMethod      = Route::parameters()['afterMethod'];
        $method           = Route::method('noCaseConvert');
        $parameters       = [];

        /** DOCUMENTAR AQUI */
        $methodParameter = $routes["@$method"]['parameters'] ?? [];
        if($routePath === '/' and !empty($afterMethod) and empty($methodParameter)){
            Redirect::location(Route::error404Url());
        } else {
            $requiredLabel = [];
            
            foreach($methodParameter as $label){
                if($label[0] !== '?')
                    $requiredLabel[] = $label;
            }
            
            /* Verifica a quantidade de parametros. Não pode ser menor que a requerida e nem maior do que as definidas */
            if(count($afterMethod) < count($requiredLabel) or count($afterMethod) > count($methodParameter))
                Redirect::location(Route::error404Url());
            
            foreach($methodParameter as $key => $label){
                if($label[0] === '?'){
                    $label = substr($label, 1, strlen($label));
                    $parameters[$label] = $afterMethod[$key] ?? null;
                } else {
                    if(empty($afterMethod[$key])){
                        if(GALASTRI['forceParameters']['status'])
                            Redirect::location(Route::error404Url());
                        
                        $parameters[$label] = false;
                    } else {
                        $parameters[$label] = $afterMethod[$key];
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
    protected function setSiteName($siteName)         { $this->siteName     = $siteName; }
    protected function setTitle($title)               { $this->title        = $title; }
    protected function setView($view)                 { $this->view         = $view; }
    protected function setCache($cache)               { $this->cache        = $cache; }
    protected function setTemplate($template)         { $this->template     = $template; }
    protected function setImport($import)             { $this->import       = $import; }
    protected function setDownloadable($downloadable) { $this->downloadable = $downloadable; }
    protected function setAuthStatus($authStatus)     { $this->authStatus   = $authStatus; }
    protected function setAuthTag($authTag)           { $this->authTag   = $authTag; }
    protected function setAuthFailUrl($authFailUrl)   { $this->authFailUrl   = $authFailUrl; }
    
    /**
     * Métodos getters para recuperar dados da rota. Foi escolhido assim para que os atributos
     * da rota estejam protegidos e para melhor legibilidade dos códigos da controller.
     */
    protected function getSiteName()          { return $this->siteName; }
    protected function getTitle()             { return $this->title; }
    protected function getView()              { return $this->view; }
    protected function getCache()             { return $this->cache; }
    protected function getTemplate()          { return $this->template; }
    protected function getImport()            { return $this->import; }
    protected function getDownloadable()      { return $this->downloadable; }
    protected function getParameters()        { return $this->parameters; }
    protected function getClassAsParameters() { return $this->classAsParameter; }
    protected function getAuthStatus()        { return $this->authStatus; }
    protected function getAuthTag()           { return $this->authStatus; }
    protected function getAuthFailUrl()       { return $this->authFailUrl; }
    
    protected function getParameter($parameter)
    {
        Debug::trace(debug_backtrace()[0]);

        if(!array_key_exists($parameter, $this->parameters)){
            Debug::error('CONTROLLER004', $parameter)::print();
        }
        return $this->parameters[$parameter];
    }

    /**
     * Método que retorna o que se denomina "Classe como parâmetro". Nas configurações de rota
     * do microframework existe a possibilidade de se criar rotas usando chaves que variam, como
     * se fossem os parâmetros. No arquivo 'config/routes.php' são as classes iniciadas por /?,
     * por exemplo: "/?dynamicValue => []".
     * 
     * Estas rotas permitem que o valor informado na URL seja armazenado. No exemplo acima, caso
     * a rota "/?dynamicValue" seja acessada, a URL poderá ser uma string qualquer.
     * 
     * Por exemplo:
     * Arquivo config/routes.php
     *      '/' => [
     *          '/?dynamicValue' => []
     *      ]
     * Ao se acessar a URL dominio.com.br/exemplo a classe chamada não será 'Exemplo', mas sim
     * 'DynamicValue', enquanto que o texto 'exemplo' será salvo como parâmetro. O nome do parâmetro
     * também será 'dynamicValue'. É este tipo de valor que este método retorna.
     * 
     * @param string $parameter        Nome da classe como parâmetro.
     * 
     */
    protected function getClassAsParameter($parameter)
    {
        if(substr($parameter, 0, 1) === '?')
            $parameter = substr($parameter, 1, strlen($parameter));
            
        Debug::trace(debug_backtrace()[0]);

        if(!array_key_exists($parameter, $this->classAsParameter)){
            Debug::error('CONTROLLER004', $parameter)::print();
        }
        return $this->classAsParameter[$parameter];
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
            $this->parameters = ltrim($path, '/');
        } else {
            Debug::error('FILE005')->print();
        }
    }
}
