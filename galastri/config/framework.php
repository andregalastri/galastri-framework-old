<?php
/**
 * - framework.php -
 * 
 * Arquivo de configuração principal. Possui as configurações fundamentais do microframework.
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
 * contentType          (array) Agrupa todos os tipos de arquivos que podem ser renderizados pelo
 *                      renderizador File e como o navegador irá armazenar o cache. Na chave deve
 *                      ser informado a extensão do arquivo.
 *                      
 *                      O valor de cada chave deve ser um array de dois índices:
 *                      |  No índice [0]: informar o tipo MIME da extensão do arquivo.
 *                      |  No índice [1]: informar os parâmetros Cache-Control.
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
        'controller'        => '../galastri/controller',
        'view'              => '../galastri/view',
    ],

    'contentType' => [
        'webmanifest'    => ['application/manifest+json', 'must-revalidate'],
        'xml'            => ['application/xml',           'must-revalidate'],
        'jpg'            => ['image/jpg',                 'must-revalidate'],
        'png'            => ['image/png',                 'must-revalidate'],
        'gif'            => ['image/gif',                 'must-revalidate'],
        'ico'            => ['image/ico',                 'must-revalidate'],
        'svg'            => ['image/svg+xml',             'must-revalidate'],
        'svgf'           => ['font/svg+xml',              'must-revalidate'],
        'pdf'            => ['application/pdf',           'must-revalidate'],
        'css'            => ['text/css',                  'must-revalidate'],
        'js'             => ['application/javascript',    'must-revalidate'],
        'woff2'          => ['font/woff2',                'max-age=86400, public'],
        'woff'           => ['font/woff',                 'max-age=86400, public'],
        'eot'            => ['font/eot',                  'max-age=86400, public'],
        'ttf'            => ['font/ttf',                  'max-age=86400, public'],
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
