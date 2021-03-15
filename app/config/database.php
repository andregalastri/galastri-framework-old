<?php
/**
 * - database.php -
 * 
 * Arquivo de configuração padrão para conexão com banco de dados.
 * Toda conexão e consulta a banco de dados do microframework se utiliza da classe PDO.
 * 
 * - PARÂMETROS -
 * driver      (string) Indica qual é o driver utilizado. O padrão é o mysql.
 * 
 * active      (bool) Ativa ou desativa a conexão com banco de dados quando TRUE ou
 *             FALSE.
 * 
 * host        (string) Endereço do servidor onde o banco de dados está hospedado.
 * 
 * database    (string) Nome do banco de dados padrão que será usado.
 * 
 * user        (string) Nome de usuário para conexão com o banco de dados.
 * 
 * password    (string) Senha do usuário para conexão com o banco de dados.
 * 
 * options     (array) Opções do PDO.
 */
return [
    'driver'        => 'mysql',
    'active'        => true,
    'host'          => '',
    'database'      => '',
    'user'          => '',
    'password'      => '',
    
    'options'       => [
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
    ],

    'backupFolder'  => '../galastri/backup/db',
];
