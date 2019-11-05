<?php
/**
 * - Database.php -
 * 
 * Classe que realiza a conexão com banco de dados e executa consultas SQL. A conexão se utiliza
 * da extensão PDO.
 * 
 * Esta classe possui métodos que permitem encadeamento de forma a não ser necessário especificar
 * o objeto da instância a cada execução.
 * 
 * ----
 * Exemplo de uso:
 *         
 *     $database = new Database;
 *     $database->connect();
 *
 *     $database->begin();
 *
 *     $database
 *         ->query("SELECT * FROM autor WHERE id=:id", "lista_autores")
 *             ->bind(":id", 2)
 *            
 *          ->query("INSERT INTO autor(nome,email,formacao,foto) VALUES(:nome,:email,:formacao,:foto)", "insere_livros")
 *             ->bindArray([
 *                 ":nome"        => "André Galastri",
 *                 ":email"    => "contato@andregalastri.com.br",
 *                 ":formacao"    => "Sistemas para Internet",
 *                 ":foto"        => "../fotos/andre.jpg",
 *             ])
 * 
 *          ->query("SELECT * FROM livro WHERE edicao=:edicao", "lista_livros")
 *             ->bind(":edicao", 1)
 *             ->pagination(1, 10)
 *             
 *         ->submit();
 *
 *     $database->commit();
 *
 *     vdump($database->getResult("lista_autores"));
 *     vdump($database->getPagination("lista_livros"));
 */
namespace galastri\core;

class Database
{
    private $conn;
    private $pdo;
    private $result;
    private $pagination;

    /**
     * Quando a instância da classe é criada, alguns atributos são configurados para terem valores
     * padrão.
     */
    public function __construct()
    {
        $this->conn         = new \StdClass;
        $this->conn->status = false;
        
        $this->result       = [];
        $this->pagination   = [];
        
        $this->query        = new \StdClass();
        $this->query->sql   = null;
        $this->query->label = null;
        
        $this->setDefaultConfig();
    }
    
    /**
     * Métodos de configuração. Cada parâmetro define uma configuração.
     */
    public function setActive($active)     { $this->conn->active   = $active;   return $this; }
    public function setDriver($driver)     { $this->conn->driver   = $driver;   return $this; }
    public function setHost($host)         { $this->conn->host     = $host;     return $this; }
    public function setDatabase($database) { $this->conn->database = $database; return $this; }
    public function setUser($user)         { $this->conn->user     = $user;     return $this; }
    public function setPassword($password) { $this->conn->password = $password; return $this; }
    public function setOptions($options)   { $this->conn->options  = $options;  return $this; }
    
    /**
     * Método que define as configurações padrão para conexão.
     */
    public function setDefaultConfig()
    {
        $this->conn->active   = GALASTRI["database"]["active"];
        $this->conn->driver   = GALASTRI["database"]["driver"];
        $this->conn->host     = GALASTRI["database"]["host"];
        $this->conn->database = GALASTRI["database"]["database"];
        $this->conn->user     = GALASTRI["database"]["user"];
        $this->conn->password = GALASTRI["database"]["password"];
        $this->conn->options  = GALASTRI["database"]["options"];

        return $this;
    }

    /**
     * Método que faz a conexão com o banco de dados baseado nas configurações especificadas tanto
     * no arquivo config/database.php ou caso novas configurações sejam definidas diretamente pelos
     * métodos de configuração.
     */
    public function connect()
    {
        $active   = $this->conn->active;
        $driver   = $this->conn->driver;
        $host     = $this->conn->host;
        $database = $this->conn->database;
        $user     = $this->conn->user;
        $password = $this->conn->password;
        $options  = $this->conn->options;

        if($active){
            try {
                $this->pdo = new \PDO("$driver:host=$host;dbname=$database", $user, $password, $options);
                $this->conn->status = true;
            } catch (\PDOException $e) {}
        }
        return $this;
    }
    
    /**
     * Métodos de transação. Permite a definição de onde uma transação será iniciada e, caso algum
     * erro ocorra durante as consultas SQL, que todas as transações concluídas sejam desfeitas.
     */
    public function begin()  { if($this->conn->active) $this->pdo->beginTransaction(); }
    public function cancel() { if($this->conn->active) $this->pdo->rollBack(); }
    public function commit() { if($this->conn->active) $this->pdo->commit(); }
    
