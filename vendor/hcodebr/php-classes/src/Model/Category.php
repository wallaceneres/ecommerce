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
}