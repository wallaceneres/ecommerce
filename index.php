<?php 

session_start();
require_once("vendor/autoload.php");
// \slim\slim para utilizar a biblioteca Slim para o gerenciamento de rotas
// \hcode\page para construcao de paginas de usuarios via rain/tpl
// \hcode\pageadmin para construcao de paginas de administradores via rain/tpl
// \hcode\model\user para utilizacao de metodos e atributos de usuarios.
use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;

//instancia um objeto para a utilizacao do slim framework
$app = new Slim();
//seleciona o metodo de debug true ou false
$app->config('debug', true);


require_once("site.php");
require_once("admin.php");
require_once("admin-users.php");
require_once("admin-categories.php");
require_once("admin-products.php");

$app->run();

 ?>