    /**
     * Método que verifica se existe um elo da corrente ativo antes de executar um novo teste.
     * Caso positivo, a corrente deverá ser resolvida antes da criação de uma nova.
     */
    private    function beforeTest()
    {
        if($this->conn->status === false){
            Debug::error("DATABASE001");
        } else {
            if($this->conn->active){
                if(Chain::hasLinks()){
                    $this->submit();
                }    
            }
        }
    }
    
    /**
     * Método que faz as consultas SQL. Neste microframework optou-se pela execução das consultas
     * através da digitação completa das querystrings. Ou seja, não existem atalhos prontos para
     * se realizar as consultas, todas as consultas precisam ser escritas em linguagem SQL.
     * 
     * O motivo disso é flexibilidade. As consultas SQL podem possuir sintaxes diferentes, inclusive
     * caso se utilize SGBDs diferentes do MySQL.
     * 
     * Todas as consultas, se utilizam da extensão PDO do PHP, o que permite o uso de algumas
     * especificações que tornam a consulta mais segura.
     * 
     * A sintaxe para consulta é
     * 
     *     $database = new Database;
     *     $database->query(<query SQL>, <rótulo>);
     * 
     * A query SQL é a consulta em si. O rótulo é um rótulo para armazenamento do resultado desta
     * consulta. Isso permite que os resultados de uma consulta sejam armazenados em uma array e
     * podem ser recuperados a qualquer momento através da chamada do rótulo. Quando um rótulo não
     * é especificado, a consulta fica armazenada em um rótulo padrão chamado galastriDefaultQuery
     * e cada consulta sem rótulo que for efetuada irá sobrescrever o resultado anterior.
     * 
     * A consulta pode se utilizar de que se utilizem de referências, que tornam os comandos SQL
     * mais seguros. Por exemplo:
     * 
     * Ao invés de utilizar algo como SELECT * FROM tabela WHERE id = $id
     * Utiliza-se SELECT * FROM tabela where id = :id
     * 
     * :id não se trata de uma variável ou constante PHP, por isso, precisa precisa ser referenciada
     * através do método bind() ou bindArray(), de forma a permitir que a consulta seja feita
     * corretamente.
     * 
     * O motivo de se usar referências é que isto é muito mais seguro do que usar a variável
     * diretamente na consulta, pois toda a consulta é interpretada como uma string, o que faz
     * com que ameaças como SQL Injection sejam suprimidas.
     * 
     * @param string $queryString      Comandos SQL para realização da consulta.
     * 
     * @param string $label            Rótulo da consulta para ser armazenado individualmente.
     */
    public function query($queryString, $label = "galastriDefaultQuery")
    {
        $this->beforeTest();

        /** Este método cria um elo em uma corrente, o que permite que sejam concatenados outros
         * métodos junto a ela. */
        Chain::create(
            "query",
            [
                "name"        => "query",
                "queryString" => $queryString,
                "label"       => $label,
                "attach"      => true,
            ],
            (
                function($chainData, $data){
                    if($this->conn->active){
                        Debug::trace(debug_backtrace()[0]);

                        $this->query->label = $data["label"] === 0 ? "defaultQuery" : $data["label"];

                        $bind      = [];
                        $bindArray = [];

                        /** Armazena os dados da querystring principal e de paginação. A querystring
                         * é dividida entre duas variáveis pois a paginação realiza uma consulta
                         * um pouco diferente da consulta principal. A consulta de paginação
                         * leva a conta todos os resultados. Já a consulta principal leva em conta
                         * apenas o que se deseja exibir. */
                        $mainQuery  = trim($data["queryString"]);
                        $mainQuery  = preg_replace("/[\t\n]+/u", " ", $mainQuery);
                        $resultLog  = [];

                        $pagQuery   = $data["queryString"];
                        $pagStatus  = false;
                        $pagLog     = [];

                        $queryType  = lower(explode(" ", $mainQuery)[0]);
                        
                        /** Verifica se o termo LIMIT foi usado na querystring. Caso tenha sido,
                         * então a paginação não será executada. Isso ocorre pois a paginação
                         * necessita de uma querystring livre de limitações, já que a paginação
                         * se utiliza, em suma, de um limitador próprio. */
                        preg_match('/limit/', lower($mainQuery), $limitMatch);
                        $limitMatch = empty($limitMatch[0]) ? false : $limitMatch[0];

                        foreach($chainData as $parameter){
                            switch($parameter["name"]){
                                /** Execução da Query. Caso o LIMIT não esteja definido na própria
                                 * querystring e caso o status de paginação seja verdadeiro, então
                                 * é inserido, na consulta principal, um LIMIT baseado nas
                                 * configurações da paginação.
                                 * 
                                 * A consulta é preparada e todos os valores inseridos nos métodos
                                 * bind() ou bindArray() são unificados na variável bindArray().
                                 * 
                                 * Cada um dos valores informados nos binds são verificados, já
                                 * que valores do tipo null precisam ser explicitamente declarados
                                 * com o parâmetro PDO::PARAM_null.*/
                                case "query":

                                    if(!$limitMatch and $pagStatus){
                                        $mainQuery .= $pagPerPage ? " LIMIT ".(($pagPage-1)*$pagPerPage).", $pagPerPage" : "";
                                    }
                                    
                                    $sql       = $this->pdo->prepare($mainQuery);
                                    $bindArray = array_merge($bind, $bindArray);

                                    foreach($bindArray as $key => &$value){
                                        if($value === null){
                                            $sql->bindParam($key, $value, \PDO::PARAM_null);
                                            $value = null;
                                        }
                                    }

                                    /** A consulta é realizada. É verificado se a consulta é do
                                     * tipo SELECT. Caso seja e caso a quantidade de resultados
                                     * encontrados seja maior do que zero, então isso indica que
                                     * tais resultados precisam ser organizados em uma array
                                     * associativa.
                                     * 
                                     * A variável/array $resultLog["found"] é definida como true,
                                     * para que seja fácil identificar quando a consulta encontra
                                     * ou não resultados.
                                     * 
                                     * A variável/array $resultLog["data"] armazena todos os
                                     * resultados encontrados em uma array associativa em que o
                                     * nome do campo da tabela é a chave o seu valor é o valor da
                                     * chave.*/
                                    if($sql->execute($bindArray)) {
                                        switch($queryType){
                                            case "select":
                                                if($sql->rowCount() > 0){
                                                    $resultLog["found"] = true;

                                                    while($found = $sql->fetch(\PDO::FETCH_ASSOC)){
                                                        $resultLog["data"][] = $found;
                                                    }

                                                    /** Não existindo LIMIT na consulta e a paginação
                                                     * estando ativa, uma nova consulta é realizada,
                                                     * levando em conta apenas a querystring sem
                                                     * limitações.
                                                     * 
                                                     * Desta forma é possível recuperar a quantidade
                                                     * total de resultados encontrado, calcular a
                                                     * quantidade de páginas que a consulta possui,
                                                     * a página atual e a quantidade de resultados
                                                     * por página.*/
                                                    if(!$limitMatch and $pagStatus){
                                                        $pagLog["status"] = true;

                                                        $sql = $this->pdo->prepare(trim($pagQuery));
                                                        $sql->execute($bindArray);

                                                        $pagLog["entries"] = $sql->rowCount();
                                                        $pagLog["pages"]   = (int)ceil($sql->rowCount()/$pagPerPage);
                                                        $pagLog["page"]    = $pagPage;
                                                        $pagLog["perPage"] = $pagPerPage;
                                                    }
                                                }
                                                break;

                                            /** Caso a consulta seja do tipo INSERT, então é
                                             * armazenado o último ID inserido no banco de dados.*/
                                            case "insert":
                                                $resultLog["lastId"] = $this->pdo->lastInsertId();
                                                break;
                                        }
                                        $resultLog["affectedRows"] = $sql->rowCount();
                                        $resultLog["queryType"]    = $queryType;

                                        $this->setPagination($pagLog);
                                        $this->setResult($resultLog);
                                    } else {
                                        Debug::error("DATABASE002", $mainQuery)->print();
                                    }
                                    break;
                                    
                                /** Os casos abaixo fazem o armazenamento dos parâmetros informados
                                 * nos métodos que estiverem encadeados na corrente. Cada elo
                                 * resolvido tem funções específicas.
                                 * 
                                 * Os métodos bind() e bindArray() criam elos na corrente cujos
                                 * dados são armazenados e usados no método principal query()
                                 * com os argumentos de referência na querystring.
                                 * 
                                 * O método pagination() cria um elo na corrente que cujos dados
                                 * são armazenados e usados no método principal query() a respeito
                                 * de paginação. */
                                case "bind":
                                    $field        = $parameter["field"];
                                    $value        = $parameter["value"];

                                    $bind[$field] = $value;
                                    break;

                                case "bindArray":
                                    $bindArray    = $parameter["fields"];
                                    break;

                                case "pagination":
                                    $pagStatus    = true;
                                    $pagPage      = $parameter["page"];
                                    $pagPerPage   = $parameter["perPage"];
                                    break;
                            }
                        }
                        return Chain::resolve($chainData, $data);
                    }
                }
            )
        );
        return $this;
    }
    
