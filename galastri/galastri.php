<?php
/**
 * Galastri Framework
 * @author André Luis Galastri <contato@andregalastri.com.br>
 * @copyright Copyright (c) 2019, André Luis Galastri
 * @version 0.4 alpha
 * @license https://github.com/andregalastri/galastri-framework/blob/master/LICENSE
 * 
 * MIT License
 * 
 * Copyright (c) 2019 André Galastri
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
 * Arquivo de inicialização do microframework. Aqui as funções e constantes são carregadas, as
 * definições de apresentação de erros (debug) são configuradas, o autoloades é carregado e, por
 * fim, o microframework é inicializado através do instanciamento da classe core\Galastri seguido
 * da execução do método inicial execute().
 */
namespace galastri;

require_once("functions.php");

/** Faz a importação das constantes dos arquivos de configuração e armazena tudo em uma constante.
 * Desta forma, todas as configurações são acessadas através da constante GALASTRI. */
define ("GALASTRI", (
    array_merge(
                         require("config/default.php"),
        ["database"   => require("config/database.php")],
        ["routes"     => require("config/routes.php")],
        ["permission" => require("config/permission.php")],
        ["url_alias"  => require("config/url_alias.php")]
    )
));

error_reporting(E_ALL);
ini_set('display_errors', GALASTRI["debug"] ? "On" : "Off");

require_once("autoload.php");

core\Galastri::execute();