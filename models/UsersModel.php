<?php

namespace Models;
use PDO;
use PDOException;

class UsersModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct("users");
    }

    function updateUser($updateValues, $conditions){
        
        $hash = password_hash($updateValues['PasswordHash'], PASSWORD_DEFAULT);
        $updateValues['PasswordHash'] = $hash;
        
        $fieldConditions = [];
        $cnt=0;
        $query="UPDATE users SET ";
        foreach ($updateValues as $key => $value) {
            $query .= " `$key`= ?,";
        }
        $query = substr($query, 0, strlen($query) - 1);
        $query = $query . " WHERE ";
        foreach ($conditions as $key => $value) {
            $query .= " `$key`= ? AND";
        }
        $query = substr($query, 0, strlen($query) - 4);
        foreach ($updateValues as $key => $value) {
            $fieldConditions[$cnt++] = $value;
        }
        foreach ($conditions as $key => $value) {
            $fieldConditions[$cnt++] = $value;
        }
        $statement = $this->db->prepare($query);
        try {
            $statement->execute($fieldConditions);
        } catch (PDOException $e) {
            throw $e;
        }
        $statement->closeCursor();
        return true;
    }

    
}