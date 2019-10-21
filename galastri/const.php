<?php
/**
 * - const.php -
 * 
 * Arquivo que faz a importação das constantes dos arquivos de configuração e armazena tudo em
 * uma única constante. Desta forma, todas as configurações são acessadas através da constante
 * GALASTRI.
 */
define ("GALASTRI", (
	array_merge(
		require("config/default.php"),
		["database"		=>	require("config/database.php")],
		["routes"		=>	require("config/routes.php")],
		["permission"	=>	require("config/permission.php")]
		)
	)
);