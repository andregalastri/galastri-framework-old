<?php
/**
 * - Permission.php -
 * 
 * Classe que trata o conceito de permissão de acesso a um recurso. Este é um conceito bastante
 * amplo, mas que aqui decidiu-se tratar pela abordagem mais simplificada.
 * 
 * Em geral, quando um site ou sistema tem partes de acesso restrito a determinados usuários, é
 * necessário verificar se o usuário que está tentando fazer o acesso possui a permissão de acesso.
 * 
 * A abordagem é simples: Em qualquer parte do código que seja necessário verificar se um usuário
 * tem permissão de acesso basta fazer a definição e validação. Caso a validação falhe, ou seja,
 * é constatado que o usuário não possui acesso, uma Exception é lançada. Por isso, é importante
 * utilizar esta classe dentro das tags try/catch.
 * 
 * 
 * - DEFINIÇÃO DAS PERMISSÕES- 
 * 
 * A definição nada mais é do que definir um ou mais valores que definem a(s) permissão(ões).
 * Por exemplo, se o recurso requer que apenas usuários do grupo 1 consigam acessá-lo:
 * 
 *  | Permission::allow(1);    // permite que usuários do grupo 1 acessem o recurso.
 * 
 * Pode-se definir vários allow() num mesmo recurso a fim de permitir que vários grupos possam
 * acessar um recurso.
 * 
 *  | Permission
 *  |    ::allow(1)            // permite que usuários do grupo 1 acessem o recurso.
 *  |    ::allow(2);           // e também permite que usuários do grupo 2 acessem o recurso.
 * 
 *  | Permission::allow(1, 2)  // permite que usuários do grupo 1 ou 2 acesse o recurso.
 * 
 *  | $list = [1, 2];          // variável contendo uma array com a lista de permissões.
 *  | Permission::allow($list) // permite que qualquer usuário do grupo de $list acesse o recurso.
 * 
 * 
 * - MENSAGENS DE ERRO - 
 * 
 * Uma vez definidas as permissões, é possível definir uma mensagem de erro personalizada que
 * será enviada pela exceção em caso de falha. Para isto, basta definir:
 * 
 *  | Permission::onError('Mensagem de erro').
 * 
 * Caso a mensagem não seja definida, a mensagem padrão definida no arquivo no parâmetro de
 * configuração ['permissions']['failMessage] é utilizada.
 * 
 * - EXECUÇÃO DA VALIDAÇÃO -
 * 
 * Estando tudo configurado, é executada a validação. Neste ponto, é informado qual a permissão
 * que o usuário possui. Por exemplo, foi configurado que usuários do grupo 1 ou 2 podem acessar
 * uma área de um sistema. Feito isso, é necessário recuperar os dados do usuário que estiver
 * acessando e informar qual o grupo que ele faz parte.
 * 
 *  | $grupoDoUsuario = $_SESSION['grupoDoUsuario'];
 *  |
 *  | Permission
 *  |    ::allow(1, 2)
 *  |    ::onError('Você não pode acessar esta área do sistema.')
 *  |    ::validate($grupoDoUsuario);
 * 
 * No exemplo acima, suponha que a global $_SESSION armazene o grupo em que o usuário logado faz
 * parte. Este é o dado que será validado.
 * 
 * Caso o dado seja válido, nenhum erro é executado. Caso o dado seja inválido, uma Exception é
 * lançada contendo a mensagem de erro.
 * 
 * O valor da validação também pode ser uma lista. Suponha que ao invés de grupos, o usuário tenha
 * uma lista de permissões dos recursos que ele pode acessar. Neste caso, configura-se as permissões
 * que o usuário pode ter e informa-se a lista que este usuário tem de permissões.
 * 
 *  | $permissoesDoUsuario = $_SESSION['permissoesDoUsuario'];
 *  |
 *  | Permission
 *  |    ::allow('adicionar_cliente', 'editar_cliente')
 *  |    ::onError('Você não pode acessar este recurso do sistema.')
 *  |    ::validate($permissoesDoUsuario);
 * 
 * No exemplo acima, suponha que a global $_SESSION armazene a lista de permissões que o usuário
 * logado possua dentro do sistema. Esta lista é que será validada.
 * 
 * Caso o usuário tenha uma das permissões configuradas, nenhum erro é executado. Caso contrário,
 * uma Exception é lançada contendo a mensagem de erro.
 */
namespace galastri\core;

use \galastri\extensions\Exception;

class Permission
{
    private static $groups  = [];
    private static $allowed = [];
    private static $onError = GALASTRI['permission']['failMessage'];
    private static $exceptionTag = GALASTRI['permission']['exceptionTag'];

    /** Esta classe trabalha sob o padrão Singleton, por isso, não poderá ser instanciada. */
    private function __construct(){}

    /**
     * Método que define a lista de permissões, ou seja, quais os valores serão considerados
     * válidos.
     * 
     * @param mixed $tags              Lista contendo valores que serão considerados válidos.
     */
    public static function allow(...$tags)
    {
        foreach(flattenArray($tags) as $tagName)
            self::$allowed[] = $tagName;

        return __CLASS__;
    }

    /**
     * Método que remove um valor da lista de permissões.
     * 
     * @param mixed $tags              Lista contendo valores que serão removidos.
     */
    public static function remove(...$tags)
    {
        foreach(flattenArray($tags) as $tagName)
            unset(self::$allowed[$tagName]);

        return __CLASS__;
    }

    /**
     * Método que define uma mensagem de erro personalizada que será armazenada na Exception.
     * Opcionalmente também pode-se definir a tag da Exception.
     * 
     * @param string $message          String contendo a mensagem de erro.
     * 
     * @param string $exceptionTag     String contendo a tag usada na Exception.
     */
    public static function onError($message, $exceptionTag = false)
    {
        self::$onError = $message;
        if($exceptionTag)
            self::$exceptionTag = $exceptionTag;
        return __CLASS__;
    }

    /**
     * Método que faz a verificação se os valores configurados no método allow() correspondem a
     * as permissões que o usuário possui.
     * 
     * @param mixed $permissions       Lista contendo um ou mais permissões que o usuário possui.
     */
    public static function validate(...$permissions)
    {
        $allowed = self::$allowed;
        $permissions = flattenArray($permissions);

        foreach($allowed as $tagName){
            if(array_search($tagName, $permissions) !== false){
                return true;
            }
        }

        throw new Exception(self::$onError, self::$exceptionTag);
    }
}
