<?php

namespace Models;

use PDO;
use PDOException;

class PurchasesModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct("purchases");
    }
    public function getCount()
    {
        $query = "SELECT COUNT(*) FROM " . $this->tableName;
        $statement = $this->db->prepare($query);
        try {
            $statement->execute();
        } catch (PDOException $e) {
            throw $e;
        }

        $results =  $statement->fetch(PDO::FETCH_ASSOC);
        $statement->closeCursor();
        return $results;
    }

    function getInfo($purchaseId)
    {
        $query = "SELECT purchases.Id,users.FirstName,users.LastName,users.Username,purchases.TotalPrice,purchases.CreatedAt
        from purchases inner join users on purchases.User = users.Id
        WHERE purchases.Id = " . $purchaseId;
        $statement = $this->db->prepare($query);
        try {
            $statement->execute();
        } catch (PDOException $e) {
            throw $e;
        }

        $results =  $statement->fetch(PDO::FETCH_ASSOC);
        $statement->closeCursor();
        return $results;
    }
}
