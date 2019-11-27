<?php
/**
 * - Route.php -
 * 
 * Classe que define as propriedades da rota baseado na URL e nas configurações de rotas do
 * arquivo config/routes.php
 */
namespace galastri\core;

class Route
{
    private static $cache;
    private static $controller;
    private static $method;
    private static $routes;
    private static $baseFolder;
    private static $view;
    private static $parameters;
    private static $path;
    private static $offline;
    private static $authTag;
    private static $onAuthFail;
    private static $urlString;
    private static $title;
    private static $template;
    private static $import;
    private static $renderer;
    private static $downloadable;
    private static $inheritanceConfig;

    /** Classe que trabalha sob o padrão Singleton, por isso, não poderá ser instanciada. */
    private function __construct(){}

    /**
     * Este microframework se utiliza de URLs amigaveis para as requisições e navegação entre as
     * páginas. Por conta disso, toda URL sofre um tratamento de forma a impedir o comportamento
     * padrão das URLs.
     * 
     * O padrão comum é que a URL acesse diretamente um arquivo. Aqui, todas as URLs são
     * redirecionadas para o arquivo galastri.php. Este arquivo inicializa o microframework que
     * em seguida realiza a resolução da URL.
     * 
     * Como este arquivo possui muitos comandos, a explicação de cada bloco importante de código
     * será feito diretamente junto aos códigos.
     */

    /**
     * Método que faz a resolução da URL.
     */
    public static function resolve()
    {
        /** A URL requisitada é armazenada na variável $url, que sofrerá duas modificações. A
         * primeira é a remoção de querystrings, ou seja, tudo o que existir após o sinal de
         * interrogação ? que por ventura estiver na URL.
         * 
         * A URL é convertido para letras minúsculas de forma a tornar, indiferente se existem
         * termos em letras maiúsculas ou minúsculas na URL.
         * 
         * Em seguida, cada termo que estiver logo após uma barras / indicá um parâmetro que será
         * armazenado em uma array. Supondo que a URL seja a seguinte:
         * 
         *     dominio.com/vendas/nova_venda
         * 
         * O resultado disso é que 'vendas' e 'nova_venda' são parâmetros. Estes valores, portanto
         * serão armazenados em uma array, que é a própria variável $url.
         * 
         * Também serão utilizadas aqui as rotas configuradas no arquivo config/routes.php. */
        $url       = explode('?', lower($_SERVER['REQUEST_URI']));
        $url       = explode('/', $url[0]);
        $routes    = GALASTRI['routes'];
        $routePath = '';

        /** Quando se trata da index, ou seja, quando nenhum parâmetro é informado na URL através
         * das barras /, pode ocorrer de a variáveis $url armazenar duas chaves vazias. Neste caso
         * é importante remover uma das chaves vazias. */
        if(empty($url[1])) array_shift($url);

        /** Todos os valores da array $url serão acrescidas com uma barra / à frente do valor
         * atual. Isso é necessário pois todas as áreas armazenadas nas configurações das rotas
         * são iniciadas por uma barra. */
        foreach($url as $key => &$routeName) $routeName = "/$routeName";
        unset($routeName);

        /** Aqui estão listados quais configurações da área pai, configurada em config/routes.php,
         * são herdáveis pelas áreas e paǵinas filhas.
         * 
         * Por exemplo, suponha a seguinte configuração:
         * 
         *     '/area1' => [
         *         '@pagina_a' => [],
         *         '@pagina_b' => [],
         *     ],
         * 
         * Suponha que se queira que todas as páginas de area1 fiquem offline para manutenção.
         * Ao invés de se colocar uma chave 'offline' => true para cada página, pode-se colocar
         * uma única chave dessas logo após a declaração de area1. Esta chave é herdável, ou seja,
         * todas as páginas de area1 também possuirão esta chave o seu respectivo valor, fazendo
         * com que tornar uma área offline fique muito mais prática.
         * 
         *     '/area1' => [
         *         'offline' => true,
         *         '@pagina_a' => [],
         *         '@pagina_b' => [],
         *     ],
         * 
         * Cada uma das configurações abaixo são herdáveis seguindo este mesmo princípio. O loop
         * foreach abaixo realiza justamente um teste através do método checkInheritanceData() para
         * se verificar e armazenar valores herdados para todas as áreas e páginas que não
         * possuirem tal configuração. */
        self::$inheritanceConfig = [
            'offline'      => false,
            'downloadable' => false,
            'cache'        => false,
            'authTag'      => false,
            'onAuthFail'   => false,
        ];

        foreach($url as $routeName){
            if(array_key_exists($routeName, $routes)){
                self::checkInheritanceData($routes[$routeName]);

                $routePath .= $routeName;
                $routes     = $routes[$routeName];
            }
        }

        /** A URL tratada será armazenada na variável $urlString em formato de string e será usada
         * posteriormente para se criar os parâmetros internos no microframework. Ela armazenará
         * o caminho completo da URL trata e servirá como base para se verificar o que será um
         * parâmetro interno do que faz parte da rota.
         * 
         * Já a variável $routePath armazena apenas a rota e servirá como base para se verificar
         * o que, da URL, é parâmetro interno do que é parte da rota.
         * Para entender melhor, veja a URL abaixo:
         * 
         *     dominio.com/vendas/editar_venda/1001
         * 
         * A variável $urlString irá armazenar tudo:          /vendas/editar_venda/1001.
         * A variável $routePath irá armazenar apenas a rota: /vendas/.
         * 
         * Perceba que o valor editar_venda e 1001 não foram armazenados como sendo parte da rota
         * pois eles são considerados parâmetros que pertencem à rota. Por isso, esses valores
         * deverão ser armazenados dentro da variável $parameters.
         * 
         * Tendo os parâmetros armazenados em uma array, verifica-se se o parâmetro 0 possui o
         * valor de um método. Todo método é precedido de um arroba @. */
        $urlString     = '/'.ltrim(implode($url), '/');
        $routePath     = '/'.ltrim($routePath, '/');
        $parameters    = explode('/', ltrim(replaceOnce($routePath, '', $urlString), '/'));
        $method        = '@'.($parameters[0] ?? '');

        /** Verifica se o método existe na rota. Caso não, então o método padrão será o @main. */
        $method = array_key_exists($method, $routes) ? $method : '@main';
        $method = $method === '@' ? '' : $method;

        /** Caso o método, seja ele @main ou qualquer outro, esteja configurado nas rotas, então
         * significa que há um controller. Aqui, portanto, é configurado qual é o controller (que
         * nada mais é do que a classe que contém os métodos e a view, que é configurada mesmo
         * que o renderizador seja outro.
         * 
         * Caso contrário, então método, view e controller serão vazios. */
        if(array_key_exists($method, $routes)){
            self::checkInheritanceData($routes[$method]);

            if(array_key_exists('controller', $routes)){
                $controller = $routes['controller'];
                $controller = str_replace('/', '\\', $controller);
            } else {
                $controller = $routePath === '/'  ? '/index' : $routePath;

                $controller = explode('/', $controller);
                foreach($controller as &$parts)
                    $parts = convertCase($parts, 'pascal');
                unset($parts);

                $controller = implode('/', $controller);
            }

            if(array_key_exists('view', $routes[$method])){
                $view = $routes[$method]['view'];
            } else {
                $view = $method === '@main' ? "$controller.php" : "$controller/".ltrim(convertCase($method, 'pascal').'.php', '@');
            }

            $controller = str_replace(['/','.'], ['\\',''], GALASTRI['folders']['controller']).str_replace('/', '\\', $controller);
        } else {
            $view       = null;
            $controller = null;
            $method     = null;

            foreach($routes as $option => $value){
                if(lower(gettype($value)) === 'array') unset($routes[$option]);
            }
        }

        /** A rota é armazenada na variável $route e contém todos os dados processados até aqui.
         * Caso se tenha configurado uma view e/ou um controller diretamente na rota, estes são
         * removidos da array. */
        $route = $routes[$method] ?? $routes;

        if(!empty(array_filter($route))){
            unset($route['controller']);
            unset($route['view']);
        }

        /** Abaixo, cada um dos dados processados são armazenados em atributos. Estes atributos
         * serão acessíveis através de métodos getters com o mesmo nome dos atributos. */
        if(self::$inheritanceConfig['cache'] === false){
            self::$cache = GALASTRI['cache'];
        } else {
            $cache = self::$inheritanceConfig['cache'];
            self::$cache['status'] = $cache['status'] ?? GALASTRI['cache']['status'];
            self::$cache['expire'] = $cache['expire'] ?? GALASTRI['cache']['expire'];
        }
        self::$controller   = $controller;
        self::$method       = ltrim($method,'@');
        self::$routes       = $routes;
        self::$view         = $view;
        self::$parameters   = $parameters;
        self::$path         = $routePath;
        self::$offline      = self::$inheritanceConfig['offline'];
        self::$authTag      = self::$inheritanceConfig['authTag'];
        self::$onAuthFail   = $route['onAuthFail'] ?? self::$inheritanceConfig['onAuthFail'];
        self::$urlString    = $urlString;
        self::$title        = $route['title'] ?? '';
        self::$template     = $route['template'] ?? [];
        self::$import       = $route['import'] ?? [];
        self::$renderer     = $route['renderer'] ?? false;
        self::$baseFolder   = $route['baseFolder'] ?? null;
        self::$downloadable = self::$inheritanceConfig['downloadable'];
    }

