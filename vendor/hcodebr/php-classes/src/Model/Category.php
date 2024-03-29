<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class Category extends Model
{
	//lista todas as categorias
	public static function listAll()
	{

		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_categories ORDER BY descategory");

	}

	//salva a nova categoria
	public function save()
	{
		$sql = new sql();

		$results = $sql->select("CALL sp_categories_save(:idcategory, :descategory)",[
			":idcategory"=>$this->getidcategory(),
			":descategory"=>$this->getdescategory()
		]);

		$this->setData($results[0]);

		Category::updateFile();

	}

	//carrega a categoria no objeto
	public function get($idcategory)
	{
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory",[
			":idcategory"=>$idcategory
		]);
		if (empty($results))
		{
			header("Location: /admin/categories");
			exit;
		}else{
			$this->setData($results[0]);
		}

	}

	//deleta a categoria
	public function delete()
	{

		$sql = new Sql();
		
		$sql->query("DELETE FROM tb_categories where idcategory = :idcategory",[
			":idcategory"=>$this->getidcategory()
		]);

		Category::updateFile();
	}

	public static function updateFile()
	{
		$categories = Category::listAll();

		$html = [];

		foreach ($categories as $row)
		{

			array_push ($html, '<li><a href="/categories/' . $row['idcategory'] . '">' . $row['descategory'] . '</a></li>');

		}

		file_put_contents($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . "categories-menu.html", implode('', $html));
	}

	public function getProducts($related = true)
	{

		$sql = new Sql();

		if($related === true)
		{
			return $sql->select("
			SELECT * FROM tb_products where idproduct IN( 
				SELECT a.idproduct
				FROM tb_products a
				INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
				where b.idcategory = :idcategory
			);",[
				":idcategory"=>$this->getidcategory()
			]);
		}else{
			return $sql->select("
			SELECT * FROM tb_products where idproduct NOT IN( 
				SELECT a.idproduct
				FROM tb_products a
				INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
				where b.idcategory = :idcategory
			);",[
				":idcategory"=>$this->getidcategory()
			]);
		}

	}

	public function addProduct(Product $product)
	{

		$sql = new Sql();

		$sql->query("INSERT INTO tb_productscategories (idcategory, idproduct) VALUES (:idcategory, :idproduct)",[
			":idcategory"=>$this->getidcategory(),
			":idproduct"=>$product->getidproduct()
		]);


	}

	public function removeProduct(Product $product)
	{

		$sql = new Sql();

		$sql->query("DELETE FROM tb_productscategories WHERE idcategory = :idcategory AND idproduct = :idproduct",[
			":idcategory"=>$this->getidcategory(),
			":idproduct"=>$product->getidproduct()
		]);


	}

	public function getProductsPage($page = 1, $itemsPerPage = 4)
	{

		$start = ($page - 1) * $itemsPerPage;

		$sql = new Sql();

		$results = $sql->select("
			SELECT SQL_CALC_FOUND_ROWS *
			FROM tb_products a
			INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
			INNER JOIN tb_categories c ON c.idcategory = b.idcategory
			WHERE c.idcategory = :idcategory
			LIMIT $start, $itemsPerPage;
		",[
			":idcategory"=>$this->getidcategory()
		]);

		$resultTotal = $sql->select("SELECT FOUND_ROWS() as nrtotal");

		return [
			'data'=>Product::checkList($results),
			'total'=>(int)$resultTotal[0]['nrtotal'],
			'pages'=>ceil($resultTotal[0]['nrtotal'] / $itemsPerPage)
		];

	}
}
?>