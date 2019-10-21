<?php
/**
 * - Composition.php -
 * 
 * Classe que faz a composição de instâncias das classes a fim de implementar um método de
 * reaproveitamento de códigos, já que o PHP não permite herança múltipla e o uso de traits não
 * faz a importação de todos os atributos e métodos, incluindo os com visibilidade private e
 * protected.
 * 
 * Recomenda-se que todas as classes que forem utilizar a composição sejam herdeiras da classe
 * Composition.
 */
namespace galastri\core;

abstract class Composition {
	protected $redirect		= NULL;
	protected $debug		= NULL;
	protected $route		= NULL;
	protected $chain		= NULL;
	protected $authentication		= NULL;
	protected $permission	= NULL;
	
	/**
	 * Métodos que criam uma instância da classe a qual representam dentro de um objeto com acesso
	 * permitido para classes herdeiras.
	 */
	protected function redirect()		{ $this->exists($this->redirect, 		'\galastri\core\Redirect');			}
	protected function debug()			{ $this->exists($this->debug,	 		'\galastri\core\Debug');			}
	protected function route()			{ $this->exists($this->route,	 		'\galastri\core\Route');			}
	protected function chain()			{ $this->exists($this->chain,	 		'\galastri\core\Chain');			}
	protected function authentication()	{ $this->exists($this->authentication,	'\galastri\core\Authentication');	}
	protected function permission()		{ $this->exists($this->permission,		'\galastri\core\Permission');		}
	
	/**
	 * Cria uma instância da classe chamada, verificando antes se a classe já está criada. Caso
	 * esteja, a instância não é criada. Isso impede que a mesma classe seja criada diversas
	 * vezes, o que poderia causar sobreposição de valores de atributos.
	 */
	private function exists(&$property, $class){
		if($property === NULL){
			$property = new $class();
		}
	}
}