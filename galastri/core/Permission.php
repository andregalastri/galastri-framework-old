<?php
/**
 * - Permission.php -
 * 
 * Classe que trata o conceito de permissão de acesso a um recurso. Este é um conceito bastante
 * amplo, mas que aqui decidiu-se tratar pela abordagem mais simplificada.
 * 
 * Em geral, quando um site tem partes de acesso restrito a determinados usuários, é necessário
 * verificar se o usuário que está tentando fazer o acesso faz parte de um grupo permitido.
 * 
 * Para isso pode-se ter duas abordagens:
 * 
 * - Restrição a grupos: nesta abordagem, o usuário terá um ID do grupo a qual ele pertence (em
 *   geral armazenado junto com seus dados no banco de dados). A cada página restrita é definido
 *   quais são os grupos que podem acessar aquele recurso. Em seguida, faz-se um teste para
 *   verificar se o ID do grupo a qual o usuário pertence faz parte da lista de grupos permitidos.
 * 
 * - Restrição usuário a usuário: esta abordagem permite maior controle de restrição de acesso,
 *   mas também é mais complexa de ser implementada. Deve-se configurar um rótulo a cada página
 *   restrita. Armazena-se em banco de dados quais são os rótulos que cada usuário pode acessar.
 *   Em seguida, faz-se um teste para verificar se o ID do rótulo da página faz parte da lista de
 *   rótulos que o usuário possui permissão.
 * 
 * É possível fazer uso das duas abordagens ao mesmo tempo.
 * 
 * - Listas de permissão fixas -
 * No arquivo config/permission.php pode-se configurar uma lista fixa de grupos e recursos,
 * colocando como chave o nome do grupo ou recurso e como valor o seu ID. Aqui são inseridos TODOS
 * os grupos ou nomes de recursos que existem no site.
 * 
 * O framework precisa que a array contendo os nomes seja associativa, com nome do grupo e ID do
 * grupo ou recurso, pois é desta forma que a busca será feita.
 * 
 *     array(
 *         <nome do recurso ou grupo> => <id do grupo ou recurso>,
 *     );
 * 
 * Por exemplo:
 * 
 *     array(
 *         "grupoVendas"        => 1,
 *         "grupoGerentes"      => 2,
 *         "grupoAdministração" => 3,
 *     );
 * 
 * - Listas de permissão dinâmicas -
 * 
 * O problema do uso do arquivo config/permission.php é que existem casos em que a lista de grupos
 * e recursos permissões é dinâmica, ou seja, é modificada, ampliada ou reduzida a depender da
 * configuração do site. Para casos como este é necessário informar esta lista dinâmica diretamente
 * no método testador.
 * 
 * A lista dinâmica deve ser armazenada em uma array (assim como ocorre na lista fixa) tendo o seu
 * conteúdo armazenado dinamicamente ou até mesmo advindo de dados de um banco de dados.
 * 
 * Esta array deve seguir a premissa de conter o nome do recurso ou grupo na chave e seu respectivo
 * ID como valor. Por exemplo:
 * 
 *     $groupArray = [
 *         "grupoVendas"        => 1,
 *         "grupoGerentes"      => 2,
 *         "grupoAdministração" => 3,
 *         "grupoSuperUser"     => 4,
 *     ];
 * 
 * Mais informações nas explicações dos métodos checkGroup() e checkResource().
 * 
 * -----
 * Exemplo de uso de permissão por grupos:
 * 
 *     $userGroup = 2;
 * 
 *     $groupArray = [
 *         "vendedores"    => 1,
 *         "gerentes"      => 2,
 *         "proprietarios" => 3,
 *     ];
 * 
 *     $permission = new Permission();
 *        
 *     $result = $permission->addGroup("vendedores")
 *                          ->addGroup("gerentes")
 *                          ->checkGroup($userGroup, $groupArray);
 *     vdump($result);
 * 
 * -----
 * Exemplo de uso de permissão usuário a usuário:
 * 
 *     $userIdPermissions = [1, 2];
 * 
 *     $resourceArray = [
 *         "listar_venda"  => 1,
 *         "editar_venda"  => 2,
 *         "remover_venda" => 3,
 *     ];
 * 
 *     $permission = new Permission();
 *        
 *     $result = $permission->setResource("remover_venda")
 *                          ->checkResource($userIdPermissions, $resourceArray);
 *     vdump($result);
 */
namespace galastri\core;

class Permission {
    private $groups   = [];
    private $resource = NULL;
    private $result   = [];
    
