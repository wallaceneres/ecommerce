<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use Hcode\Model\User;

class Cart extends Model
{

	const SESSION = "Cart";

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

		$results = $sql->select("SELECT  FROM tb_carts WHERE dessessionid = :dessessionid",[
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
		
		$results = $sql->select("SELECT  FROM tb_carts WHERE idcart = :idcart",[
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

}
?>