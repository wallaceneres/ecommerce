<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class OrderStatus extends Model
{

    CONST EM_ABERTO =  1;
    CONST AGUARDANDO = 2;
    CONST PAGO = 3;
    CONST ENTREGUE = 4;

}

?>