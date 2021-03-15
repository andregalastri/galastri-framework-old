<?php
/**
 * - debug.php -
 * 
 * Arquivo de configuração do debug. Esta configuração é colocada sozinha pois permite que o
 * teste que verifica se o debug está ativo ou não ocorra logo no início do carregamento das
 * configurações. Ou seja, caso ocorra qualquer erro nas configurações do framework, o PHP irá
 * exibir o erro caso o debug esteja ativo.
 * 
 * IMPORTANTE: Não remova nenhuma chave aqui e apenas modifique os valores.
 * 
 * - PARÂMETRO -
 * debug                (bool) Ativa ou desativa as mensagens de debug quando true ou false. Quando
 *                      em produção é importante desativar pois em caso de erros nenhuma mensagem
 *                      de erro interno do servidor é exibida. Isso é importante pois as mensagens
 *                      de erro interno podem exibir informações restritas do servidor.
 */
return [
    'debug' => true,
];
