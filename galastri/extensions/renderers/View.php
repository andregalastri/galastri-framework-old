<?php
/**
 * - View.php -
 * 
 * Arquivo que contém os comandos do renderizador view. Este renderizador exibe uma página HTML
 * e poderá exibir dados processados pelo controller. Todo renderizador deve ser configurado na
 * rota da URL, no arquivo config\routes.php.
 */
namespace galastri\extensions\renderers;

trait View {
    private $viewController = TRUE;
    private $view;

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
    private function view(){
        $this->debug->trace = debug_backtrace()[0];
        
        $this->view       = new \StdClass;
        $this->view->data = NULL;
        
        $this
            ->viewCheckObject()
            ->viewCheckHasView()
            ->viewSetTemplate()
            ->viewCheckExists();
        
        $this->view->data->data = $this->checkAuth($this->view->data);
        
        $this->requireContent($this->view->data, $this->view->data->template["root"]);
    }
    
    /**
     * Verifica se o controller é um objeto. Caso seja, então é chamado o método getRendererData()
     * que trás uma StdClass com uma série de atributos que incluem os dados processados e
     * retornados pelo controller. P template HTML pode ser montado a partir destes dados, e toda
     * informação processada pode ser exibida.
     */
    private function viewCheckObject(){
        $controller = $this->controller;

        if(is_object($controller)){
            $this->view->data = $controller->getRendererData();
        } else {
            $this->debug->error("CONTROLLER003", gettype($controller))->print();
        }
        return $this;
    }
    
    /**
     * Verifica se o caminho para o arquivo da view foi configurado.
     */
    private function viewCheckHasView(){
        $view = $this->view->data->view;
        $path = $this->view->data->path;

        if($path === FALSE){
            $this->debug->error("VIEW002", $view)->print();
        }
        return $this;
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
    private function viewSetTemplate(){
        $template = GALASTRI["template"];

        $data = $this->view->data;
        $import = $data->import;
        
        /** Configuração do template. */
        if(!empty($data->template)){
            foreach($data->template as $key => $value){
                $template[$key] = keyExists($key, $data->template["parts"], $value);
            }
        }
        
        /** Configuração dos arquivos adicionais. */
        if(!empty($data->import)){
            $import = [];
            foreach($data->import as $file){
                $split = explode(".", $file);
                $import[] = sprintf(GALASTRI["importTags"][$split[1]], $file);
            }
        }

        /** Configuração do título da página. */
        foreach(GALASTRI["title"]["template"] as $part){
            if(array_key_exists($part, GALASTRI["title"])){
                $title[] = GALASTRI["title"][$part];
            } else {
                if(property_exists($data, $part)){
                    $title[] = $data->$part;
                }
            }  
        }

        $this->view->data->template = $template;
        $this->view->data->import   = $import;
        $this->view->data->title    = ltrim(implode("", $title), GALASTRI["title"]["divisor"]);
        $this->view->data->view     = GALASTRI["folders"]["view"]."/".ltrim($data->view, "/");

        return $this;
    }

    /**
     * Verifica se o arquivo da view existe.
     */
    private function viewCheckExists(){
        $view = $this->view->data->view;

        if(!is_file($view)){
            $this->debug->error("VIEW001", $view)->print();
        }
    }
    
    /**
     * Este método é usado no arquivo Galastri.php para verificar se a rota foi configurada com
     * o status de offline.
     */
    private function viewCheckOffline(){
        $offline = $this->route->offline;
        $urlString = $this->route->offline;
        
        if($offline){
            $url = GALASTRI["urls"]["maintenance"];
            if($urlString !== $url){
                $this->redirect->location($url);
            }
        }
        return $this;
    }
}