<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class User extends Model
{

	const SESSION = "User";

	public static function login($login, $password)
	{

		$sql = new Sql();
		//realiza uma consulta no banco
		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
			":LOGIN"=>$login
		));
		//se o resultado do array for 0 é exibida uma exception e encerra a execucao do codigo
		if(count($results) === 0 )
		{
			throw new \Exception("Usuário inexistente ou senha inválida");
		}
		//caso o resultado for maior que 0, grava a posicao 0 na variavel data
		$data = $results[0];

		//se a senha digitada for igual a senha armazenada, instancia a classe usuario
		//if (password_verify($password, $data["despassword"]) === true)
		//{
			if($password === $data["despassword"]){

			$user = new User();
			//insere os dados do usuario atraves de setters
			$user->setData($data);
			//registra a sessão com os dados do usuario
			$_SESSION[User::SESSION] = $user->getValues();
			//retorna o usuario
			return $user;

		}else
		//se a senha estiver incorreta, exibe outro exception
		{
			throw new \Exception("Usuario inexistente ou senha inválida");
		}
	}

	public static function verifyLogin($inadmin = true)
	{
		if(!isset($_SESSION[User::SESSION])
		||
		!$_SESSION[User::SESSION]
		||
		!(int)$_SESSION[User::SESSION]["iduser"] > 0
		||
		(bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin)
		{
			header("Location: /admin/login");
			exit;
		}
	}

	public static function logout()
	{

		$_SESSION[User::SESSION] = null;

	}

	public static function listAll(){

		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");

	}

	public function save()
	{

		$sql = new Sql();

		$results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)",array(
			":desperson"=>$this->getdesperson(),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>$this->getdespassword(),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()
		));

		$this->setData($results[0]);


	}

	public function deleteUser($iduser)
	{
		$sql = new Sql();

		return $sql->query("delete from tb_users where iduser= :IDUSER",[
			":IDUSER"=>$iduser
			]);
	}

}