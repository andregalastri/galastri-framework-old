<?php
namespace galastri\controller;

class index extends \galastri\core\Controller
{
    
	protected function main()
    {
		return [];
	}

	protected function paginaNaoEncontrada()
    {
        header('HTTP/1.0 404 Not Found');
		return [];
	}
}
