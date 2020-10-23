<?php
/**
 * - Authentication.php -
 * 
 * Classe responsável por verificar e armazenar informações de autenticação de usuário.
 * 
 * O padrão do microframework é o de sempre verificar quaisquer áreas ou páginas que tiverem uma
 * auth configurada no arquivo config/routes.php. Se a auth não possuir a chave configurada,
 * então o usuário é redirecionado.
 * 
 * Uma autorização é formada de dois campos padrão: o token, que armazena uma chave aleatória de
 * 64 caracteres e o ip do usuário.
 * 
 * Além destes campos é possível armazenar campos adicionais, visando armazenar dados quaisquer.
 * Pessoalmente não acho interessante armazenar dados demais em sessões, apenas o que for realmente
 * importante de ser lembrado, como id do usuário, ou nome de exibição, por exemplo. De qualquer
 * forma, informações completas de credenciais o ideal é armazenar em banco de dados.
 * 
 * Uma autenticação, quando realizada com sucesso, requer uma tag de identificação. Esta tag serve
 * apenas como forma de identificar a sessão nas áreas que exigem a autenticação com aquela tag.
 * 
 * Por exemplo, suponha que o site tenha dois locais de autenticação: clientes e administradores.
 * 
 * Na área de clientes, a tag que será verificada pode ser armazenada com o nome 'clientes'. Já
 * na área de administração, a tag pode ser outra, com o nome de 'administradores', por exemplo.
 * 
 * Estas tags são armazenadas como chaves da array $_SESSION e cada uma conterá um token e um ip
 * do usuário armazenados. Não significa que ambos serão usados, apenas significa que uma área vai
 * se utilizar de uma tag, enquanto outra se utiliza de outra tag.
 * 
 * Exemplo de uso da classe:
 * 
 *     Authentication::setTag('clientes')
 *                   ::addField('user_id', $userId)
 *                   ::start();
 * 
 * O exemplo acima é usado quando o usuário realiza um login bem sucedido. A tag da autenticação
 * é 'clientes'. Esta tag não deve ser diferente para cada usuário, ela apenas representa uma
 * área. Ou seja, em todas as áreas que possuírem a tag 'clientes', este usuário estará autenticado
 * e poderá obter acesso.
 * 
 * No arquivo config/routes.php esta tag é configurada usando o parâmetro authTag para uma área
 * ou página. Se esta tag não estiver ativa na sessão, então há duas possibilidades:
 * 
 * 1. O usuário é redirecionado para outra página. Esta configuração é feita através do parâmetro
 * authFailUrl, cujo valor é o local para onde o usuário deverá ser redirecionado. Pode-se utilizar
 * um atalho de redirecionamento ou uma URL (absoluta ou relativa). O parâmetro authFailUrl deve
 * ser configurado no mesmo local em que a authTag foi definida.
 * 
 * 2. O usuário não é redirecionado, mas o renderizador não recebe nenhum dado da controller. Os
 * únicos dados recebido são 'status = deniedAuth' e 'message = deniedAuth'. Isso permite fazer
 * com que uma view, por exemplo, exiba uma mensagem alertando da impossibilidade de se exibir a
 * página devido a não autenticação.
 * 
 * Exemplo de validação:
 * 
 *     $check = Authentication::validate('clientes');
 * 
 *     if($check){
 *         // Usuário está autenticado.
 *     } else {
 *         // Usuário não está autenticado.
 *     }
 */
namespace galastri\core;

class Authentication
{
    private static $authTag;
    
    /** Classe que trabalha sob o padrão Singleton, por isso, não poderá ser instanciada. */
    private function __construct(){}
    
    /**
     * Método que define a tag de uma área de autenticação quando um login é bem sucedido.
     * 
     * @param string $authTag          Nome da tag.
     */
    public static function setTag($authTag)
    {
        session_start();
        self::$authTag = $authTag;
        return __CLASS__;
    }
    
    /**
     * Método que adiciona campos adicionais para armazenamento referente a autenticação. Pode-se
     * armazenar dados do usuário, como seu ID, por exemplo, de forma que se possa verificar no
     * banco de dados os dados do usuário. Pode-se armazenar também seu nome de exibição ou quaisquer
     * dados que se deseje. É interessante não adicionar campos demais, apenas o que for necessário
     * para um melhor funcionamento da autenticação.
     * 
     * Os campos token e ip são reservados pelo microframework, portanto, tais nomes não podem
     * ser usados.
     * 
     * @param string $field            Nome do campo que irá armazenar um valor. Este campo nada
     *                                 mais é do que uma chave de uma array.
     * 
     * @param mixed $value             Valor do campo.
     */
    public static function addField($field, $value)
    {
        if($field === 'token' or $field === 'ip'){
            Debug::error('AUTH001', $field)::print();
        } else {
            $authTag = self::$authTag;

            if(!isset($_SESSION[$authTag][$field])){
                $_SESSION[$authTag][$field] = $value;
            }
        }
        return __CLASS__;
    }
    
