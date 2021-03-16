<?php
/**
 * - Debug.php -
 * 
 * Classe que permite traçar a localização de erros internos do microframework quando a configuração
 * de debug estiver ativa na configuração do arquivo config/default.php.
 * 
 * Quando o debug está ativo e ocorrer um erro, uma mensagem é exibida detalhando o problema. Já
 * quando o debug está inativo, então a mensagem de erro é uma mensagem genérica de que houve um
 * erro.
 * 
 * Por isso é importante desativar o debug quando colocar um site em produção, pois determinadas
 * mensagens de erro podem apresentar dados do servidor que devem ser restritos somente durante
 * a fase de desenvolvimento.
 */
namespace galastri\core;

class Debug
{
    private static $message = null;
    private static $error   = false;
    private static $trace   = false;

    /** Classe que trabalha sob o padrão Singleton, por isso, não poderá ser instanciada. */
    private function __construct(){}

    /**
     * Método que armazena os dados da função debug_backtrace(), para traçar a rota dos erros
     * internos do microframwork.
     * 
     * @param array $trace              Armazena um dos vetores da função debug_backtrace().
     */
    public static function trace($trace)
    {
        self::$trace = $trace;
    }

    /**
     * Método que define que houve um erro e qual será a mensagem que será exibida.
     * 
     * @param string $tag               Armazena a tag que contém a mensagem de erro que será
     *                                  exibida enquanto o debug estiver ativo.
     * 
     * @param mixed ...$data            Armazena dados quaisquer que são importantes de serem
     *                                  informados nas mensagens de erro.
     */
    public static function error($tag, ...$data)
    {
        if(!GALASTRI['debug']){
            self::$message = '<code>OCORREU UM ERRO INTERNO. CONTATE O DESENVOLVEDOR.</code>';
        } else {
            $class  = self::$trace['class'];
            $method = self::$trace['function'];
            $line   = self::$trace['line'];
            $file   = self::$trace['file'];
            $error  = self::getMessage($tag, $data);

            self::$message = "
                <code>
                    <small>Erro durante a execução da função <b>$class->$method()</b></small><br/>
                    <big>$error</big><br/><br/>
                    <small>
                        LINHA <b>$line</b> EM <b>'$file'</b><br/>
                        $tag
                    </small>
                </code>";
        }
        self::$error = true;
        return __CLASS__;
    }

    /**
     * Método que retorna o status atual do atributo $error.
     */
    public static function getError()
    {
        return self::$error;
    }

    /**
     * Método que imprime uma mensagem de erro na tela.
     */
    public static function print()
    {
        exit(print(self::$message));
    }

    /**
     * Método que retorna uma mensagem de erro, podendo ser armazenado em uma variável, por exemplo.
     */
    public static function return()
    {
        return strip_tags(self::$message);
    }