    /**
     * Método que adiciona um nome de grupo que a página ou recurso permite acesso. Pode-se
     * utilizar este método várias vezes com o objetivo de formar uma lista.
     * 
     * @param string $groupName        Nome do grupo, igual como definido nas arrays que armazenam
     *                                 as listas de grupos do site.
     */
    public function addGroup($groupName){
        $this->groups[] = $groupName;
        return $this;
    }
    
    /**
     * Método que remove um nome de grupo que a página ou recurso permite acesso. Pode-se
     * utilizar este método várias vezes com o objetivo de remover vários grupos da lista.
     * 
     * @param string $groupName        Nome do grupo, igual como definido nas arrays que armazenam
     *                                 as listas de grupos do site.
     */
    public function removeGroup($groupName){
        unset($this->groups[$groupName]);
        return $this;
    }
    
    /**
     * Método que faz a verificação se o ID do grupo do usuário corresponde a um dos IDs que fazem
     * parte da lista de grupos existentes. Aqui é importante levar em conta que a lista de grupos
     * deve conter TODOS os nomes e IDs de grupos possíveis e não a lista de permissão.
     * 
     * A lista de permissões é formada pelos métodos addGroup() e removeGroup(). A lista de grupos
     * é, portanto, a que contém todos os grupos existentes.
     * 
     * Esta lista pode ser fixa, configurada no arquivo config/permission.php ou gerada dinamicamente.
     * Caso seja gerada dinamicamente, deve ser colocada em uma array em que as chaves armazenam
     * o nome do grupo e os valores armazenem o ID. Em seguida, esta array é informada no segundo
     * parâmetro do método que fará o teste.
     * 
     *     $groupArray = [
     *         "vendedores"    => 1,
     *         "gerentes"      => 2,
     *         "administrador" => 3,
     *     ];
     * 
     *     $userGroup = 3;
     *     ...
     *     ->checkGroup($userGroup, $groupArray);
     * 
     * @param int|string $userGroup    Em geral contém o ID do grupo a qual o usuário pertence.
     * 
     * @param array $groupTags         Array contendo todos os grupos existentes no sistema de
     *                                 permissões.
     */
    public function checkGroup($userGroup, $groupTags = GALASTRI["permission"]["groups"]){
        $groups = $this->groups;
        
        foreach($groups as $groupName){
            if($groupTags[$groupName] === $userGroup){
                return TRUE;
            }
        }
        return FALSE;
    }
    
    /**
     * Método que define um rótulo pra página ou recurso. Com isso é possível fazer um teste se
     * o usuário possui acesso ao recurso com este nome.
     * 
     * @param string $resourceName     Nome do recurso, igual como definido nas arrays que
     *                                 armazenam as listas de recursos do site.
     */
    public function setResource($resourceName){
        $this->resource = $resourceName;
        return $this;
    }

    /**
     * Método que faz a verificação se a lista de recursos que usuário possui acesso corresponde
     * ao recurso a qual ele está desejando acessar. Aqui é importante levar em conta que a lista
     * de recursos do site deve conter TODOS os nomes e IDs de recursos possíveis e não a lista de
     * permissão.
     * 
     * A lista de permissões é do usuário, ou seja, se ele possuir em sua lista de permissão um
     * ID que corresponda com o informado no recurso, ele terá acesso ao recurso. A lista de
     * recursos, portanto, é a que contém todos os recursos existentes.
     * 
     * Esta lista pode ser fixa, configurada no arquivo config/permission.php ou gerada dinamicamente.
     * Caso seja gerada dinamicamente, deve ser colocada em uma array em que as chaves armazenam
     * o nome do recurso e os valores armazenem o ID. Em seguida, esta array é informada no segundo
     * parâmetro do método que fará o teste.
     * 
     *     $resourceArray = [
     *         "listar_venda"     => 1,
     *         "editar_venda"    => 2,
     *         "remover_venda" => 3,
     *     ];
     * 
     *     $userIdPermissions = [1, 2];
     *     ...
     *     ->checkResource($userIdPermissions, $resourceArray);
     * 
     * @param array $userResources     Array com a lista de IDs de recursos a qual o usuário
     *                                 possui permissão de acesso.
     * 
     * @param array $resourceTags      Array contendo todos os recursos existentes no sistema de
     *                                 permissões.
     */
    public function checkResource($userResources, $resourceTags = GALASTRI["permission"]["resources"]){
        $resource = $this->resource;
        $resourceIds = array_flip($resourceTags);
        
        foreach($userResources as $resourceId){
                if($resourceIds[$resourceId] === $resource){
                return TRUE;
            }
        }
        return FALSE;
    }
}