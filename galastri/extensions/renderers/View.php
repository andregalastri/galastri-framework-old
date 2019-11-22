<?php
/**
 * - View.php -
 * 
 * Arquivo que contém os comandos do renderizador view. Este renderizador exibe uma página HTML
 * e poderá exibir dados processados pelo controller. Todo renderizador deve ser configurado na
 * rota da URL, no arquivo config\routes.php.
 */
namespace galastri\extensions\renderers;

use galastri\core\Debug;
use galastri\core\Redirect;
use galastri\core\Route;

trait View
{
    private static $view;

    private static function viewController()
    {
        return true;
    }

    /**
     * Método principal que faz uma série de testes para verificar se os dados retornados pelo
     * controller estão corretos.
     * 
     * Primeiro é verificado se o controller retorna um objeto. Em seguida é verificado se o
     * arquivo especificado como sendo a view foi configurado. O template então é processado e
     * armazenado e por fim verifica-se se a view existe.
     * 
     * Todos os dados processados e retornados pelo controller estarão disponíveis no atributo
     * data.
     * 
     * Estando tudo correto, é verificado se a página foi configurada como sendo restrita, ou seja,
     * acessível apenas caso esteja com uma sessão configurada.
     */
    private static function view()
    {
        Debug::trace(debug_backtrace()[0]);
        
        self::$view = new \StdClass;
        
        self::viewCheckObject()
            ::viewCheckHasView()
            ::viewSetTemplate()
            ::viewCheckExists();
        
        self::$view = self::checkAuth(self::$view);
        
        self::requireContent(self::$view, self::$view->template['root']);
    }
    
    /**
     * Verifica se o controller é um objeto. Caso seja, então é chamado o método getRendererData()
     * que trás uma StdClass com uma série de atributos que incluem os dados processados e
     * retornados pelo controller. P template HTML pode ser montado a partir destes dados, e toda
     * informação processada pode ser exibida.
     */
    private static function viewCheckObject()
    {
        $controller = self::$controller;

        if(is_object($controller)){
            self::$view = $controller->getRendererData();
        } else {
            Debug::error('CONTROLLER003', gettype($controller))::print();
        }
        return __CLASS__;
    }
    
    /**
     * Verifica se o caminho para o arquivo da view foi configurado.
     */
    private static function viewCheckHasView()
    {
        $view = self::$view->view;
        $path = self::$view->path;

        if($path === false){
            Debug::error('VIEW002', $view)::print();
        }
        return __CLASS__;
    }

    /**
     * Configura o template, verificando se cada parte foi especificada. O template padrão é
     * composto por quatro partes:
     * - O arquivo de template raiz, que é quem agrupa todos as partes;
     * - O arquivo que contém dados da tag <head>;
     * - O arquivo que contém dados da tag <nav>;
     * - O arquivo que contém dados da tag <footer>;
     * Estas partes podem ser desativadas, a depender da configuração no arquivo config\routes.php.
     * 
     * Este método também configura a importação dos arquivos adicionais (que em geral são colocadas
     * dentro da tag <head>), como arquivos .js e .css. É importante lembrar que tais arquivos
     * podem ser colocados diretamente no arquivo template\head.php, de forma que eles fiquem
     * disponíveis para todas as páginas. O parâmetro import configurado nas rotas serve para
     * adicionar arquivos que não são globais, ou seja, arquivos específicos para uma página.
     * 
     * Este método também configura o título da página, que geralmente é usado entre as tags <title>.
     */
    private static function viewSetTemplate()
    {
        $template = GALASTRI['template'];

        $data = self::$view;
        $import = $data->import;
        
        /** Configuração do template. */
        if(!empty($data->template)){
            foreach($data->template as $key => $value){
                $template[$key] = $data->template[$key] ?? $value;
            }
        }
        
        /** Configuração dos arquivos adicionais. */
        if(!empty($data->import)){
            $import = [];
            foreach($data->import as $file){
                $split = explode('.', $file);
                $import[] = sprintf(GALASTRI['importTags'][$split[1]], $file);
            }
        }

        /** Configuração do título da página. */
        foreach(GALASTRI['title']['template'] as $part){
            if(array_key_exists($part, GALASTRI['title'])){
                $title[] = GALASTRI['title'][$part];
            } else {
                if(property_exists($data, $part)){
                    $title[] = $data->$part;
                }
            }  
        }

        self::$view->template = $template;
        self::$view->import   = $import;
        self::$view->title    = ltrim(implode('', $title), GALASTRI['title']['divisor']);
        self::$view->view     = GALASTRI['folders']['view'].'/'.ltrim($data->view, '/');

        return __CLASS__;
    }

    /**
     * Verifica se o arquivo da view existe.
     */
    private static function viewCheckExists()
    {
        $view = self::$view->view;

        if(!is_file($view)){
            Debug::error('VIEW001', $view)::print();
        }
    }
    
    /**
     * Este método é usado no arquivo Galastri.php para verificar se a rota foi configurada com
     * o status de offline.
     */
    private static function viewCheckOffline()
    {
        $offline   = Route::offline();
        $urlString = Route::urlString();
        
        if($offline){
            if(isset($offline['redirect']) and $urlString !== '/')
                Redirect::location($offline['redirect']);

            self::printContent($offline['message'] ?? 'offline');
        }
        return __CLASS__;
    }
}
