<?php

namespace Models;

use PDO;
use PDOException;

class PurchasedItemsModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct("purchaseditems");
    }

    //array me purchase items
    public function getPurchaseInfo($purchaseId)
    {
        $query = "SELECT purchaseditems.Id,products.Title,products.Author,purchaseditems.Price,purchaseditems.Quantity 
        FROM purchaseditems 
        INNER JOIN purchases on purchaseditems.Purchase = purchases.Id 
        INNER JOIN products on purchaseditems.Product = products.Id 
        WHERE purchases.Id = " . $purchaseId;
        $statement = $this->db->prepare($query);
        try {
            $statement->execute();
        } catch (PDOException $e) {
            throw $e;
        }

        $results =  $statement->fetchAll(PDO::FETCH_ASSOC);
        $statement->closeCursor();
        return $results;
    }
}
