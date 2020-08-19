<?php
/**
 * !!DOCUMENTAR
 * - Route.php -
 * 
 * Classe que define as propriedades da rota baseado na URL e nas configurações de rotas do
 * arquivo config/routes.php
 */
namespace galastri\core;

use \galastri\core\Debug;
use \galastri\core\Authentication;

class Route
{
    private static $urlArray;
    private static $route = [
        'classPath' => '',
        'urlPath' => '',
        'currentNode' => '',
    ];
    private static $urlParameters = [
        'afterMethod' => [],
        'classAsParameter' => [],
    ];
    private static $tmpParameters = [];
    private static $inheritanceConfig = [
        'renderer'     => null,
        'offline'      => false,
        'downloadable' => false,
        'cache'        => false,
        'authTag'      => false,
        'authFailUrl'  => null,
        'template'     => false,
        'error404Url'  => null,
        'siteName'     => null,
    ];

    private static $controller;
    private static $method;
    private static $methodNames = [
        'original' => '',
        'camelCase' => '',
    ];
    private static $methodParameters;
    private static $view;
    private static $classPath;
    private static $path;
    private static $offline;
    private static $authTag;
    private static $authStatus;
    private static $authBlock = false;
    private static $authFailUrl;
    private static $error404Url;
    private static $cache;
    private static $routes;
    private static $baseFolder;
    private static $parameters;
    private static $newParameters;
    private static $urlString;
    private static $siteName;
    private static $title;
    private static $template;
    private static $import;
    private static $renderer;
    private static $downloadable;

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

    private static function getUrl()
    {
        $url = explode('?', $_SERVER['REQUEST_URI']);
        $url = explode('/', $url[0]);

        if(empty($url[1])) array_shift($url);
        
        self::$urlArray = $url;
    }

    private static function addSlashs()
    {
        foreach(self::$urlArray as &$routeName)
            $routeName = "/$routeName";

        unset($routeName);
    }

    private static function parseNodes($routeConfig)
    {
        foreach(self::$urlArray as $key => $urlRoute){
            if(array_key_exists($urlRoute, $routeConfig)){
                self::buildPath();
                self::resolveInheritanceParameters($routeConfig[$urlRoute]);
                self::$route['currentNode'] = $urlRoute;
                
                self::$authStatus = self::resolveAuthStatus(self::$inheritanceConfig['authTag']);
                $routeConfig = $routeConfig[$urlRoute];
                
                array_shift(self::$urlArray);
                
                if(!empty(self::$tmpParameters)){
                    array_shift(self::$tmpParameters);
                }
            } else {
                break;
            }
        }
        self::$route['keys'] = array_keys($routeConfig);
        self::$route['data'] = $routeConfig;
    }

    private static function resolveInheritanceParameters($routeArray)
    {
        foreach(self::$inheritanceConfig as $config => &$value){
            if(array_key_exists($config, $routeArray)){
                $value = $routeArray[$config];
            }
        }
    }

    private static function buildPath()
    {
        $currentNode = self::$route['currentNode'];
        
        if($currentNode === '/' or $currentNode === '')
            return false;
        
        $currentNode = ltrim($currentNode, '/?');
        $currentNode = ltrim($currentNode, '?');

        self::$route['urlPath'] .= '/'.$currentNode;
        self::$route['classPath'] .= '/'.convertCase($currentNode, 'pascal');
    }

    private static function defineRouteUrl()
    {
        $url = implode(self::$urlArray);
        $url = ltrim($url, '/');
        $url = "/$url";

        self::$route['url'] = $url;
    }

    private static function defineUrlParameters()
    {
        $tmpParameters = replaceOnce(self::$route['classPath'], '', self::$route['url']);
        $tmpParameters = ltrim($tmpParameters, '/');

        if(!empty($tmpParameters)){
            $tmpParameters = explode('/', $tmpParameters);
            self::$tmpParameters = $tmpParameters;
        }
    }

