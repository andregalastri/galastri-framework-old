<?php
/**
 * - Debug.php -
 * 
 * Classe que permite traçar a localização de erros internos do microframework quando a configuração
 * de debug estiver ativa na configuração do arquivo config/default.php.
 * 
 * Quando o debug está ativo e ocorrer um erro, uma mensagem é exibida detalhando o problema. Já
 * quando o debug está inativo, então a mensagem de erro é uma mensagem genérica de que houve um
 * erro.
 * 
 * Por isso é importante desativar o debug quando colocar um site em produção, pois determinadas
 * mensagens de erro podem apresentar dados do servidor que devem ser restritos somente durante
 * a fase de desenvolvimento.
 */
namespace galastri\core;

class Debug extends Composition {
	private $message	= NULL;
	public $error		= FALSE;
	public $trace		= FALSE;
	
	/**
	 * Método que define que houve um erro e qual será a mensagem que será exibida.
	 * 
	 * @param string $tag				Armazena a tag que contém a mensagem de erro que será
	 * 									exibida enquanto o debug estiver ativo.
	 * 
	 * @param mixed ...$data			Armazena dados quaisquer que são importantes de serem
	 * 									informados nas mensagens de erro.
	 */
	public	function error($tag, ...$data){
		if(!GALASTRI["debug"]){
			$this->message	= "<code>OCORREU UM ERRO INTERNO. CONTATE O DESENVOLVEDOR.</code>";
		} else {
			$class			= $this->trace["class"];
			$method			= $this->trace["function"];
			$line			= $this->trace["line"];
			$file			= $this->trace["file"];
			$error			= $this->getError($tag, $data);

			$this->message	= "
				<code>
					<small>Erro durante a execução da função <b>$class->$method()</b></small><br/>
					<big>$error</big><br/><br/>
					<small>
						LINHA <b>$line</b> EM <b>'$file'</b><br/>
						$tag
					</small>
				</code>";
		}
		
		$this->error = TRUE;
		return $this;
	}
	
	/**
	 * Método que imprime uma mensagem de erro na tela.
	 */
	public	function print(){
		exit(print($this->message));
	}
	
	/**
	 * Método que retorna uma mensagem de erro, podendo ser armazenado em uma variável, por exemplo.
	 */
	public	function return(){
		return strip_tags($this->message);
	}
	
	
	/**
	 * Método que reune todas as tags de erros do microframework e suas respectivas mensagens de
	 * erros.
	 * 
	 * @param string $tag				Armazena a tag que contém a mensagem de erro que será
	 * 									exibida enquanto o debug estiver ativo.
	 * 
	 * @param array data				Armazena dados quaisquer que são importantes de serem
	 * 									informados nas mensagens de erro.
	 */
	private function getError($tag, $data){
		switch($tag){
			case "REDIRECT001":		return "Nenhum parâmetro foi informado. É necessário informar uma string contendo uma URL ou uma palavra chave para redirecionamento.";
			
			case "OFFLINE001":		return GALASTRI["offline"]["message"];

			case "CONFIG001":		return "A rota <b>'$data[0]'</b> não possui um renderizador configurado no arquivo <b>'config/routes.php'</b>.";
			case "CONFIG002":		return "A rota <b>'$data[0]'</b> não possui o método <b>'$data[1]'</b> configurado no arquivo <b>'config/routes.php'</b>.";
			case "CONFIG003":		return "A rota <b>'$data[0]'</b> requer um controller configurado no arquivo <b>'config/routes.php'</b>.";

			case "RENDERER001":		return "O renderizador <b>'$data[0]'</b> requer um método configurado para a rota <b>'$data[1]'</b> no arquivo <b>'config/routes.php'</b>.";
			case "RENDERER002":		return "O renderizador <b>'$data[0]'</b> requer um controller configurado para a rota <b>'$data[1]'</b> no arquivo <b>'config/routes.php'</b>.";
			case "RENDERER003":		return "O renderizador <b>'$data[0]'</b> não existe.";

			case "CONTROLLER001":	return "O controller <b>'$data[0]'</b> não existe.";
			case "CONTROLLER002":	return "O controller <b>'$data[0]'</b> não possui o método <b>'$data[1]'</b>.";
			case "CONTROLLER003":	return "É esperado que o controller retorne um objeto, mas o retorno é um dado do tipo <b>'$data[0]'</b>.";

			case "VIEW001":			return "O renderizador <b>'view'</b> não conseguiu localizar o arquivo <b>'$data[0]'</b>.";
			case "VIEW002":			return "A rota <b>'$data[0]'</b> requer uma view configurada no arquivo <b>'config/routes.php'</b>.";

			case "FILE001":			return "A extensão <b>'.$data[0]'</b> e seu respectivo Content-type não foi definido nas configurações.";
			case "FILE002":			return "O caminho do arquivo não foi definido.";
			case "FILE003":			return "Não foi definida a extensão do arquivo.";
			case "FILE004":			return "O arquivo <b>'$data[0]'</b> não existe.";
			case "FILE005":			return "O método '<b>filePath()</b>' só pode ser usado junto ao renderizador 'file'";

			case "DATETIME001":		return "A data <b>'$data[0]'</b> é inválida ou não está de acordo com o formato <b>'$data[1]'</b>.";
			case "DATETIME002":		return "A data informada não é um objeto do tipo DateTime";
			case "DATETIME003":		return "A data limite <b>'$data[0]'</b> é inválida ou não está de acordo com o formato <b>'$data[1]'</b>.";
			case "DATETIME004":		return "A data limite informada não é um objeto do tipo DateTime.";
			
			case "VALIDATION001":	return "O método <b>'validate()'</b> precisa ser iniciado antes dos métodos validadores.";

			case "DATABASE001":		return "O método <b>'connect()'</b> precisa ser iniciado antes dos métodos de consulta.";
			case "DATABASE002":		return "A querystring <b>'$data[0]'</b> é inválida.";
			case "DATABASE003":		return "Não existe nenhum resultado armazenado na consulta padrão.";
			case "DATABASE004":		return "Não existe nenhum rótulo <b>'$data[0]'</b> definido em uma consulta.";
			
			case "NUMBER001":		return "O tipo <b>'$data[0]'</b> não é um tipo de número válido. Os tipos válidos que podem ser informados são $data[1].";

			case "AUTH001":			return "O nome <b>'$data[0]'</b> é um nome de campo de sessão reservado pelo microframework.";

			case "DEFAULT":
			default:		return FALSE;
		}
	}
}