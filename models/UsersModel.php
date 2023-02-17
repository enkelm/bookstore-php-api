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

    function emailExists($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE Email = :email");

        // bind the email parameter to the query
        $stmt->bindParam(':email', $email);

        // execute the query
        $stmt->execute();

        // check if any rows were returned
        if ($stmt->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    function updateUser($updateValues, $conditions)
    {

        if ($updateValues['PasswordHash'] !== '') {
            $hash = password_hash($updateValues['PasswordHash'], PASSWORD_DEFAULT);
            $updateValues['PasswordHash'] = $hash;
        } else {
            unset($updateValues['PasswordHash']);
        }

        $fieldConditions = [];
        $cnt = 0;
        $query = "UPDATE users SET ";
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
