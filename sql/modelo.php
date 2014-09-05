<?php
require_once dirname(__FILE__)."/../bbdd/config.php";

class Modelo
{
    protected $_db;

    public function c()
    {
        $this->_db = new mysqli(MYSQL_HOSTNAME, MYSQL_USERNAME, MYSQL_PASSWORD, MYSQL_DATABASE);

        if ( $this->_db->connect_errno )
        {
            echo "Fallo al conectar a MySQL: ". $this->_db->connect_error;
            return;
        }

        $this->_db->set_charset(MYSQL_CHARSET);
    }
}
?> 