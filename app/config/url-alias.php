<?php
/**
 * - url-alias.php -
 * 
 * Arquivo que possui rótulos de redirecionamento. É possível configurar rótulos para qualquer
 * URL e utilizar o componente Redirect para fazer o redirecionamento através do rótulo aqui
 * definido. Isso permite maior facilidade de modificação de uma URL, pois o valor chamado não
 * precisa ser reconfigurado no código fonte como um todo, bastando mudar a configuração deste
 * arquivo.
 * 
 * Exemplo, ao configurar um rótulo 'teste1' => '/minha-pagina/teste1', ao chamar o redirecionador
 * Redirect::location('teste1') o usuário será redirecionado para a URL '/minha-pagina/teste1'.
 */
return [
    'index'     => '/',
    'error404'  => '/pagina-nao-encontrada',
];
