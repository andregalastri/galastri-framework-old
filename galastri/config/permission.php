<?php
/**
 * - permission.php -
 *
 * Arquivo de configuração de nomes dos grupos e/ou recursos e seus respectivos IDs que serão
 * usados para testes de restrição de acesso a usuários não autorizados.
 * 
 * Em geral, quando um site têm partes de acesso restrito a determinados usuários, é necessário
 * verificar se o usuário que está tentando fazer o acesso faz parte de um grupo permitido.
 * 
 * Para isso pode-se ter duas abordagens:
 * 
 * - Restrição a grupos: nesta abordagem, o usuário terá um ID do grupo a qual ele pertence (em
 * geral armazenado junto com seus dados no banco de dados). A cada página restrita é definido
 * quais são os grupos que podem acessar aquele recurso. Em seguida, faz-se um teste para verificar
 * se o ID do grupo a qual o usuário pertence faz parte da lista de grupos permitidos.
 * 
 * - Restrição usuário a usuário: esta abordagem permite maior controle de restrição de acesso, mas
 * também é mais complexa de ser implementada. Deve-se configurar um rótulo a cada página restrita.
 * Armazena-se em banco de dados quais são os rótulos que cada usuário pode acessar. Em seguida,
 * faz-se um teste para verificar se o ID do rótulo da página faz parte da lista de rótulos que o
 * usuário possui permissão.
 * 
 * É possível fazer uso das duas abordagens ao mesmo tempo.
 * 
 * Aqui pode-se configurar uma lista fixa de grupos e recursos, colocando como chave o nome do
 * grupo ou recurso e como valor o seu ID.
 * 
 * Caso se deseje utilizar uma lista dinâmica, será necessário informar estes dados diretamente
 * no método testador (mais informações em core/Permission.php).
 */
return [
	"groups"       => [],
	"resources"    => [],
];
