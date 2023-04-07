<?php

namespace Hcode;

class PageAdmin extends Page
{
	//altera o diretorio das views
	public function __construct($opts = array(), $tpl_dir = "/views/admin/")
	{
		//utiliza o método construct da classe pai apenas alterando os parametros.
		parent::__construct($opts, $tpl_dir);

	}

}