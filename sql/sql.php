<?php
require_once "modelo.php";

class sql extends Modelo
{
    public function sql_s($sql)
    {
        parent::c();
        $result = $this->_db->query($sql);
        if($result)
        {
        $response = array();
        while($row = $result->fetch_assoc())
        {
            $response[]=array_map('utf8_encode',$row);
        }
        return $response;
        }
        else {
            print_r($this->_db->error_list);
            return "";
        }
    }
    public function sql_i($sql,$comand="NULL")
    {
        parent::c();
        $result = $this->_db->query($sql);
        if($result)
        {
            if($comand == "DEV")
            {
                printf("%d Row inserted.\n", $this->_db->affected_rows);
            }
        return $result;
        }
        else {
            print_r($this->_db->error_list);
            return "";
        }
    }
    public function sql_id($sql)
    {
        parent::c();
        $result = $this->_db->query($sql);
        if($result)
        {
        return $this->_db->insert_id;
        }
        else {
            print_r($this->_db->error_list);
            return "";
        }
    }
    public function sql_rows($sql)
    {
        parent::c();
        $result = $this->_db->query($sql);
        if($result)
        {
            return $this->_db->affected_rows;
        }
        else {
            print_r($this->_db->error_list);
            return "";
        }
    }
}
?>