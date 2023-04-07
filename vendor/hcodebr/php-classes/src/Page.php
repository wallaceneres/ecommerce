<?php

namespace Hcode;

use Rain\Tpl;

class Page
{

	//variavel para instanciar a classe TPL
	private $tpl;
	//array para receber os opcionais passados para a classe page
	private $options = [];
	//array com as configurações padroes para exibicao de header e footer
	private $defaults = [
		"header"=>true,
		"footer"=>true,
		"data"=>[]
	];

	//funcao para imprimir o header na pagina, $opts = para receber os dados e tpl_dir para receber o caminho das views
	public function __construct($opts = array(), $tpl_dir = "/views/")
	{
		//realiza um merge dos arrays para utilizar os parametros passados pelo array options ao inves dos defaults
		$this->options = array_merge($this->defaults, $opts);

		//configura os diretorios
		$config = array(
			"tpl_dir" => $_SERVER["DOCUMENT_ROOT"].$tpl_dir,
			"cache_dir" => $_SERVER["DOCUMENT_ROOT"]."/views-cache/",
			"debug" => false
		);
		//executa as configuracoes
		Tpl::configure( $config );
		$this->tpl = new Tpl;
		$this->setData($this->options["data"]);
		//verifica se precisa imprimir o cabealho da pagina
		if($this->options["header"]===true){
			$this->tpl->draw("header");
		}
	}

	private function setData($data = array())
	{
		foreach ($data as $key => $value) {
			//vincula as chaves aos valores
			$this->tpl->assign($key, $value);
		}
	}

	public function setTpl($name, $data = array(), $returnHTML = false)
	{
		$this->setData($data);
		//imprime o layout na tela
		return $this->tpl->draw($name, $returnHTML);
	}


	//funcao para imprimir o footer na pagina
	public function __destruct()
	{
		//verifica se precisa exibir o footer na pagina
		if($this->options["footer"]===true) {
			$this->tpl->draw("footer");
		}
	}
}