    /**
     * Método que reune todas as tags de erros do microframework e suas respectivas mensagens de
     * erros.
     * 
     * @param string $tag               Armazena a tag que contém a mensagem de erro que será
     *                                  exibida enquanto o debug estiver ativo.
     * 
     * @param array data                Armazena dados quaisquer que são importantes de serem
     *                                  informados nas mensagens de erro.
     */
    private static function getMessage($tag, $data)
    {
        switch($tag){
            case 'ROUTE001':      return "Foram definidas $data[0] rotas dinâmicas (iniciadas com '/?') para esta<br>mesma área, no arquivo 'config/routes.php'.<br><br>É permitido apenas 1 rota deste tipo para cada área.";
            case 'ROUTE002':      return "A authTag '$data[0]' foi definida sem uma authFailUrl. É obrigatório definir uma authFailUrl para 'false' ou para uma url válida.";

            case 'REDIRECT001':   return "Nenhum parâmetro foi informado. É necessário informar uma string contendo uma URL ou uma palavra chave para redirecionamento.";

            case 'OFFLINE001':    return GALASTRI['offline']['message'];

            case 'CONFIG001':     return "A rota <b>'$data[0]'</b> não possui um renderizador configurado no arquivo <b>'config/routes.php'</b>.";
            case 'CONFIG002':     return "A rota <b>'$data[0]'</b> não possui o método <b>'$data[1]'</b> configurado no arquivo <b>'config/routes.php'</b>.";
            case 'CONFIG003':     return "A rota <b>'$data[0]'</b> requer um controller configurado no arquivo <b>'config/routes.php'</b>.";

            case 'RENDERER001':   return "O renderizador <b>'$data[0]'</b> requer um método configurado para a rota <b>'$data[1]'</b> no arquivo <b>'config/routes.php'</b>.";
            case 'RENDERER002':   return "O renderizador <b>'$data[0]'</b> requer um controller configurado para a rota <b>'$data[1]'</b> no arquivo <b>'config/routes.php'</b>.";
            case 'RENDERER003':   return "O renderizador <b>'$data[0]'</b> não existe.";
            case 'RENDERER004':   return "O renderizador não foi definido.";

            case 'CONTROLLER001': return "O controller <b>'$data[0]'</b> não existe.";
            case 'CONTROLLER002': return "O controller <b>'$data[0]'</b> não possui o método <b>'$data[1]'</b>.";
            case 'CONTROLLER003': return "É esperado que o controller retorne um objeto, mas o retorno é um dado do tipo <b>'$data[0]'</b>.";
            case 'CONTROLLER004': return "O parâmetro <b>'$data[0]'</b> não existe. É preciso defini-lo no arquivo <b>'config/routes.php'</b>.";
            case 'CONTROLLER005': return "O parâmetro <b>'$data[0]'</b>, definido no método <b>'$data[1]'</b>, deve ser um <b>array</b> e não um(a) <b>'$data[2]'</b>";

            case 'VIEW001':       return "O renderizador <b>'view'</b> não conseguiu localizar o arquivo <b>'$data[0]'</b>.";
            case 'VIEW002':       return "A rota <b>'$data[0]'</b> requer uma view configurada no arquivo <b>'config/routes.php'</b>.";

            case 'FILE001':       return "A extensão <b>'.$data[0]'</b> e seu respectivo Content-type não foi definido nas configurações.";
            case 'FILE002':       return "O caminho do arquivo não foi definido.";
            case 'FILE003':       return "Não foi definida a extensão do arquivo.";
            case 'FILE004':       return "O arquivo <b>'$data[0]'</b> não existe.";
            case 'FILE005':       return "O método '<b>filePath()</b>' só pode ser usado junto ao renderizador 'file'";

            case 'DATETIME001':   return "A data <b>'$data[0]'</b> é inválida ou não está de acordo com o formato <b>'$data[1]'</b>.";
            case 'DATETIME002':   return "A data informada não é um objeto do tipo DateTime";
            case 'DATETIME003':   return "A data limite <b>'$data[0]'</b> é inválida ou não está de acordo com o formato <b>'$data[1]'</b>.";
            case 'DATETIME004':   return "A data limite informada não é um objeto do tipo DateTime.";
            case 'DATETIME005':   return "O método <b>Datetime::create()</b> retornou os seguintes erros:<br>$data[0]";

            case 'VALIDATION001': return "O método <b>'validate()'</b> precisa ser iniciado antes dos métodos validadores.";

            case 'DATABASE001':   return "O método <b>'connect()'</b> precisa ser iniciado antes dos métodos de consulta.";
            case 'DATABASE002':   return "A querystring <b>'$data[0]'</b> é inválida.";
            case 'DATABASE003':   return "Não existe nenhum resultado armazenado na consulta padrão.";
            case 'DATABASE004':   return "Não existe nenhum rótulo <b>'$data[0]'</b> definido em uma consulta.";
            case 'DATABASE005':   return "É necessário definir o nome do arquivo de backup.";

            case 'NUMBER001':     return "O tipo <b>'$data[0]'</b> não é um tipo de número válido. Os tipos válidos que podem ser informados são $data[1].";

            case 'AUTH001':       return "O nome <b>'$data[0]'</b> é um nome de campo de sessão reservado pelo microframework.";

            case 'DEFAULT':
            default:              return false;
        }
    }
}