    /**
     * Métodos getters para recuperar o conteúdo dos atributos.
     */
    public static function method($conversion = 'camelCase')
    {
        return $conversion == 'camelCase' ? convertCase(self::$method, 'camel') : self::$method;
    }
    public static function cache()       { return self::$cache; }
    public static function controller()  { return self::$controller; }
    public static function routes()      { return self::$routes; }
    public static function view()        { return self::$view; }
    public static function parameters()  { return self::$parameters; }
    public static function path()        { return self::$path; }
    public static function offline()     { return self::$offline; }
    public static function authTag()     { return self::$authTag; }
    public static function onAuthFail()  { return self::$onAuthFail; }
    public static function urlString()   { return self::$urlString; }
    public static function title()       { return self::$title; }
    public static function template()    { return self::$template; }
    public static function import()      { return self::$import; }
    public static function renderer()    { return self::$renderer; }
    public static function baseFolder()  { return self::$baseFolder; }
    public static function downloadable(){ return self::$downloadable; }

    /**
     * Método responsável por verificar se existem configurações herdáveis. Caso sim, então a
     * configuração é ativada para todas as áreas e páginas filhas.
     * 
     * @param string $routeArray       Armazena a array com os dados da área a ser verificada.
     */
    private static function checkInheritanceData($routeArray)
    {
        foreach(self::$inheritanceConfig as $config => &$value){
            if(array_key_exists($config, $routeArray)){
                $value = $routeArray[$config];
            }
        }
    }
}
