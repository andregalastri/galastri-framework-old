<?php
namespace galastri\controller;

class index extends \galastri\core\Controller {
    
	protected function main(){
		return [];
	}
	
	protected function pagina_nao_encontrada(){
		header("HTTP/1.0 404 Not Found");
		return [];
	}
}