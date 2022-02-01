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

//rota get para index
$app->get('/', function() {
	//instancia uma nova pagina de usuario padrao
	$page = new Page();

	$page->setTpl("index");

});
//rota get para index de administrador
$app->get('/admin', function() {

	User::verifyLogin();
    
	$page = new PageAdmin();

	$page->setTpl("index");

});
//rota get para login de administrador
$app->get('/admin/login', function() {
    
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("login");

});
//rota post para login de administrador 
$app->post('/admin/login', function() {
    
	User::login($_POST["login"], $_POST["password"]);

	header("Location: /admin");
	exit;

});
//rota get para logout de administrador
$app->get('/admin/logout', function() {

	User::logout();

	header("Location: /admin/login");
	exit;

});
//rota get para lista de usuarios
$app->get('/admin/users', function(){

	User::verifyLogin();

	$users = User::listAll();

	$page = new PageAdmin();

	$page->setTpl("users", array(

		"users"=>$users

	));

});
//rota get para criacao de usuarios
$app->get('/admin/users/create', function(){

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("users-create");

});
//rota get para index
$app->get("/admin/users/:iduser/delete", function($iduser){

	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$user->delete();

	header("Location: /admin/users");
	exit;

});

$app->get("/admin/users/:iduser", function($iduser){

	User::verifyLogin();

	$user = new User();

	$user->get((int)$iduser);

	$page = new PageAdmin();

	$page->setTpl("users-update",array(
		"user"=>$user->getValues()
	));

});

$app->post("/admin/users/create", function(){

	User::verifyLogin();

	$user = new User();

	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

	$user->setData($_POST);

	$user->save();

	header("Location: /admin/users");
	exit;

});

$app->post("/admin/users/:iduser", function($iduser){

	User::verifyLogin();

	$user = new user();

	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

	$user->get((int)$iduser);

	$user->setData($_POST);

	$user->update();

	header("Location: /admin/users");
	exit;

});

$app->get("/admin/forgot", function(){

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot");

});

/*$app->post("/admin/forgot",function(){

	$user = User::getForgot($_POST["email"]);  

	header("Location: /admin/forgot/sent");
	exit;

});*/

$app->get("/admin/forgot/sent",function()
{

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-sent");


});

$app->get("/admin/categories",function(){

	User::verifyLogin();

	$categories = Category::listAll();

	$page = new PageAdmin();

	$page->setTpl("categories", [

		'categories' => $categories

	]);


});

$app->get("/admin/categories/create",function(){

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("categories-create");


});

$app->post("/admin/categories/create",function(){

	User::verifyLogin();

	$category = new Category();

	$category->setData($_POST);
	
	$category->save();

	header("Location: /admin/categories");
	exit;

});

$app->get("/admin/categories/:idcategory/delete", function($idcategory){

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$category->delete();

	header("Location: /admin/categories");
	exit;

});

$app->get("/admin/categories/:idcategory", function($idcategory){

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new PageAdmin();

	$page->setTpl("categories-update", [

		'category'=>$category->getValues()

	]);

});

$app->post("/admin/categories/:idcategory", function($idcategory){

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$category->setData($_POST);

	$category->save();

	header('Location: /admin/categories');
	exit;

});

$app->get("/categories/:idcategory", function($idcategory)
{

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new Page();

	$page->setTpl("category", [

		'category'=>$category->getValues(),
		'products'=>[]

	]);

});

$app->run();

 ?>