<?php
/**
 * - File.php -
 * 
 * Arquivo que contém os comandos do renderizador file. Este renderizador exibe arquivos. O seu
 * funcionamento é o mais diferente dentre os renderizadores padrão. Ao contrário dos demais,
 * todos os parâmetros usados na URL formam o caminho onde o arquivo está. Por exemplo, supondo
 * que a rota abaixo seja configurada para ser renderizada pelo renderizador file:
 * 
 *     dominio.com/imagens
 * 
 * Tudo o que vier depois de /imagens deverá montar o caminho onde o arquivo está. Por exemplo:
 * 
 *     dominio.com/imagens/usuario/avatar.jpg
 * 
 * O caminho especificado significa que o arquivo avatar.jpg está dentro da pasta imagens/usuario.
 * Ou seja, todos os parâmetros formam um caminho.
 * 
 * - ARQUIVOS ACESSADOS DINAMICAMENTE -
 * A descrição acima apresenta o comportamento padrão do renderizador para exibir arquivos. Existe
 * porém a necessidade de se alterar este comportamento, principalmente quando se trata de arquivos
 * de acesso restrito. Neste caso será necessário configurar um controller e nele alterar os
 * parâmetros que formam o caminho do arquivo para um novo caminho.
 * 
 * Por exemplo, suponha que um arquivo é restrito a um usuário e somente ele pode acessar este
 * arquivo. O comportamento padrão do renderizador faz com que o arquivo seja acessível por
 * qualquer usuário.
 * 
 * Para se resolver este problema, uma abordagem interessante é criar um método na controller que
 * fará o controle de duas coisas:
 * - Da sessão e permissão de acesso      O controller verifica se o usuário que está acessando
 *                                        o arquivo possui permissão para acessá-lo.
 * 
 * - Controle do arquivo através de um    Pode-se manipular os parâmetros da URL. Ao invés de
 *   código de acesso.                    os parâmetros representarem um caminho do arquivo, ele
 *                                        poderia representar apenas um código. Este código
 *                                        poderia estar armazenado em um banco de dados em que
 *                                        se relaciona o código com o caminho real do arquivo.
 * 
 *                                        Ao acessar a URL contendo o código, este seria consultado
 *                                        no banco de dados e traria o caminho real do arquivo.
 *                                        Em seguida este caminho real seria colocado no lugar
 *                                        do caminho da URL através do método filePath(), da
 *                                        classe padrão core\Controller.
 * 
 * Veja como ficaria:
 * - A URL ficaria parecida com isto:
 * 
 *   dominio.com.br/imagens/img_cod001
 * 
 * - Este caminho não representa o caminho real do arquivo, mas sim um código que representa o
 *   caminho real. É necessário mudar, portanto, o comportamento padrão do renderizador.
 * 
 * - No banco de dados, haveria uma relação entre código e o caminho real do arquivo. Por exemplo,
 *   o código img_cod001 poderá ser o código para o arquivo localizado em:
 *   
 *   imagens/uploads/minha_imagem.jpg
 * 
 * - Por conta disso, faz-se uma busca no banco de dados pelo caminho real da imagem, baseado no
 *   código do parâmetro 1. Outra abordado é este código ser passado por meio do método GET.
 * 
 * - O banco de dados retorna o endereço real do arquivo.
 * 
 * - Utiliza-se o método filePath(), que é um método da classe core\Controller, para informar o
 *   endereço real do arquivo. Este método fará a substituição total dos parâmetros da URL para o
 *   caminho retornado pelo banco de dados. Esta manipulação de parâmetros da URL é possível de
 *   ser feita apenas na controller.
 * 
 * - Desta forma, apesar da URL não apresentar o caminho do arquivo, o arquivo foi especificado
 *   nos parâmetros por conta da busca do código informado em um banco de dados. Por isso,  o
 *   arquivo será exibido normalmente.
 */
namespace galastri\extensions\renderers;

use galastri\core\Debug;
use galastri\core\Route;

trait File
{
    private static $file;

    private static function fileController(){
        return false;
    }