    private static function defineController($runTimes = 0)
    {
        Debug::trace(debug_backtrace()[0]);
        
        $tmpParameters = self::$tmpParameters;
        
        self::buildPath();

        if(empty($tmpParameters)){
            self::setMethod('main');
        } else {
            if(array_search('@'.$tmpParameters[0], self::$route['keys']) !== false){
                self::setMethod($tmpParameters[0]);
                array_shift(self::$tmpParameters);
            } else {
                $dynamicNode = self::searchDynamicNode();

                if($dynamicNode['count'] > 0){
                    array_shift(self::$urlArray);
                    array_shift(self::$tmpParameters);

                    self::setUrlParameter($dynamicNode['nodeName'], $tmpParameters[0]);
                    
                    self::$authStatus = self::resolveAuthStatus($tmpParameters[0]);
                    self::$route['currentNode'] = $dynamicNode['nodeName'];
                    
                    self::parseNodes(self::$route['data'][$dynamicNode['nodeName']]);
                    self::defineRouteUrl();
                    self::defineUrlParameters();
                    self::defineController($runTimes + 1);
                } else {
                    self::setMethod('main');
                }
            }
            self::setUrlParameters(self::$tmpParameters);
        }
        self::setController();
    }

    private static function searchDynamicNode()
    {
        Debug::trace(debug_backtrace()[0]);

        $dynamicController = array_filter(self::$route['keys'], function($key){
            if(substr($key, 0, 2) === '/?'){
                return true;
            }
        });

        if(count($dynamicController) > 1)
            Debug::error('ROUTE001', count($dynamicController))::print();

        self::resolveInheritanceParameters(self::$route['data'][implode($dynamicController)] ?? []);

        return [
            'nodeName' => implode($dynamicController),
            'count' => count($dynamicController),
        ];
    }

    private static function setUrlParameters($array)
    {
        self::$urlParameters['afterMethod'] = $array;
    }

    private static function setUrlParameter($key, $value)
    {
        $key = ltrim($key, '/');
        $key = ltrim($key, '?');

        self::$urlParameters['classAsParameter'][$key] = $value;
    }

    private static function setController()
    {
        $configFolder = rtrim(GALASTRI['folders']['controller'], '/');
        $controller = self::$route['classPath'];

        if(empty($controller))
            $controller = '/Index';

        self::$controller = str_replace(['/','.'], ['\\',''], $configFolder);
        self::$controller .= str_replace('/', '\\', $controller);
    }

    private static function setMethod($name)
    {
        
        $name = ltrim($name, '@');
        self::$methodNames['original'] = $name;
        self::$methodNames['camelCase'] = convertCase($name, 'camel');
    }

    private static function defineView()
    {
        self::$view = self::$route['classPath'].'/'.self::$methodNames['camelCase'].'.php';
    }

    private static function defineMethodParameters()
    {
        $methodNode = '@'.self::$methodNames['original'];

        if(!isset(self::$route['data'][$methodNode])){
            self::$methodParameters = self::$route['data'];
        } else {
            self::$methodParameters = self::$route['data'][$methodNode];
        }
    }

    private static function resolveAuthFailUrl($authFailUrl)
    {
        if($authFailUrl){
            $urlKeys = explode('/', $authFailUrl);

            $classAsParameter = self::getUrlParameters()['classAsParameter'];
            foreach($urlKeys as $urlKey){
                if(self::isDynamic($urlKey)){
                    $urlKey = ltrim($urlKey, '?');
                    $filteredUrl[] = $classAsParameter[$urlKey];
                } else {
                    $filteredUrl[] = $urlKey;
                }
            }
            $authFailUrl = implode('/', $filteredUrl);
        }

        return $authFailUrl;
    }

    private static function resolveAuthStatus($authTag)
    {
        if($authTag){
            if(self::isDynamic($authTag)){
                $classAsParameter = self::getUrlParameters()['classAsParameter'];
                $authTag = ltrim($authTag, '?');
                $authTag = $classAsParameter[$authTag];
            }

            if(Authentication::validate($authTag) === false and self::$authBlock === false)
                self::$authBlock = true;

            return Authentication::validate($authTag);
        } else {
            return true;
        }
    }

