<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class Product extends Model
{
	//lista todas os produtos
	public static function listAll()
	{

		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_products ORDER BY desproduct");

	}

	public static function checkList($list)
	{

		foreach ($list as &$row)
		{

			$p = new Product();
			$p->setData($row);
			$row = $p->getValues();
		}

		return $list;

	}

	//salva o novo produto
	public function save()
	{
		$sql = new sql();

		$results = $sql->select("CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl)",[
			":idproduct"=>$this->getidproduct(),
			":desproduct"=>$this->getdesproduct(),
            ":vlprice"=>$this->getvlprice(),
            ":vlwidth"=>$this->getvlwidth(),
            ":vlheight"=>$this->getvlheight(),
            ":vllength"=>$this->getvllength(),
            ":vlweight"=>$this->getvlweight(),
            ":desurl"=>$this->getdesurl()
		]);

		$this->setData($results[0]);
	}

	//carrega o produto no objeto
	public function get($idproduct)
	{
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct",[
			":idproduct"=>$idproduct
		]);
		if (empty($results))
		{
			header("Location: /admin/products");
			exit;
		}else{
			$this->setData($results[0]);
		}
	}

	//deleta o produto
	public function delete()
	{

		$sql = new Sql();
		
		$sql->query("DELETE FROM tb_products where idproduct = :idproduct",[
			":idproduct"=>$this->getidproduct()
		]);
	}

	//metodo para verificar se o produto existe no diretorio e definir qual url carregar no serdesphoto
	public function checkPhoto()
	{

		if (file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 
		'res' . DIRECTORY_SEPARATOR . 
		'site' . DIRECTORY_SEPARATOR . 
		'img' . DIRECTORY_SEPARATOR . 
		'products' . DIRECTORY_SEPARATOR .
		$this->getidproduct() . '.jpg'
	)){

		$url = "/res/site/img/products/" . $this->getidproduct() . ".jpg";

	}else{

		$url = "/res/site/img/products/product.jpg";
	}

	return $this->setdesphoto($url);

	}

	public function getValues()
	{

		$this->checkPhoto();

		$values = parent::getValues();

		return $values;

	}


	//metodo para adicionar a imagem do produto no diretório
	public function setPhoto($file)
	{
		//se nenhuma imagem for inserida ele ignora o restante do código
		if($file["size"] === 0)
		{
			$this->checkPhoto();
		}else{

			$extension = explode('.', $file['name']);

			$extension = end($extension);

			switch($extension)
			{

				case "jpg":
				case "jpeg":
					$image = imagecreatefromjpeg($file["tmp_name"]);
				break;
				case "gif":
					$image = imagecreatefromgif($file["tmp_name"]);
				break;
				case "png":
					$image = imagecreatefrompng($file["tmp_name"]);
				break;

			}

			$dist = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 
			'res' . DIRECTORY_SEPARATOR . 
			'site' . DIRECTORY_SEPARATOR . 
			'img' . DIRECTORY_SEPARATOR . 
			'products' . DIRECTORY_SEPARATOR .
			$this->getidproduct() . '.jpg';
			
			//se o arquivo ja existir ele é excluído
			if(file_exists($dist)){
				unlink($dist);
			}

			imagejpeg($image, $dist);

			imagedestroy($image);

			$this->checkPhoto();
		}

	}

}

?>