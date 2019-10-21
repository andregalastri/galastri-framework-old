<?php
/**
 * - Route.php -
 * 
 * Classe que define as propriedades da rota baseado na URL e nas configurações de rotas do
 * arquivo config/routes.php
 */
namespace galastri\core;

class Route extends Composition {
	public	$controller;
	public	$method;
	public	$view;
	public	$parameters;
	public	$path;
	public	$title;
	public	$cache;
	public	$template;
	public	$import;
	public	$renderer;
	public	$offline;
	public	$urlString;
	public	$downloadable;
	private $inheritanceConfig;
	
	/**
	 * Este microframework se utiliza de composição como forma de trabalhar com reutilização de
	 * códigos, já que o PHP não permite heranças múltiplas. Mais informações no arquivo
	 * core/Composition.php.
	 */
	private function composition(){
		$this->redirect();
	}
	
	/**
	 * Este microframework se utiliza de URLs amigaveis para as requisições e navegação entre as
	 * páginas. Por conta disso, toda URL sofre um tratamento de forma a impedir o comportamento
	 * padrão das URLs.
	 * 
	 * O padrão comum é que a URL acesse diretamente um arquivo. Aqui, todas as URLs são
	 * redirecionadas para o arquivo galastri.php. Este arquivo inicializa o microframework que
	 * em seguida realiza a resolução da URL.
	 * 
	 * Como este arquivo possui muitos comandos, a explicação de cada bloco importante de código
	 * será feito diretamente junto aos códigos.
	 */
	public function __construct(){
		$this->composition();
		
		/** A URL requisitada é armazenada na variável $url, que sofrerá duas modificações. A
		 * primeira é a remoção de querystrings, ou seja, tudo o que existir após o sinal de
		 * interrogação ? que por ventura estiver na URL.
		 * 
		 * A URL é convertido para letras minúsculas de forma a tornar, indiferente se existem
		 * termos em letras maiúsculas ou minúsculas na URL.
		 * 
		 * Em seguida, cada termo que estiver logo após uma barras / indicá um parâmetro que será
		 * armazenado em uma array. Supondo que a URL seja a seguinte:
		 * 
		 * 		dominio.com/vendas/nova_venda
		 * 
		 * O resultado disso é que 'vendas' e 'nova_venda' são parâmetros. Estes valores, portanto
		 * serão armazenados em uma array, que é a própria variável $url.
		 * 
		 * Também serão utilizadas aqui as rotas configuradas no arquivo config/routes.php.*/
		$url		= explode("?", lower($_SERVER['REQUEST_URI']));
		$url		= explode("/", $url[0]);
		$routes		= GALASTRI["routes"];
		$routePath  = "";

		/** Quando se trata da index, ou seja, quando nenhum parâmetro é informado na URL através
		 * das barras /, pode ocorrer de a variáveis $url armazenar duas chaves vazias. Neste caso
		 * é importante remover uma das chaves vazias.
		 */
		if(empty($url[1])) array_shift($url);
		
		/** Todos os valores da array $url serão acrescidas com uma barra / à frente do valor
		 * atual. Isso é necessário pois todas as áreas armazenadas nas configurações das rotas
		 * são iniciadas por uma barra.*/
		foreach($url as $key => &$routeName) $routeName = "/$routeName";
		unset($routeName);
		
		/** Aqui estão listados quais configurações da área pai, configurada em config/routes.php,
		 * são herdáveis pelas áreas e paǵinas filhas.
		 * 
		 * Por exemplo, suponha a seguinte configuração:
		 * 
		 * 		"/area1" => [
		 * 			"@pagina_a" => [],
		 * 			"@pagina_b" => [],
		 * 		],
		 * 
		 * Suponha que se queira que todas as páginas de area1 fiquem offline para manutenção.
		 * Ao invés de se colocar uma chave "offline" => TRUE para cada página, pode-se colocar
		 * uma única chave dessas logo após a declaração de area1. Esta chave é herdável, ou seja,
		 * todas as páginas de area1 também possuirão esta chave o seu respectivo valor, fazendo
		 * com que tornar uma área offline fique muito mais prática.
		 * 
		 * 		"/area1" => [
		 * 			"offline" => TRUE,
		 * 			"@pagina_a" => [],
		 * 			"@pagina_b" => [],
		 * 		],
		 * 
		 * Cada uma das configurações abaixo são herdáveis seguindo este mesmo princípio. O loop
		 * foreach abaixo realiza justamente um teste através do método checkInheritanceData() para
		 * se verificar e armazenar valores herdados para todas as áreas e páginas que não
		 * possuirem tal configuração.*/
		$this->inheritanceConfig	= [
			"offline"			=> FALSE,
			"downloadable"		=> FALSE,
			"cache"				=> FALSE,
			"authTag"			=> FALSE,
			"onAuthFail"		=> FALSE,
		];

		foreach($url as $routeName){
			if(array_key_exists($routeName, $routes)){
				$this->checkInheritanceData($routes[$routeName]);
				
				$routePath	.= $routeName;
				$routes		 = $routes[$routeName];
			}
		}
		
		/** A URL tratada será armazenada na variável $urlString em formato de string e será usada
		 * posteriormente para se criar os parâmetros internos no microframework. Ela armazenará
		 * o caminho completo da URL trata e servirá como base para se verificar o que será um
		 * parâmetro interno do que faz parte da rota.
		 * 
		 * Já a variável $routePath armazena apenas a rota e servirá como base para se verificar
		 * o que, da URL, é parâmetro interno do que é parte da rota.
		 * Para entender melhor, veja a URL abaixo:
		 * 
		 * 		dominio.com/vendas/editar_venda/1001
		 * 
		 * A variável $urlString irá armazenar tudo:			/vendas/editar_venda/1001.
		 * A variável $routePath irá armazenar apenas a rota:	/vendas/.
		 * 
		 * Perceba que o valor editar_venda e 1001 não foram armazenados como sendo parte da rota
		 * pois eles são considerados parâmetros que pertencem à rota. Por isso, esses valores
		 * deverão ser armazenados dentro da variável $parameters.
		 * 
		 * Tendo os parâmetros armazenados em uma array, verifica-se se o parâmetro 0 possui o
		 * valor de um método. Todo método é precedido de um arroba @.
	 	 */
		$urlString	= "/".ltrim(implode($url), "/");
		$routePath  = "/".ltrim($routePath, "/");
		$parameters = explode("/", ltrim(replaceOnce($routePath, "", $urlString), "/"));
		$method 	= "@".keyEmpty(0, $parameters, "");

		/** Da forma como tudo está sendo configurado, tudo o que é considerado um parâmetro é
		 * interpretado apenas assim: como um parâmetro. Porém, quando se trata da index, a
		 * existência de parâmetros podem confundir. Isso por que espera-se que logo após a barra
		 * venha uma página e não um parâmetro. Por isto, esta verificação abaixo testa se a rota
		 * está apontando para a index. Caso sim, é verificado se existem parâmetros. Caso sim,
		 * é testado se o primeiro parâmetro é igual a uma página (um método). Caso não, então
		 * haverá redirecionamento para uma página de erro 404.*/
		if($routePath === "/" and $routeName !== "/"){
			if(!array_key_exists($routeName, $routes) and !array_key_exists($method, $routes)){
				$this->redirect->location("error404");
			}
		}

		/** Verifica se o método existe na rota. Caso não, então o método padrão será o @main.*/
		$method = array_key_exists($method, $routes) ? $method : "@main";
		$method = $method === "@" ? "" : $method;

		/** Caso o método, seja ele @main ou qualquer outro, esteja configurado nas rotas, então
		 * significa que há um controller. Aqui, portanto, é configurado qual é o controller (que
		 * nada mais é do que a classe que contém os métodos e a view, que é configurada mesmo
		 * que o renderizador seja outro.
		 * 
		 * Caso contrário, então método, view e controller serão vazios.*/
		if(array_key_exists($method, $routes)){
			$this->checkInheritanceData($routes[$method]);
			
			$controller 	= $routePath === "/"  ? "/index" : $routePath;
			$view			= $method === "@main" ? "$controller.php" : "$controller/".ltrim("$method.php", "@");

			if(array_key_exists("controller", $routes[$method]))	$controller = $routes[$method]["controller"];
			if(array_key_exists("view",		  $routes[$method]))	$view 		= $routes[$method]["view"];

			$controller 	= str_replace("/", "\\", $controller);
		} else {
			$view			= NULL;
			$controller		= NULL;
			$method			= NULL;

			foreach($routes as $option => $value){
				if(lower(gettype($value)) === "array")	unset($routes[$option]);
			}
		}
		
		/** A rota é armazenada na variável $route e contém todos os dados processados até aqui.
		 * Caso se tenha configurado uma view e/ou um controller diretamente na rota, estes são
		 * removidos da array.*/
		$route = keyExists($method, $routes, $routes);
		
		if(!empty(array_filter($route))){
			unset($route["controller"]);
			unset($route["view"]);
		}
		
		/** Abaixo, cada um dos dados processados são armazenados em atributos de uma classe
		 * StdClass que será a forma como estes dados serão utilizados nas outras partes do
		 * microframwork.*/
		if($this->inheritanceConfig["cache"] === FALSE){
			$this->cache = GALASTRI["cache"];
		} else {
			$cache = $this->inheritanceConfig["cache"];
			$this->cache["status"] = keyExists("status", $cache, GALASTRI["cache"]["status"]);
			$this->cache["expire"] = keyExists("expire", $cache, GALASTRI["cache"]["expire"]);
		}
		$this->controller		= $controller;
		$this->method			= ltrim($method,"@");
		$this->view				= $view;
		$this->parameters		= $parameters;
		$this->path				= $routePath;
		$this->offline			= $this->inheritanceConfig["offline"];
		$this->authTag			= $this->inheritanceConfig["authTag"];
		$this->onAuthFail		= keyExists("onAuthFail", $route, $this->inheritanceConfig["onAuthFail"]);
		$this->urlString		= $urlString;
		$this->title			= keyExists("title", $route, "");
		$this->template			= keyExists("template", $route, []);
		$this->import			= keyExists("import", $route, []);
		$this->renderer			= keyExists("renderer", $route, FALSE);
		$this->downloadable		= $this->inheritanceConfig["downloadable"];
	}
	
	/**
	 * Método responsável por verificar se existem configurações herdáveis. Caso sim, então a
	 * configuração é ativada para todas as áreas e páginas filhas.
	 * 
	 * @param string $routeArray		Armazena a array com os dados da área a ser verificada.
	 */
	private function checkInheritanceData($routeArray){
		foreach($this->inheritanceConfig as $config => &$value){
			if(array_key_exists($config, $routeArray)){
				$value = $routeArray[$config];
			}
		}
	}
}