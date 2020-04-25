<?php
/**
 * - framework.php -
 * 
 * Arquivo de configuração principal. Possui as configurações mais básicas do microframework.
 * 
 * IMPORTANTE: Todas as configurações são importantes, por isso, modifique apenas as coisas que
 * realmente deseja mudar e deixe o restante com valores padrão. Os valores padrões são importantes
 * pois o microframework ainda pressupõe que as chaves de configurações estejam configuradas. Por
 * isso, não remova nenhuma chave aqui e apenas modifique os valores que realmente você usará.
 * 
 * - PARÂMETROS -
 * title                (array) Agrupa as definições usadas entre as tags <title>.
 * 
 *   siteName           (string) Título do site. Em geral o nome que irá aparecer em todas as
 *                      páginas.
 * 
 *   divisor            (string) Divisor entre o título da página e o título do site. Por
 *                      exemplo "Vendas | Galastri Framework".
 * 
 *   template           (array) Ordem dos elementos que formam o título que será exibido entre as
 *                      tags <title>. O padrão é o título da página seguido do divisor e do nome
 *                      do site.
 * 
 * timezone             (string) Timezone padrão do site.
 * 
 * authentication       (array) Agrupa definições de sessão, para áreas que exigirem autorização
 *                      de acesso. É possível que esta opção seja simplificada.
 * 
 *   cookieExpire       (int) Indica o valor em segundos para expiração do cache. Quando não
 *                      configurado irá levar em conta a configuração global.
 * 
 * template             (array) Armazena os endereços dos arquivos de template.
 * 
 *   root               (string) Indica um caminho para o arquivo raiz do template.
 *  
 *   head               (string) Indica um caminho para o arquivo que contem a tag <head>.
 * 
 *   nav                (string) Indica um caminho para o arquivo que contem a tag <nav>.
 * 
 *   footer             (string) Indica um caminho para o arquivo que contem a tag <footer>.
 * 
 * folders              (array) Armazena as pastas padrão onde arquivos controller e view são
 *                      armazenados.
 * 
 *   root               (string) Pasta raiz do microframework.
 * 
 *   controller         (string) Pasta onde são armazenados os arquivos controller.
 * 
 *   view               (string) Pasta onde são armazenados os arquivos view.
 * 
 * contentType          (array) Agrupa todos os tipos de arquivos possíveis que podem ser
 *                      renderizados pelo renderizador File. Na chave informa-se a extensão do
 *                      arquivo e no valor informa-se o seu tipo MIME. Coloque aqui quantos tipos
 *                      de arquivos desejar.
 * 
 * importTags           (array) Agrupa as tags HTML que serão usados quando forem configurados
 *                      arquivos adicionais no arquivo routes.php.
 * 
 * offline              (array) Agrupa configurações para desativar o site.
 * 
 *   status             (bool) Ativa ou desativa o site quando true ou false. Ideal para quando
 *                      o for realizar manutenção no site.
 * 
 *   message            (string) Mensagem que será impressa enquanto o site estiver offline.
 * 
 *   redirectTo         (string|null) Quando preenchido, redireciona o usuário para uma página
 *                      específica.
 * 
 *   forceMessage       (bool) Força ou não a exibição da mensagem de status offline quando true
 *                      ou false.
 * 
 * cache                (array) Configurações de cache para imagens e arquivos.
 * 
 *   status             (bool) Ativa ou desativa o cache. Para imagens de uso comum é ideal para
 *                      garantir baixo consumo de banda de internet.
 * 
 *   expire             (int) Indica o valor em segundos para expiração do cache.
 */
return [
    'title' => [
        'siteName'       => 'Galastri Framework',
        'divisor'        => ' | ',
        'template'       => ['title', 'divisor', 'siteName'],
    ],

    'timezone'           => 'America/Sao_Paulo',

    'template' => [
        'root'           => '../galastri/view/template/template.php',
        'head'           => '../galastri/view/template/head.php',
        'nav'            => '../galastri/view/template/nav.php',
        'footer'         => '../galastri/view/template/footer.php',    
    ],

    'folders' => [
        'root'              => '../galastri',
        'additional-config' => '../galastri/config/additional-config',
        'controller'        => '../galastri/controller',
        'view'              => '../galastri/view',
    ],

    'contentType' => [
        'webmanifest'    => 'application/manifest+json',
        'xml'            => 'application/xml',
        'jpg'            => 'image/jpg',
        'png'            => 'image/png',
        'gif'            => 'image/gif',
        'ico'            => 'image/ico',
        'svg'            => 'image/svg+xml',
        'svgf'           => 'font/svg+xml',
        'pdf'            => 'application/pdf',
        'css'            => 'text/css',
        'js'             => 'application/javascript',
        'woff2'          => 'font/woff2',
        'woff'           => 'font/woff',
        'eot'            => 'font/eot',
        'ttf'            => 'font/ttf',
    ],

    'importTags' => [
        'js'             => '<script src="%s"></script>',
        'css'            => '<link rel="stylesheet" type="text/css" href="%s">',
    ],

    'offline' => [
        'status'         => false,
        'message'        => 'Em manutenção.',
        'redirectTo'     => 'index',
        'forceMessage'   => false,
    ],

    'cache' => [
        'status'         => true,
        'expire'         => 86400 * 2, /* Cache expira em 2 dias */
    ],
    
    'permission' => [
        'failMessage'    => 'Sem permissão para a ação.',
        'exceptionTag'   => 'permissionFail',
    ],
    
    'authentication' => [
        'failMessage'    => 'Autorização não concedida.',
        'exceptionTag'   => 'deniedAuth',
        'cookieExpire'   => 86400 * 1, /* Cookie expira em 1 dia */
    ],

    'error404Url'        => 'error404',
];
