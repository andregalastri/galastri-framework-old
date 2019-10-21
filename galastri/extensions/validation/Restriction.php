<?php
/**
 * - Restriction.php -
 * 
 * Trait que contém os métodos de restrição usados por validadores da classe Validantion. Cada
 * método especifica um comportamento diferente a ser definido pelo próprio validador.
 */
namespace galastri\extensions\validation;

trait Restriction {
	/**
	 * Métodos de restrição. Sua função pode variar de validador para validador. Para mais
	 * informações, verifique as explicações sobre os validadores.
	 */
	public	function any()				{ $this->restrictionChain("any"); 			return $this; }
	public	function all()				{ $this->restrictionChain("all"); 			return $this; }
	public	function strict()			{ $this->restrictionChain("strict"); 		return $this; }
	public	function notStrict()		{ $this->restrictionChain("notStrict"); 	return $this; }
	
	/**
	 * Método que cria o elo na corrente com a restrição. Todo as restrições utilizam os mesmos
	 * comandos, por isso optou-se por criar um comando único que pode ser reaproveitado por
	 * todos eles.
	 * 
	 * @param string $name				Nome do método de comparação.
	 */
	private function restrictionChain($name){
		$this->chain->create(
			$name,
			[
				"name" 		 => $name,
				"attach"	 => TRUE,
			],
			(function($chainData, $data){ return $this->chain->resolve($chainData, $data); })
		);
	}
}