    /**
     * Método que cria um elo na corrente que armazena argumentos de referência e seu respectivo
     * valor real. Os argumentos de referência podem ser nomeados iniciando-se com dois pontos :
     * ou sem nomes, usando apenas pontos de interrogação ?. Neste caso, o bind irá requerer o
     * uso de números na ordem em que aparecem na querystring.
     * 
     * Exemplos de uso:
     * 
     *     $database->query(SELECT * FROM tabela WHERE autor = :autor AND editora = :editora)
     *              ->bind(":autor", $_POST["autor"])
     *              ->bind(":editora", $_POST["editora"])
     *              ->submit();
     * 
     *     $database->query(SELECT * FROM tabela WHERE autor = ? AND editora = ?)
     *              ->bind(1, $_POST["autor"])
     *              ->bind(2, $_POST["editora"])
     *              ->submit();
     * 
     * @param int|string $field        Armazena o nome da referência ou o número da ocorrência da
     *                                 querystring.
     * 
     * @param mixed $value             Valor real a qual o argumento de referência se refere e que
     *                                 será o dado real usado na consulta.
     */
    public function bind($field, $value)
    {
        if($this->conn->active){
            Chain::create(
                "bind",
                [
                    "name"         => "bind",
                    "field"        => $field,
                    "value"        => $value,
                    "attach"    => true,
                ],
                (function($chainData, $data){ return Chain::resolve($chainData, $data); })
            );
        }
        return $this;
    }
    
    
    /**
     * Método que faz a mesma coisa que o método bind(), com a diferença de que ao invés de receber
     * uma array com vários argumentos de referência e seus respectivos valores. O bind() aceita
     * apenas um argumento de referência e um valor. O bindArray() recebe uma array com vários.
     * 
     * Importante: este método tem efeito uma única vez, ou seja, usar vários bindArray() fará
     * apenas com que seus valores se sobreponham, não adiantando utilizá-lo várias vezes.
     * 
     * Exemplo de uso:
     * 
     *     $dados = array(
     *          ":autor" => $_POST["autor"],
     *          ":editora" => $_POST["editora"],
     *     );
     * 
     *     $database->query(SELECT * FROM tabela WHERE autor = :autor AND editora = :editora)
     *              ->bindArray($dados)
     *              ->submit();
     * 
     * @param array $fields            Armazena os nomes de referência ou números de ocorrência
     *                                 da querystring e seus respectivos valores reais.
     */
    public function bindArray(array $fields)
    {
        if($this->conn->active){
            Chain::create(
                "bindArray",
                [
                    "name"   => "bindArray",
                    "fields" => $fields,
                    "attach" => true,
                ],
                (function($chainData, $data){ return Chain::resolve($chainData, $data); })
            );
        }
        return $this;
    }
    