    /**
     * Método principal que faz uma série de testes para verificar se os parâmetros informados
     * levam a um arquivo válido.
     * 
     * Primeiro é verificado se o controller retorna um objeto. Este renderizador não exige um
     * controller especificado. Caso não haja um controller, o teste passará Em seguida é verificado se o
     * arquivo especificado foi configurado. A extensão do arquivo também é verificada, pois ela
     * deve estar configurada em config\default.php. Por fim verifica-se se o arquivo existe.
     * 
     * Todos os dados processados e retornados pelo controller estarão disponíveis no atributo
     * data.
     * 
     * Estando tudo correto, é verificado se o arquivo foi configurada como sendo restrita, ou seja,
     * acessível apenas caso esteja com uma sessão configurada.
     * 
     * São configurados diversos cabeçalhos com o intuito de armazenar cache e exibir corretamente
     * o tipo de arquivo requisitado.
     */
    private static function file()
    {
       Debug::trace(debug_backtrace()[0]);

        self::$file = new \StdClass;

        self::fileCheckObject()
            ::fileCheckPath()
            ::fileCheckExtension()
            ::fileCheckContentType()
            ::fileCheckExists();
        
        self::$file->authFailUrl = Route::authFailUrl();
        self::$file->authBlock = Route::authBlock();
    
        self::$file = self::checkAuth(self::$file);

        
        /** Tags responsáveis por controlar o cache do arquivo no navegador do usuário. O uso de
         * uma e-tag permite que o arquivo seja armazenado em cache e, caso seja modificado, o
         * novo arquivo seja carregado no lugar do arquivo em cache. Lembrando que tudo isso só
         * será utilizado caso a configuração de cache esteja ativa. */
        $etag = md5(filemtime(self::$file->path).self::$file->path);

        header('Last-Modified: '.gmdate('r', time()));
        header('Cache-Control: '.GALASTRI['contentType'][self::$file->extension][1]);
        header('Expires: Tue, 01 Jul 1980 1:00:00 GMT');
        header('Etag: '.$etag); 
        
        /** Cabeçalhos para caso o arquivo esteja configurado para ser baixável. */
        if(self::$file->downloadable){
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename(self::$file->path).'"');
            header('Expires: 0');
            header('Pragma: public');
            header('Content-Length: '.filesize(self::$file->path));
            flush();
            ob_start();
            readfile(self::$file->path);
            ob_end_flush;
            flush();
            exit();
        } else {
            /** Caso o arquivo não esteja configurado para ser baixável, ele será renderizado na
             * tela do usuário. É verificado se o cache está ativo. Caso esteja, dois testes são
             * executados
             * 
             * 1. Verifica se o prazo do cache expirou. Caso sim, o arquivo deverá ser baixado de
             * novo. Caso não, então será usado o arquivo em cache.
             * 
             * 2. Mesmo se a verificação anterior confirmar que o prazo não expirou, o arquivo pode
             * ter sido modificado. Este segundo teste verifica se o arquivo foi modificado. Caso
             * sim, então o arquivo deverá ser baixado novamente. Caso o arquivo não tenha sido
             * modificado, então será usado o arquivo em cache. */
            if(self::$file->cache['status']){
                $cached = false;
                if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])){
                    if(time() <= strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])+self::$file->cache['expire']){
                        $cached = true;
                    }
                }
                if(isset($_SERVER['HTTP_IF_NONE_MATCH'])){
                    if(str_replace('"', '', stripslashes($_SERVER['HTTP_IF_NONE_MATCH'])) != $etag){
                        $cached = false;
                    }
                }
                if($cached){
                    header('HTTP/1.1 304 Not Modified');
                    exit();
                }
            }
            
            header('Content-type: '.GALASTRI['contentType'][self::$file->extension][0]);
            self::printContent(file_get_contents(self::$file->path));
        }
    }
    
    /**
     * Verifica se o controller foi definido. Este renderizador não obriga o uso de um controller,
     * por isso, caso não haja controller definido, o os parâmetros e configurações usados serão
     * os informados pela rota.
     * 
     * Caso o controller esteja definido, então é verificado se ele é um objeto. Caso seja, então
     * é chamado o método getRendererData() que trás uma StdClass com uma série de atributos que
     * incluem os dados processados e retornados pelo controller. P template HTML pode ser montado
     * a partir destes dados, e toda informação processada pode ser exibida.
     */
    private static function fileCheckObject()
    {
        $controller = self::$controller;

        if($controller){
            if(is_object($controller)){
                self::$file = $controller->getRendererData();
//                self::$file->parameters   = $controller->getRendererData()->parameters;
//                self::$file->downloadable = $controller->getRendererData()->downloadable;
//                self::$file->cache        = $controller->getRendererData()->cache;
            } else {
               Debug::error('CONTROLLER003', gettype($controller))::print();
            }
        } else {
            self::$file->parameters   = Route::parameters()['afterMethod'];
            self::$file->downloadable = Route::downloadable();
            self::$file->cache        = Route::cache();
        }
        return __CLASS__;
    }

    /**
     * Verifica se os parâmetros armazenam valores, para que o caminho do arquivo seja montado.
     */
    private static function fileCheckPath()
    {
        $parameters = self::$file->parameters;
        
        if(!empty($parameters)){
            if(is_array($parameters)){
                self::$file->parameters = rtrim(implode('/', $parameters), '/');
            }
        } else {
           Debug::error('FILE002')::print();
        }
        return __CLASS__;
    }

    /**
     * Verifica se o arquivo informado possui uma extensão e se esta extensão está configurada no
     * arquivo config/default.php. Isso é necessário pois a chamada do arquivo irá requerer um
     * tipo MIME e isto precisa estar informado no arquivo de configuração.
     */
    private static function fileCheckExtension()
    {
        $parameters = self::$file->parameters;
        $folder = GALASTRI['folders']['app'];
        $path = Route::baseFolder() ?? Route::path();
        $path = $path === '/' ? '/' : "$path/";

        self::$file->path = path($folder.$path.$parameters);

        if(sizeof(explode('.', $parameters))>=2){
            self::$file->extension = lower(array_slice(explode('.', $parameters), -1, 1)[0]);
        } else {
           Debug::error('FILE003')::print();
        }
        return __CLASS__;
    }

    /**
     * Verifica se o tipo MIME foi definido para a extensão.
     */
    private static function fileCheckContentType()
    {
        $extension = self::$file->extension;
        $contentType = GALASTRI['contentType'];
        
        if(!array_key_exists($extension, $contentType)){
           Debug::error('FILE001', $extension)::print();
        }
        return __CLASS__;
    }
    
    /**
     * Verifica se o arquivo requisitado existe.
     */
    private static function fileCheckExists()
    {
        $path = self::$file->path;
        
        if(!is_file($path)){
           Debug::error('FILE004', $path)::print();
        }
    }
    
    /**
     * Este método é usado no arquivo Galastri.php para verificar se a rota foi configurada com
     * o status de offline. Aqui foi reaproveitado o método da view, pois o teste é exatamente
     * o mesmo.
     */
    private static function fileCheckOffline()
    {
        return self::viewCheckOffline();
    }
}
