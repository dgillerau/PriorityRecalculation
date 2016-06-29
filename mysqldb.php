<?php
//class to communicate with bd
class dbconn
{
    private $db_host = "localhost";
    private $db_user = "root";
    private $db_pass = "";
    private $db_name = "priorityrecalculation";
    private $cn_state;
    private $conn;
    private $result = array();
    public $numResults;

    public function connect()
        {
            $this->conn = new mysqli($this->db_host, $this->db_user, $this->db_pass, $this->db_name);
            if ($this->conn->connect_error) {
                $this->cn_state=false;
                die("Connection failed: " . $this->conn->connect_error);
            } else {
                $this->cn_state=true;
                return true;
            }
        }

    public function disconnect()
        {
            if($this->cn_state)
            {
                mysqli_close($this->conn);
            }
        }

    private function tableExists($table)
    {

        $tablesInDb = @mysqli_query($this->conn,'SHOW TABLES FROM '.$this->db_name.' LIKE "'.$table.'"');
        //echo 'SHOW TABLES FROM '.$this->db_name.' LIKE "'.$table.'"';
        //echo mysqli_num_rows($tablesInDb);

        if(mysqli_num_rows($tablesInDb)==1){
            return true;
        }else{
            return false;
        }

    }

    public function getResult()
    {
        return $this->result;
    }


    public function delete($table)
    {
        $sql = "delete from ".$table;
        if ($this->conn->query($sql) === TRUE) {
            //echo "New record created successfully";
        } else {
            echo "Error: " . $sql . "<br>" . $this->conn->error;
        }
    }

    public function insert($table,$values,$rows = null)
    {

        if($this->tableExists($table) == true)
        {
            $insert = 'INSERT INTO '.$table;
            //echo $insert;
            if($rows != null)
            {
                $insert .= ' ('.$rows.')';
            }

            for($i = 0; $i < count($values); $i++)
            {
                if(is_string($values[$i]))
                    $values[$i] = '"'.$values[$i].'"';
            }
            $values = implode(',',$values);
            $insert .= ' VALUES ('.$values.')';
            //echo $insert."<br>";
            $ins = @mysqli_query($this->conn,$insert);
            if($ins)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }

    public function select($table, $rows = '*', $where = null, $group = null, $order = null)
    {
        $q = 'SELECT '.$rows.' FROM '.$table;
        if($where != null)
            $q .= ' WHERE '.$where;
        if($group != null)
            $q .= ' GROUP BY '.$group;
        if($order != null)
            $q .= ' ORDER BY '.$order;

        //echo $q;
        if($this->tableExists($table))
        {
            $query = @mysqli_query($this->conn,$q);
            if($query)
            {
                $this->numResults = mysqli_num_rows($query);
                for($i = 0; $i < $this->numResults; $i++)
                {
                    $r = mysqli_fetch_array($query);
                    $key = array_keys($r);
                    for($x = 0; $x < count($key); $x++)
                    {
                        // Sanitizes keys so only alphavalues are allowed
                        if(!is_int($key[$x]))
                        {
                            if(mysqli_num_rows($query) > 1)
                                $this->result[$i][$key[$x]] = $r[$key[$x]];
                            else if(mysqli_num_rows($query) < 1)
                                $this->result = null;
                            else
                                $this->result[$key[$x]] = $r[$key[$x]];
                        }
                    }
                }
                return true;
            }
            else
            {
                return false;
            }
        }
        else
            return false;
    }

    public function execute($proc, $parameters = null)
    {
        $q = 'CALL '.$proc;
        if($parameters != null)
            $q .= ' '.$parameters;

        echo $q;
        @mysqli_query($this->conn,$q);

    }

    public function update($table,$rows,$where)
    {
        if($this->tableExists($table))
        {
            // Parse the where values
            // even values (including 0) contain the where rows
            // odd values contain the clauses for the row
            //echo count($where);
            //echo $where[0];
            //echo $where[1];

            $ct = count($where);

            for($i = 0; $i < $ct; $i++)
            {
                if($i%2 != 0)
                {
                    if(is_string($where[$i]))
                    {
                        if(($i+1) < $ct)
                            $where[$i] = '="'.$where[$i].'" AND ';
                        else
                            $where[$i] = '="'.$where[$i].'"';
                    }
                    else
                    {
                        if(($i+1) < $ct)
                            $where[$i] = '='.$where[$i]. ' AND ';
                        else
                            $where[$i] = '='.$where[$i];
                    }
                }
            }
            $where = implode('',$where);


            $update = 'UPDATE '.$table.' SET ';
            $keys = array_keys($rows);
            for($i = 0; $i < count($rows); $i++)
            {
                if(is_string($rows[$keys[$i]]))
                {
                    $update .= $keys[$i].'="'.$rows[$keys[$i]].'"';
                }
                else
                {
                    $update .= $keys[$i].'='.$rows[$keys[$i]];
                }

                // Parse to add commas
                if($i != count($rows)-1)
                {
                    $update .= ',';
                }
            }
            $update .= ' WHERE '.$where;
            //echo $update;
            $query = @mysqli_query($this->conn,$update);
            if($query)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }
}

?>