    private static function isDynamic($string)
    {
        return substr($string, 0, 1) === '?';
    }

    private static function getUrlParameter($key) { return self::$urlParameters[$key]; }
    private static function getUrlParameters() { return self::$urlParameters; }
    private static function getController(){ return self::$controller; }
    // private static function getMethod($type = 'camelCase'){ return self::$methodNames[$type]; }
    private static function getView(){ return self::$view; }
    private static function getMethodParameters(){ return self::$methodParameters; }
    private static function getMethodParameter($key, $default = null){ return self::$methodParameters[$key] ?? $default; }

    public static function resolve()
    {
        Debug::trace(debug_backtrace()[0]);

        self::getUrl();
        self::addSlashs();
        self::parseNodes(GALASTRI['routes']);
        self::defineRouteUrl();
        self::defineUrlParameters();
        self::defineController();
        self::defineView();

        self::defineMethodParameters();

        self::resolveInheritanceParameters(self::getMethodParameters());

        if(self::$inheritanceConfig['cache'] === false){
            self::$cache = GALASTRI['cache'];
        } else {
            $cache = self::$inheritanceConfig['cache'];
            self::$cache['status'] = $cache['status'] ?? GALASTRI['cache']['status'];
            self::$cache['expire'] = $cache['expire'] ?? GALASTRI['cache']['expire'];
        }
        self::$controller   = self::getMethodParameter('controller', self::getController());
        self::$method       = self::$methodNames['camelCase'];
        self::$routes       = self::$route['data'];
        self::$view         = self::getMethodParameter('view', self::getView());
        self::$parameters   = self::getUrlParameters();
        self::$classPath    = self::$route['classPath'];
        self::$path         = self::$route['urlPath'];
        self::$offline      = self::$inheritanceConfig['offline'];
        self::$authTag      = self::$inheritanceConfig['authTag'];
        // self::$authStatus   = self::resolveAuthStatus(self::$inheritanceConfig['authTag']);
        self::$authFailUrl  = self::resolveAuthFailUrl(self::$inheritanceConfig['authFailUrl']);
        self::$error404Url  = self::$inheritanceConfig['error404Url'] ?? GALASTRI['error404Url'];
        self::$urlString    = self::$route['urlPath'].self::$route['url'];
        self::$siteName     = self::$inheritanceConfig['siteName'] ?? GALASTRI['title']['siteName'];
        self::$title        = self::getMethodParameter('title', '');
        self::$template     = self::$inheritanceConfig['template'];
        self::$import       = self::getMethodParameter('import', []);
        self::$renderer     = self::$inheritanceConfig['renderer'];
        self::$baseFolder   = self::getMethodParameter('baseFolder', null);
        self::$downloadable = self::$inheritanceConfig['downloadable'];
    }

    /**
     * Métodos getters para recuperar o conteúdo dos atributos.
     */
    public static function method($conversion = 'camelCase')
    {
        return $conversion == 'camelCase' ? self::$methodNames['camelCase'] : self::$methodNames['original'];
    }
    public static function cache()       { return self::$cache; }
    public static function controller()  { return self::$controller; }
    public static function routes()      { return self::$routes; }
    public static function view()        { return self::$view; }
    public static function parameters()  { return self::$parameters; }
    public static function path()        { return self::$path; }
    public static function offline()     { return self::$offline; }
    public static function error404Url() { return self::$error404Url; }
    public static function authTag()     { return self::$authTag; }
    public static function authFailUrl() { return self::$authFailUrl; }
    public static function authStatus()  { return self::$authStatus; }
    public static function authBlock()   { return self::$authBlock; }
    public static function urlString()   { return self::$urlString; }
    public static function siteName()    { return self::$siteName; }
    public static function title()       { return self::$title; }
    public static function template()    { return self::$template; }
    public static function import()      { return self::$import; }
    public static function renderer()    { return self::$renderer; }
    public static function baseFolder()  { return self::$baseFolder; }
    public static function downloadable(){ return self::$downloadable; }
}
