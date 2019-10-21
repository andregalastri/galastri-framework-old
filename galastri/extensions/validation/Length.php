<?php
/**
 * - Length.php -
 * 
 * Validador usado pela classe Validation que verifica a quantidade de caracteres de um dado e
 * permite delimitar a quantidade desses caracteres usando métodos de configuração de comparação.
 * 
 * Forma de uso (o comando abaixo não se trata de um exemplo):
 * 
 *		$validation
 *			->validate(<dado>, <rótulo>)
 *				->length()
 *					->min(<quantidade mínima>)
 *					->max(<quantidade máximo>)
 *					->diff(<quantidade diferente de>)
 *					->equal(<quantidade igual a>)
 *					->smaller(<quantidade menor que>)
 *					->greater(<quantidade maior que>)
 *			->execute();
 */
namespace galastri\extensions\validation;

trait Length {
	/**
	 * Método que verifica a quantidade de caracteres do dado.
	 */
	public	function length(){
		$this->beforeTest();
		$this->chain->create(
			"length",
			[
				"name" 		=> "length",
				"attach"	=> TRUE,
			],
			(
				function($chainData, $data){
					$error 				= $this->error->status;
					$this->debug->trace	= debug_backtrace()[0];

					if(!$error){
						$testValue		= $this->validation->value;

						foreach($chainData as $parameter){
							switch($parameter["name"]){
								
								/** Compara a quantidade de caracteres do dado com a quantidade
								 * especificada nos métodos de comparação. */
								case "length":
									foreach($operation as $operator){
										if(!$this->compare(strlen($testValue), $operator["operator"], $operator["delimiter"])){
											$error						= TRUE;
											$errorLog["invalidData"] 	= strlen($testValue);
											$errorLog["reason"]			= "length_".strlen($testValue);
											break 3;
										}
									}
									break;

								case "min":
								case "max":
								case "lesser":
								case "greater":
								case "equal":
								case "diff":
									$operation[]	= [
										"operator"	=> $parameter["operator"],
										"delimiter"	=> $parameter["delimiter"],
									];
									break;
							}
						}

						if($error){
							$errorLog["error"]			= $error;
							$errorLog["testName"]		= "length";

							$this->setValidationError($errorLog);
						}

						return $this->chain->resolve($chainData, $data);
					}
				}
			)
		);
		return $this;
	}
}