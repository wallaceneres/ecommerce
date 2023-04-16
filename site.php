<?php

use Hcode\Page;
use Hcode\Model\Category;
use Hcode\Model\Product;

$app->get('/',function()
{
	$products = Product::listAll();

	$page = new Page();

	$page->setTpl("index",[

		"products"=>Product::checklist($products)

	]);

});

$app->get("/categories/:id",function($idcategory)
{

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new Page();

	$page->setTpl("category",[
		"category"=>$category->getValues(),
		"products"=>Product::Checklist($category->getProducts())
	]);

});

?>