    /**
     * Método que faz o armazenamento dos dados de autenticação. Por padrão, é criado um campo
     * token, que armazena um código randômico de 64 caracteres e que é armazenado tanto na
     * $_SESSION quanto em um cookie. Este token pode ser usado para autenticação automática, como
     * quando o usuário habilita campos como 'Lembrar no próximo login', por exemplo.
     * 
     * @param string $authTag          Nome da tag que terá os dados armazenados.
     */
    public static function start($authTag = false)
    {
        $authTag = $authTag === false ? self::$authTag : $authTag;

        $_SESSION[$authTag]['token'] = base64_encode(random_bytes(48));
        $_SESSION[$authTag]['ip'] = $_SERVER['REMOTE_ADDR'];
        setcookie($authTag, $_SESSION[$authTag]['token'], time()+(int)GALASTRI['authentication']['cookieExpire'], '/');
        session_regenerate_id();
        session_write_close();
    }
    
    /**
     * Método que atualiza todos os dados da tag de autenticação (token, id da sessão e IP do
     * usuário).
     * 
     * @param string $authTag          Nome da tag que terá os dados atualizados.
     */
    public static function update($authTag)
    {
        if(self::check($authTag)){
            self::start($authTag);
        }
        return false;
    }
    
    /**
     * Método que remove uma tag de autenticação da sessão do usuário, fazendo com o que o usuário
     * seja deslogado apenas das áreas que utilizem aquela tag.
     * 
     * @param string $authTag          Nome da tag que terá os dados removidos.
     */
    public static function unset($authTag)
    {
        session_start();
        if(self::check($authTag)){
            unset($_SESSION[$authTag]);
            setcookie($authTag, null, time() - 3600, '/');
            unset($_COOKIE[$authTag]);
            return true;
        }
        return false;
    }
    
    /**
     * Método que destrói toda a sessão, fazendo com que o usuário seja deslogado de todas as áreas
     * que requerer autenticação.
     */
    public static function destroy()
    {
        session_start();
        foreach($_SESSION as $key => $value){
            setcookie($key, null, time() - 3600, '/');
            unset($_COOKIE[$key]);
        }
        session_unset();
        session_destroy();
    }
    
    /**
     * Método que resgata os dados da sessão do usuário.
     * 
     * @param string $authTag          Nome da tag que terá os dados recuperados.
     */
    public static function getData($authTag)
    {
        session_start();
        if(self::check($authTag)){
            $session = $_SESSION[$authTag];
            session_write_close();
            return $session;
        }
        session_write_close();
        return false;
    }
    
    /**
     * Método que valida se a autenticação existe e se ela é válida.
     * 
     * @param string $authTag          Nome da tag que terá os dados validados.
     * 
     * @param bool $ipCheck            Habilita (true) ou desabilita (false) a verificação de que
     *                                 o IP do usuário ainda bate com o IP armazenado. Trata-se
     *                                 de uma verificação adicional.
     *                                         
     *                                 Por padrão, o IP não é verificado. É importante levar em
     *                                 conta que muitas vezes esta verificação de IP nem sempre
     *                                 é precisa. Isso por que o IP do usuário pode mudar devido
     *                                 a uma simples alteração no provedor do usuário, o que pode
     *                                 forçá-lo a ser deslogado, por exemplo. Além disso, usuários
     *                                 conectados em roteadores podem ter seus IPs compartilhados,
     *                                 o que faz com que usuários de uma mesma rede local possam
     *                                 ter o mesmo IP.
     *                                         
     *                                 Por isso não é inesperado que uma autenticação de um
     *                                 usuário seja considerada inválida devido a estes fatores.
     */
    public static function validate($authTag, $ipCheck = false)
    {
        session_start();
        if(self::check($authTag)){
            if($_SESSION[$authTag]['token'] === $_COOKIE[$authTag]){
                if($ipCheck){
                    if($_SESSION[$authTag]['ip'] === $_SERVER['REMOTE_ADDR']){
                        session_write_close();
                        return true;
                    } else {
                        session_write_close();
                        return false;
                    }
                } else {
                    session_write_close();
                    return true;
                }
            }
        }
        session_write_close();
        return false;
    }
    
    /**
     * Método que faz uma checagem comparação entre o token da sessão e o token do cookie.
     * 
     * @param string $authTag          Nome da tag que será verificada.
     */
    public static function tokenCompare($authTag)
    {
        if(self::check($authTag)){
            if($_SESSION[$authTag]['token'] === $_COOKIE[$authTag]){
                return true;
            }
        }
        return false;
    }

    /**
     * Método que faz uma checagem simples, verificando se o token existe. Esta não é uma checagem
     * completa, trata-se apenas de um método interno para verificar a existência da chave token.
     * A checagem completa é feita pelo método validate().
     * 
     * @param string $authTag          Nome da tag que será verificada.
     */
    private static function check($authTag)
    {
        if(session_status() === PHP_SESSION_NONE){
            return false;
        }

        if(!isset($_SESSION[$authTag]['token'])){
            return false;
        }
        return true;
    }
}
