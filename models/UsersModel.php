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

    function getPurchaseId($userId)
    {
        $query = "SELECT purchases.Id 
        FROM purchases
        WHERE purchases.User = " . $userId;
        $statement = $this->db->prepare($query);
        try {
            $statement->execute();
        } catch (PDOException $e) {
            throw $e;
        }

        $results = $statement->fetch(PDO::FETCH_ASSOC);
        $statement->closeCursor();
        return $results;
    }
}