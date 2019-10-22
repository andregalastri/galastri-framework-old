<?php
/**
 * - routes.php -
 *
 * Arquivo de configuração das rotas da url.
 * 
 * | Todas as áreas (classes) devem ter seus nomes precedidos por barra (/)
 * | Todas as páginas da área (métodos) devem ter seus nomes precedidos por arroba (@)
 * | Todos os parâmetros de configuração não devem ser precedidos por nenhum caractere especial.
 */

/**
 * - CLASSES E MÉTODOS -
 * /                 (string) Indica a index.
 *                         
 * @main             (string) Indica o método padrão main() que estará contido na classe. Este
 *                   método é chamado quando nenhuma outra página for especificada na URL.
 * 
 * Todas as outras áreas e páginas podem ter quaisquer nomes especificados, desde que se defina
 * se são classes (/) ou métodos (@).
 * 
 * - PARÂMETROS PARA CLASSES OU MÉTODOS -
 * renderer          (string) Indica um renderizador para a área ou método. Os padrões são "view"
 *                   "json" e "file".
 *                             
 * offline           (bool) Opcional. Indica se a área ou método está offline. Esta configuração
 *                   afeta todos as áreas e métodos filhos.
 *                             
 * authTag           (string) Opcional. Indica que a área ou método requer autenticação ativa para
 *                   ser acessada. É necessário especificar um nome que servirá como tag de acesso
 *                   para os parâmetros da sessão.
 *                             
 * onAuthFail        (string) Opcional. Indica uma URL para caso o usuário não esteja autenticado.
 * 
 * - PARÂMETROS PARA MÉTODOS -
 * title             (string) Opcional. Indica um título para a página.
 * 
 * view              (string) Opcional. Indica uma view diferente do padrão. Quando não configurado
 *                   irá se basear no caminho padrão /<view>/<classe>/<metodo>.php.
 * 
 * controller        (string) Opcional. Indica uma controller diferente do padrão. Quando não
 *                   configurado irá se basear no caminho padrão /<controller>/<classe>/<metodo>.php.
 * 
 * cache             (array) Opcional. Configurações de cache para imagens e arquivos.
 * 
 *   status          (bool) Opcional. Ativa ou desativa o cache. Quando não configurado irá levar
 *                   em conta a configuração global.
 * 
 *   expire          (int) Opcional. Indica o valor em segundos para expiração do cache. Quando
 *                   não configurado irá levar em conta a configuração global.
 * 
 * template          (array) Opcional. Permite ativar ou desativar partes do template.
 * 
 *   root            (string) Opcional. Indica um caminho para o arquivo raiz do template. Quando
 *                   não configurado, o caminho usado será o padrão.
 *  
 *   head            (bool|string) Opcional. Desativa a tag <head> do template quando FALSE ou
 *                   define um caminho para a parte. Quando não configurado, o caminho usado será
 *                   o padrão.
 * 
 *   nav             (bool|string) Opcional. Desativa a tag <nav> do template quando FALSE ou
 *                   define um caminho para a parte. Quando não configurado, o caminho usado será
 *                   o padrão.
 * 
 *   footer          (bool|string) Opcional. Desativa a tag <footer> do template quando FALSE ou
 *                   define um caminho para a parte. Quando não configurado, o caminho usado será
 *                   o padrão.
 * 
 * import            (array) Opcional. Cada valor da array indica um caminho para o arquivo externo
 *                   que poderá ser importado para a tag <head>. Por padrão pode-se informar arquivos
 *                   .js e .css.
 * 
 * downloadable      (bool) Opcional. Força a página a ser baixada. É usada principalmente para
 *                   fazer com que arquivos, imagens e documentos sejam baixaveis.
 */
return [
    "/" => [
        "@main"                  => [
            "renderer"           => "view",
        ],
        
        "@pagina_nao_encontrada" => [
            "renderer"           => "view",
            "view"               => "pagina_nao_encontrada.php",
        ],
    ],
];

    
