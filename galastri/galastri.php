<?php
/**
 * Galastri Framework
 * @author André Luis Galastri <contato@andregalastri.com.br>
 * @copyright Copyright (c) 2020, André Luis Galastri
 * @version See VERSION
 * @license https://github.com/andregalastri/galastri-framework/blob/master/LICENSE
 * 
 * MIT License
 * 
 * Copyright (c) 2020 André Galastri
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software
 * and associated documentation files (the "Software"), to deal in the Software without restriction,
 * including without limitation the rights to use, copy, modify, merge, publish, distribute,
 * sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all copies or
 * substantial portions of the Software.
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING
 * BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
 * DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

/**
 * - galastri.php -
 * 
 * Arquivo de inicialização do microframework. Aqui as funções e constantes são carregadas o
 * autoloader é carregado e, por fim, o microframework é inicializado através da chamada do método
 * execute() da classe core\Galastri.
 */
namespace galastri;

define('DIR', __DIR__);
require_once('functions.php');

define('FRAMEWORK_VERSION', file_exists(path('../VERSION')) ? file_get_contents(path('../VERSION')) : '');

/** Faz a importação das funções globais. */

/** Faz a importação das constantes dos arquivos de configuração e armazena tudo em uma constante.
 * Desta forma, todas as configurações são acessadas através da constante GALASTRI.
 */
define ('GALASTRI',
        array_merge(
            $debug,
            require(path('config/framework.php')),
            ['database' => require(path('config/database.php'))],
            ['routes'   => require(path('config/routes.php'))],
            ['urlAlias' => require(path('config/url-alias.php'))],
            ['version'  => FRAMEWORK_VERSION]
        )
);

unset($debug);

/** Faz a importação de arquivos adicionais que podem ser configurados pelo usuário. */
foreach(glob(path(GALASTRI['folders']['additional-config'].'/*.php')) as $additionalConfig)
    require($additionalConfig);

require_once(path('autoload.php'));

core\Galastri::execute();
