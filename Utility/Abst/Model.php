<?php
/**
 * Created by PhpStorm.
 * User: sinchan
 * Date: 2018/12/14
 * Time: 21:14
 */

namespace App\Infrastructure\Utility\Abst;

use App\Infrastructure\Utility\Pool\MysqlObject;

class Model
{
    private $db;

    public function __construct(MysqlObject $mysqlObject)
    {
        $this->db = $mysqlObject;
    }

    function getDbConnection():MysqlObject{
        return $this->db;
    }

}