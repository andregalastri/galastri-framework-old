<?php
/**
 * - autoload.php -
 * 
 * Inicia o autoloader, responsável por importar arquivos logo que as instâncias de classes são
 * criadas. Ao invés de se utilizar comandos como require() ou include() para carregar arquivos
 * de classes, o próprio PHP fica responsável por detectar esta necessidade durante a criação de
 * instâncias de classes.
 * 
 * Para que isto funcione corretamente é necessário seguir algumas regras:
 * - É necessário usar namespaces em todos os arquivos que contém classes.
 * - É necessário que o nome da classe seja idêntico ao nome do arquivo que a contém.
 * 
 * Por exemplo:
 * Supondo um arquivo controller chamado contato.php, localizado dentro da pasta galastri/controller.
 * O conteúdo deste arquivo deverá seguir o seguinte template:
 * 
 * <?php
 *     namespace galastri\controller;
 * 
 *     class contato {
 *         <conteúdo da classe>
 *     }
 * 
 * namespace:  Especificar o diretório exato onde o próprio arquivo está.
 * class:      Especificar o nome da classe idêntico ao nome do arquivo.
 * 
 * O motivo desta obrigatoriedade é de que o autoloader precisa localizar o arquivo correto
 * quando a instância da classe for criada. A criação desta instância deverá conter o caminho
 * completo para o arquivo, conforme exemplo abaixo:
 * 
 * <?php
 *     new galastri\controller\contato;
 * 
 * Desta forma, ao criar esta instância, o PHP irá buscar o arquivo contato.php dentro do
 * diretório galastri/controller.
 * 
 * Sem estas configurações, haverá erro de chamada das classes.
 * 
 * @param string $className        Armazena o endereço completo do arquivo a ser requisitado,
 *                                 formado por namespace\classe. É necessário substituir a barra
 *                                 invertida por uma barra normal, para que o endereço seja
 *                                 localizado no sistema de arquivos.
 */
spl_autoload_register(function($className){
    
    $className = explode('\\', $className);
                 array_shift($className);
    $className = implode('/', $className);

    $corePath = path('/../galastri/'."/$className.php");
    $appPath = path('/'.GALASTRI['folders']['app']."/$className.php");
    
    /* Verifica se o arquivo com o nome da classe existe. Se existir ele irá importar o conteúdo
     * do arquivo através de um require_once. */
    if(file_exists($corePath)) require_once $corePath;
    if(file_exists($appPath))  require_once $appPath;
});
