<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use Hcode\Model\User;

class Cart extends Model
{

	const SESSION = "Cart";
	const SESSION_ERROR = "ConstError";

	public static function getFromSession()
	{

		$cart = new Cart();

		//verificar se tem a sessão e se o id do carrinho é maior que zero, caso positivo, chama a funcao get para carregar os dados do banco usando um carrinho pre existente
		if(isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]['idcart'] > 0)
		{
			$cart->get((int)$_SESSION[Cart::SESSION]['idcart']);
			
		}else
		{
			//funcao para procurar na tabela tb_carts se já existe uma session com carrinho ativo.
			$cart->getFromSessionId();
	
			//se não encontrar nenhum dado na tabela tb_carts
			if(!(int)$cart->getidcart() > 0)
			{

				$data = [

					"dessessionid"=>session_id()

				];

				if(User::checkLogin(false))
				{

					$user = User::getFromSession();
					$data['iduser'] = $user->getiduser();

				}

				$cart->setData($data);

				$cart->save();

				$cart->setToSession();

			}

		}

		return $cart;

	}

	public function setToSession()
	{
		$_SESSION[Cart::SESSION] = $this->getValues();
	}

	//metodo para pesquisar no banco se ja existe
	public function getFromSessionId()
	{
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid",[
			":dessessionid"=>session_id()
		]);

		if (count($results) > 0)
		{
			$this->setData($results[0]);
		}

	}

	//carrega os dados do carrinho recuperado da session e buscando no banco de dados
	public function get(int $idcart)
	{

		$sql = new Sql();
		
		$results = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart",[
			":idcart"=>$idcart
		]);

		if (count($results) > 0)
		{
			$this->setData($results[0]);
		}

	}
	
	public function save()
	{

		$sql = new Sql();

		$results = $sql->select("CALL sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays )",[
			":idcart"=>$this->getidcart(),
			":dessessionid"=>$this->getdessessionid(),
			":iduser"=>$this->getiduser(),
			":deszipcode"=>$this->getdeszipcode(),
			":vlfreight"=>$this->getvlfreight(),
			":nrdays"=>$this->getnrdays()
		]);

		$this->setData($results[0]);

	}

	public function addProduct(Product $product)
	{

		$sql = new Sql();

		$sql->query("INSERT INTO tb_cartsproducts (idcart, idproduct) VALUES (:idcart, :idproduct)",[
			":idcart"=>$this->getidcart(),
			":idproduct"=>$product->getidproduct()
		]);

		$this->getCalculateTotal();

	}

	public function removeProduct(Product $product, $all = false)
	{

		$sql = new Sql();

		if($all)
		{

			$sql->query("UPDATE tb_cartsproducts SET dtremoved = now() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved is NULL",[
				":idcart"=>$this->getidcart(),
				"idproduct"=>$product->getidproduct()
			]);
			
		}else
		{

			$sql->query("UPDATE tb_cartsproducts SET dtremoved = now() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved is NULL LIMIT 1",[
				":idcart"=>$this->getidcart(),
				"idproduct"=>$product->getidproduct()
			]);


		}

		$this->getCalculateTotal();
	}

	public function getProducts()
	{

		$sql = new Sql();

		$row = $sql->select("
		SELECT b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl, COUNT(*) AS nrqtd, SUM(b.vlprice) AS vltotal
		FROM tb_cartsproducts a
		INNER JOIN tb_products b ON a.idproduct = b.idproduct
		WHERE a.idcart = :idcart AND a.dtremoved IS NULL
		GROUP BY b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl
		ORDER BY b.desproduct
		",[
			"idcart"=>$this->getidcart()
		]);

		return Product::checkList($row);

	}

	public function getProductsTotals(){
		$sql = new Sql();

		$results = $sql->select("
		SELECT SUM(vlprice) AS vlprice, SUM(vlwidth) AS vlwidth, SUM(vlheight) AS vlheight,SUM(vllength) AS vllength, SUM(vlweight) AS vlweight, COUNT(*) AS nrqtd
		FROM tb_products a
		INNER JOIN tb_cartsproducts b ON a.idproduct = b.idproduct
		WHERE b.idcart = :idcart AND dtremoved IS NULL;
		",[
			":idcart"=>$this->getidcart()
		]);

		if (count($results) > 0){
			return $results[0];
		}else{
			return [];
		}
	}

	public function setFreight($nrzipcode)
	{

		$nrzipcode = str_replace('-', '', $nrzipcode);

		$totals = $this->getProductsTotals();

		if ($totals['nrqtd'] > 0)
		{

			$qs = http_build_query([

				"nCdEmpresa" => "",
				"sDsSenha" => "",
				"nCdServico" => "40010",
				"sCepOrigem" => "09750730",
				"sCepDestino" => $nrzipcode,
				"nVlPeso" => $totals['vlweight'],
				"nCdFormato" => "1",
				"nVlComprimento" => $totals['vllength'],
				"nVlAltura" => $totals['vlheight'],
				"nVlLargura" => $totals['vllength'],
				"nVlDiametro" => "0",
				"sCdMaoPropria" => "S",
				"nVlValorDeclarado" => $totals['vlprice'],
				"sCdAvisoRecebimento" => "S"

			]);

			$xml = simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?".$qs);
			
			$result = $xml->Servicos->cServico;

			if ($result->MsgErro != '')
			{
				Cart::setMsgError($result->MsgErro);
			}else
			{
				Cart::clearMsgError();
			}

			$this->setnrdays($result->PrazoEntrega);
			$this->setvlfreight(Cart::formatValueToDecimal($result->Valor));
			$this->setdeszipcode($nrzipcode);

			$this->save();

			return $result;

		}else{

		}
	}

	public static function setMsgError($msg)
	{
		$_SESSION[Cart::SESSION_ERROR] = (string)$msg;
	}

	public static function clearMsgError()
	{
		$_SESSION[Cart::SESSION_ERROR] = NULL;
	}

	public static function getMsgError()
	{
		
		$msg = (isset($_SESSION[Cart::SESSION_ERROR])) ? $_SESSION[Cart::SESSION_ERROR] : '';

		Cart::clearMsgError();

		return $msg;

	}

	public static function formatValueToDecimal($value)
	{
		$value = str_replace('.','', $value);
		return str_replace(',','.', $value);
	}

	public function updateFreight()
	{

		if($this->getdeszipcode() != '')
		{
			$this->setFreight($this->getdeszipcode());
		}

	}

	public function getValues()
	{

		$this->getCalculateTotal();

		return parent::getValues();

	}

	public function getCalculateTotal()
	{

		$this->updateFreight();

		$totals = $this->getProductsTotals();

		$this->setvlsubtotal($totals['vlprice']);
		$this->setvltotal($totals['vlprice'] + $this->getvlfreight());

	}

}
?>