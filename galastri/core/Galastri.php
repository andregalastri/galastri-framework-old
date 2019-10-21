<?php
/**
 * - Galastri.php -
 * 
 * Classe que contém a inicialização do microframework, os renderizadores e os controladores. A
 * execução de tudo ocorre dentro da classe Galastri, o que permite maior flexibilidade na chamada
 * de componentes através de composição.
 */
namespace galastri\core;

class Galastri extends Composition {
	
	/**
	 * Importação dos renderizadores.
	 * Rendizador, no contexto deste microframework, é qualquer componente que imprima algo na
	 * tela, sejam dados, sejam arquivos. Cada renderizador tem sua função. Todos eles foram
	 * escritos como sendo traits ao invés de classes.
	 * 
	 * Os renderizadores padrão são:
	 * 
	 * view				Organiza os elementos para imprimir um HTML. Importante: este microframe
	 * 					faz preferencia do uso do próprio PHP para imprimir os dados que são
	 * 					processados pelo controller, não optando por nenhuma engine de template.
	 * 					De qualquer forma, é provável que a instalação de uma engine de templates
	 * 					não cause incompatibilidades. Este renderizador exige que haja um controller
	 * 					configurado.
	 * 
	 * json				Todos os dados organizados pelo controller são impressos na tela em formato
	 * 					json. É ideal para se criar uma API rest ou para retornos de dados para
	 * 					consultas assíncronas de Javascript. Este renderizador exige que haja um
	 * 					controller configurado.
	 * 
	 * file				Responsável por renderizar arquivos, como imagens, documentos PDF, audio,
	 * 					vídeo, etc. É possível utilizar este renderizador para arquivos de download.
	 * 					Este renderizador não exige que haja um controller configurado,
	 */
	use		\galastri\extensions\renderers\File;
	use		\galastri\extensions\renderers\Json;
	use		\galastri\extensions\renderers\View;
	
	private $controller = FALSE;
	
	/**
	 * Este microframework se utiliza de composição como forma de trabalhar com reutilização de
	 * códigos, já que o PHP não permite heranças múltiplas. Mais informações no arquivo
	 * core\Composition.php.
	 */
	private function composition(){
		$this->debug();
		$this->redirect();
		$this->route();
	}

	public function __construct(){
		$this->composition();
		date_default_timezone_set(GALASTRI["timezone"]);
	}
	
	/**
	 * Execução do microframework.
	 * Uma série de testes são executados em série para verificar se as configurações foram feitas
	 * corretamente. Se tudo estiver certo, uma instância do controller é criada
	 * 
	 * O controller, após processado, irá retornar dados que são armaeznados no atributo $controller,
	 * sendo este acessível pelo renderizador.
	 * 
	 * O renderizador configurado é chamado para realizar a exibição dos dados.
	 */
	public  function execute(){
		session_start();
		$this->debug->trace = debug_backtrace()[0];

		$this
			->checkRendererExists()
			->checkOffline("global")
			->checkOffline($this->route->renderer)
			->checkRequiredController()
			->checkController()
			->checkClass()
			->checkMethod()
			->callController();

		$renderer = $this->route->renderer;
		$this->$renderer();
	}
	
	/**
	 * Verifica se a configuração offline está ativa ou não. Este método executa outro método cujo
	 * nome é formado pelo nome do renderizador e seguido pelo termo "CheckOffline". Por exemplo,
	 * o renderizador view possui um método chamado viewCheckOffline().
	 * 
	 * Por padrão, ao menos 2 métodos são executados usando este aqui: um verificando se a
	 * configuração offline está ativa globalmente e outra se a configuração offline está ativa
	 * na rota (arquivo routes.php).
	 * 
	 * @param string $scope				Nome do renderizador ou do escopo que irá se unir ao termo
	 * 									"CheckOffline" a fim de compor o nome completo da função
	 * 									que verifica se há configurações offline ativas.
	 */
	private function checkOffline($scope){
		return $this->{$scope."CheckOffline"}();
	}
	
	
	/**
	 * Verifica se o renderizador especificado na configuração existe. É importante ressaltar que
	 * os renderizadores são traits importadas logo após a definição desta classe.
	 */
	private function checkRendererExists(){
		$this->debug->trace = debug_backtrace()[0];
		
		$renderer = $this->route->renderer;
		
		if(isset($renderer) and !empty($renderer)){
			if(!method_exists(get_class($this), $renderer)){
				$this->debug->error("RENDERER003", $renderer)->print();
			}
		} else {
			$this->debug->error("CONFIG001", $path)->print();
		}
		
		return $this;
	}
	