    /**
     * Método que cria um elo na corrente contendo dados para criar paginação.
     * 
     * Importante: este método tem efeito uma única vez, ou seja, usar vários pagination() fará
     * apenas com que seus valores se sobreponham, não adiantando utilizá-lo várias vezes.
     * 
     * Exemplo de uso:
     * 
     *     $database->query(SELECT * FROM tabela)
     *              ->pagination(1, 10)
     *              ->submit();
     * 
     * @param int $page                Armazena a página atual. Nada mais é do que um offset, ou
     *                                 seja, se a paginação exibe 10 resultados por página, então
     *                                 a página 1 irá exibir os resultados de 1 a 10, a página 2
     *                                 irá exibir os resultados de 11 a 20, e assim por diante.
     * 
     * @param int $perPage             Armazena quantos resultados serão mostrados por página.
     *                                 Ou seja, trata-se da quantidade máxima de resultados que
     *                                 serão retornados na consulta.
     * 
     */
    public function pagination($page, $perPage)
    {
        if($this->conn->active){
            Chain::create(
                "pagination",
                [
                    "name"    => "pagination",
                    "page"    => $page,
                    "perPage" => $perPage,
                    "attach"  => true,
                ],
                (function($chainData, $data){ return Chain::resolve($chainData, $data); })
            );
        }
        return $this;
    }
    
