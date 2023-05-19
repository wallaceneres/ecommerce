<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class User extends Model
{

	const SESSION = "User";
	const SECRET = "HcodePhp7_Secret";
	const ERROR = "UserError";
	const SUCCESS = "UserSuccess";
	const ERROR_REGISTER ="UserErrorRegister";

	public static function getFromSession()
	{

		if (isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] > 0)
		{

			$user = new User();

			$user->setData($_SESSION[User::SESSION]);

		}

		return $user;

	}

	public static function checkLogin($inadmin = true)
	{

		if(
			!isset($_SESSION[User::SESSION])
			||
			!$_SESSION[User::SESSION]
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0
		){
			//Não está logado
			return false;
		}else
		{
			if($inadmin === true && (bool)$_SESSION[User::SESSION]['inadmin'] === true)
			{
				//Está logado e é administrador
				return true;
		
			} else if($inadmin === false)
			{
				//se esta logado e nao é adm retorna true
				//o metodo verifylogin realizara o cast para false para redirecionar para a pagina de login nas paginas de adm
				//else que realizara a verificacao de paginas de usuario comum (nao adm)
				return true;
			}else
			{
				return false;//retorna falso para qualquer outra condição
			}
		}


	}

	public static function setMsgError($msg)
	{
		$_SESSION[User::ERROR] = (string)$msg;
	}

	public static function clearMsgError()
	{
		$_SESSION[User::ERROR] = NULL;
	}

	public static function getMsgError()
	{
		
		$msg = (isset($_SESSION[User ::ERROR])) ? $_SESSION[User::ERROR] : '';

		User::clearMsgError();

		return $msg;

	}

	public static function setMsgSuccess($msg)
	{
		$_SESSION[User::SUCCESS] = (string)$msg;
	}

	public static function clearMsgSuccess()
	{
		$_SESSION[User::SUCCESS] = NULL;
	}

	public static function getMsgSuccess()
	{
		
		$msg = (isset($_SESSION[User ::SUCCESS])) ? $_SESSION[User::SUCCESS] : '';

		User::clearMsgSuccess();

		return $msg;

	}

	public static function setErrorRegister($msg)
	{
		$_SESSION[User::ERROR_REGISTER] = (string)$msg;
	}

	public static function clearErrorRegister()
	{
		$_SESSION[User::ERROR_REGISTER] = NULL;
	}

	public static function getErrorRegister()
	{
		
		$msg = (isset($_SESSION[User ::ERROR_REGISTER])) ? $_SESSION[User::ERROR_REGISTER] : '';

		User::clearErrorRegister();

		return $msg;

	}

	public static function login($login, $password)
	{

		$sql = new Sql();
		//realiza uma consulta no banco
		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b ON a.idperson = b.idperson WHERE a.deslogin = :LOGIN", array(
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
		if (password_verify($password, $data["despassword"]) === true)
		{
			//if($password === $data["despassword"]){
			$user = new User();

			$data['desperson'] = utf8_encode($data['desperson']);
			//insere os dados do usuario atraves de setters
			$user->setData($data);
			//registra a sessão com os dados do usuario
			$_SESSION[User::SESSION] = $user->getValues();
			//retorna o usuario
			return $user;

		}else
		//se a senha estiver incorreta, exibe outro exception
		{
			throw new \Exception("Usuario inexistente ou senha inválida---");
		}
	}

	public static function verifyLogin($inadmin = true)
	{
		if(!User::checkLogin($inadmin))
		{
			if($inadmin){
				header("Location: /admin/login");
				exit;
			}else{
				header("Location: /login");
				exit;
			}
		}
	}

	public static function logout()
	{

		session_unset();

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
			":deslogin"=>utf8_decode($this->getdeslogin()),
			":despassword"=>User::getPasswordHash($this->getdespassword()),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()
		));

		$this->setData($results[0]);


	}

	public function delete()
	{
		$sql = new Sql();

		return $sql->query("delete from tb_users where iduser= :IDUSER",[
			":IDUSER"=>$this->getiduser()
			]);
	}

	public function get($iduser)
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) where a.iduser= :IDUSER",[
			":IDUSER"=>$iduser
			]);

		$data = $results[0];
		
		$data['desperson'] = utf8_encode($data['desperson']);

		$this->setData($data);
	}

	public function update()
	{

		$sql = new Sql();

		$results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)",array(
			"iduser"=>$this->getiduser(),
			":desperson"=>utf8_decode($this->getdesperson()),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>$this->getdespassword(),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()
		));

		$this->setData($results[0]);

	}

	public static function getForgot($email)
	{

		$sql = new Sql();

		$results = $sql->select("
		
			SELECT *
			FROM tb_persons a
			INNER JOIN tb_users b USING(idperson)
			WHERE a.desemail = :email",[
				":email"=>$email
			]);

		if(count($results) === 0)
		{
			throw new \Exception ("Não foi possível recuperar a senha.");
		}
		else
		{
			$data = $results[0];
			$results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)",[
				":iduser"=>$data["iduser"],
				":desip"=>$_SERVER["REMOTE_ADDR"]
			]);

			if(count($results2) === 0)
			{
				throw new \Exception("Não foi possível recuperar a senha");
			}
			else{
				$dataRecovery = $results2[0];

				$code = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128,User::SECRET, $dataRecovery['idrecovery'],MCRYPT_MODE_ECB));
			
				$link = "http://127.0.0.1/admin/forgot/reser?code=$code";
			}


		}

	}

	public static function getPasswordHash($password)
	{

		return password_hash($password, PASSWORD_DEFAULT, [
			'cost'=>12
		]);

	}

	public static function checkLoginExist($login)
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :deslogin",[

			":deslogin"=>$login

		]);

		return (count($results) > 0);

	}

}