	/**
	 * Verifica se o renderizador obriga que um controller esteja configurado e que ele exista
	 * na pasta controller. Os renderizadores view e json, por padrão, exigem que existam controllers
	 * ativos para cada página requisitada. Caso não exista nenhum método ou classe configurada
	 * nestes casos, uma mensagem de erro é exibida.
	 */
	private function checkRequiredController(){
		$this->debug->trace = debug_backtrace()[0];
		
		$renderer			= $this->route->renderer;
		$controller			= $this->route->controller;
		$method				= $this->route->renderer;
		$path				= $this->route->path;
		
		$requireController	= $this->{$renderer."Controller"};

		if($method === NULL and $requireController){
			$this->debug->error("RENDERER001", $renderer, $path)->print();
		} elseif($controller === NULL and $requireController){
			$this->debug->error("RENDERER002", $renderer, $path)->print();
		}
		return $this;
	}
	
	/**
	 * Verifica se o controller existe ou não. Quando o controller não é obrigatório, e ele
	 * não foi definido, o valor do atributo $controller é FALSE. Já quando o controller existe,
	 * o atributo $controller irá armazenar o caminho de chamada da classe controladora.
	 */
	private function checkController(){
		$controller = $this->route->controller;
		if($controller === NULL){
			$this->controller = FALSE;
		} else {
			$this->controller = str_replace(["/","."], ["\\",""], GALASTRI["folders"]["controller"]).str_replace("/", "\\", $controller);
		}
		return $this;
	}

	/**
	 * Verifica se o arquivo com a classe controladora existe.
	 */
	private function checkClass(){
		$this->debug->trace = debug_backtrace()[0];
		$controller = $this->controller;
		
		if($controller){
			if(!class_exists($controller)){
				$this->debug->error("CONTROLLER001", $controller)->print();
			}
		}
		return $this;
	}

	/**
	 * Verifica se o arquivo com a classe controladora possui um método que representa a página
	 * requisitada.
	 */
	private function checkMethod(){
		$this->debug->trace = debug_backtrace()[0];
		
		$controller = $this->controller;
		$method = $this->route->method;
		
		if($controller){
			if(!method_exists($controller, $method)){
				$this->debug->error("CONTROLLER002", $controller, $method)->print();
			}
		}
		return $this;
	}
	
	/**
	 * Estando tudo correto e sem erros, uma instância desta classe controladora é criada dentro
	 * do atributo $controller, seguido da execução do método que representa a página requisita.
	 * 
	 * A execução deste método irá retornar dados ou não. Caso retorne, estes dados poderão ser
	 * impressos pelo renderizador.
	 */
	private function callController(){
		$controller = $this->controller;

		if($controller){
			$this->controller = new $controller();
			$this->controller->startController($this->route);
		}
	}
	
	/**
	 * Verifica se a opção global offline está ativa. Caso esteja, verifica se a configuração
	 * redirectTo está prenchida. Caso sim, o usuário é redirecionado para aquela página configurada
	 * no redirectTo. Caso não, apenas a mensagem message será exibida na tela.
	 */
	private function globalCheckOffline(){
		if(GALASTRI["offline"]["status"]){
			$redirectTo = GALASTRI["offline"]["redirectTo"];
			if($redirectTo){
				$url = GALASTRI["urls"][$redirectTo];
				if($this->route->urlString !== $url) $this->redirect->location($url);
			}
			if(($redirectTo and GALASTRI["offline"]["forceMessage"]) or !$redirectTo)	$this->printContent(GALASTRI["offline"]["message"], TRUE);
		}
		return $this;
	}
	
	/**
	 * Método usado apenas pelos renderizadores. Verifica se a página requer autenticação. Neste
	 * caso, é verificado se a sessão está ativa. Caso não esteja, será necessário ou redirecionar
	 * o usuário para uma outra página ou retornar dados de erros.
	 * 
	 * O redirecionamento só ocorrerá quando o parâmetro onAuthFail estiver ativo na configuração
	 * das rotas (routes.php). Do contrário, uma array contendo erro código -1 e uma mensagem
	 * "session" é retornada para a view.
	 * 
	 * É importante alertar que este teste depende de outro teste realizado na classe Controller.
	 * Lá é verificado se a sessão está ativa ou não antes da controller ser processada.
	 * 
	 * @param object $data				Armazena o objeto obtido do método getRenderData() da
	 * 									classe Controller.
	 */
	private function checkAuth($data){
		$authStatus = property_exists($data, "authStatus") ? $data->authStatus : TRUE;
		$onAuthFail = $this->route->onAuthFail;
		
		if($authStatus === FALSE){
			if($onAuthFail){
				$this->redirect->location($onAuthFail);
			} else {
				return ["error" => ["code" => -1, "message" => "session"]];
			}
		}
		
		return $data->data;
	}
	
	/**
	 * Métodos para impressão ou requerimento de conteúdo.
	 */
	private function requireContent	($render,  $file, $exit = FALSE){ $exit ? exit(require_once($file))	: require_once($file); }
	private function printContent	($content, $exit = FALSE){		  $exit ? exit(print($content))		: print($content); }
}