    /**
     * Método que faz a resolução dos elos da corrente. Cada um dos elos criados não é executado,
     * apenas armazenado. Por isso, o método submit() é necessário, pois é ele que inicia a
     * resolução da corrente executando cada um dos elos armazenados.
     */
    public function submit()
    {
        if($this->conn->active){
            return Chain::resolve();
        }
    }
    
    /**
     * Método que armazena os resultados da consulta em um objeto StdClass que será acessível
     * através do método getResult().
     * 
     * @param array $result            Armazena a array que retorna todos os dados encontrados
     *                                 numa consulta SQL, caso a consulta seja um SELECT, ou
     *                                 dados como o último id inserido na tabela, caso a consulta
     *                                 seja um INSERT, ou ainda a quantidade de resultados
     *                                 afetados, em qualquer tipo de consulta.
     */
    private function setResult($result)
    {
        $label                = $this->query->label;
        $this->result[$label] = new \StdClass;
        
        $this->result[$label]->label        = $label;
        $this->result[$label]->queryType    = keyExists("queryType", $result, false);
        $this->result[$label]->affectedRows = keyExists("affectedRows", $result, false);
        $this->result[$label]->found        = keyExists("found", $result, false);
        $this->result[$label]->data         = keyExists("data", $result, null);
        $this->result[$label]->lastId       = keyExists("lastId", $result, null);
    }
    
    /**
     * Método que armazena os resultados de paginação em um objeto StdClass que será acessível
     * através do método getPagination().
     * 
     * @param array $result            Armazena a array que retorna os dados de paginação
     *                                 retornados pela consulta de paginação.
     */
    private function setPagination($result)
    {
        $label                    = $this->query->label;
        $this->pagination[$label] = new \StdClass;
        
        $this->pagination[$label]->label   = $label;
        $this->pagination[$label]->status  = keyExists("status", $result, false);
        $this->pagination[$label]->entries = keyExists("entries", $result, null);
        $this->pagination[$label]->pages   = keyExists("pages", $result, null);
        $this->pagination[$label]->page    = keyExists("page", $result, null);
        $this->pagination[$label]->perPage = keyExists("perPage", $result, null);
    }
    
    /**
     * Recupera os resultados de consulta.
     * 
     * @param string|null $label       Informa o rótulo de consulta utilizado no método query()
     *                                 que armazena o resultado que se quer recuperar. Quando
     *                                 não informado, utiliza o rótulo padrão.
     */
    public function getResult($label = "galastriDefaultQuery")
    {
        return $this->propertyResults($label, "result");
    }
    
    /**
     * Recupera os resultados de paginação.
     * 
     * @param string|null $label       Informa o rótulo de consulta utilizado no método query()
     *                                 que armazena o resultado que se quer recuperar. Quando
     *                                 não informado, utiliza o rótulo padrão.
     */
    public function getPagination($label = "galastriDefaultQuery")
    {
        return $this->propertyResults($label, "pagination");
    }
    
    /**
     * Este método é chamado quando algum dos métodos getResult() ou getPagination() é executado.
     * Como ambos executam comandos idênticos, optou-se por definir um bloco de comandos único que
     * pode ser reaproveitado por qualquer um dos métodos.
     * 
     * @param string $label            Informa o rótulo de consulta utilizado no método query().
     * 
     * @param string $property         Informa o nome da propriedade que representa qual o
     *                                 método está sendo executado.
     */
    private function propertyResults($label, $property)
    {
        Debug::trace(debug_backtrace()[0]);
        
        $keys = array_keys($this->$property);
        
        if(isset($this->$property[$label])){
            return $this->$property[$label];
        } else {
            if($label === "galastriDefaultQuery"){
                Debug::error("DATABASE003", $label)->print();

            } else {
                Debug::error("DATABASE004", $label)->print();
            }
        }
    }
    
    /**
     * Método que limpa os dados armazenados em um rótulo.
     * 
     * @param string $label            Informa o rótulo de consulta utilizado no método query().
     */
    public function clearResult($label)
    {
        $this->result[$label]     = null;
        $this->pagination[$label] = null;
    }
    
    /**
     * Método que elimina um rótulo e, consequentemente, todos seus dados.
     * 
     * @param string $label            Informa o rótulo de consulta utilizado no método query().
     */
    public function removeResult($label)
    {
        $this->clearResult($label);

        if($label != "galastriDefaultQuery"){
            unset($this->result[$label]);
            unset($this->pagination[$label]);
        }
    }
}
