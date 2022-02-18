<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class Cart extends Model
{

    const SESSION = "Cart";

    public static function getFromSession(){

        $cart = new Cart();

       TO DO if (isser($_SESSION[Cart::SESSION]) && )
        {

        }

    }

    public function save()
    {

        $sql = new Sql();

        $results = $sql->select("CALL sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays",[

            ":idcart"=>$this->getidcart(),
            ":dessessionid"=>$this->getdessessionid(),
            ":iduser"=>$this->getiduser(),
            ":deszipcode"=>$this->deszipcode(),
            ":vlfreight"=>$this->getvlfreight(),
            ":nrdays"=>$this->getnrdays()

        ]);

        $this->setData($results[0]);

    }

}

?>