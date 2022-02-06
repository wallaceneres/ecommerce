<?php

use \Hcode\Page;

$app->get('/', function() {
	//instancia uma nova pagina de usuario padrao
	$page = new Page();

	$page->setTpl("index");

});

?>