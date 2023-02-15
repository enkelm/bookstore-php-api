<?php

namespace Models;

use PDO;
use PDOException;

class BaseModel extends DatabaseCtx
{
    protected string $tableName;
    protected array $fields;

    public function __construct(string $tableName)
    {
        parent::__construct();
        $this->tableName = $tableName;

        $statement = $this->db->prepare("DESCRIBE $this->tableName");
        try {
            $statement->execute();
        } catch (PDOException $e) {
            echo "Error Message: " . $e->getCode() . $e->getMessage();
        }
        $this->fields = $statement->fetchAll(PDO::FETCH_COLUMN);
        $statement->closeCursor();
    }

    public function getAll()
    {
        $query = "SELECT * FROM $this->tableName";
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

    public function fetchBy(array $conditions): array
    {
        $fieldsConditions = [];
        $query = "SELECT * FROM $this->tableName WHERE";
        foreach ($conditions as $key => $value) {
            if (in_array($key, $this->fields)) {
                $fieldsConditions[$key] = $value;
                $query .= " $key=:$key AND";
            }
        }
        $query = substr($query, 0, strlen($query) - 4);

        $statement = $this->db->prepare($query);

        try {
            $statement->execute($fieldsConditions);
        } catch (PDOException $e) {
            throw $e;
        }

        $results =  $statement->fetchAll(PDO::FETCH_ASSOC);
        $statement->closeCursor();
        return $results;
    }

    public function insert(array $insertValues)
    {
        $fieldsValues = [];
        $query = "INSERT INTO $this->tableName (";
        foreach ($insertValues as $key => $value) {
            if (in_array($key, $this->fields)) {
                $fieldsValues[$key] = $value;
                $query .= "$key,";
            }
        }
        $query = substr($query, 0, strlen($query) - 1);
        $query .= " ) VALUES (";
        foreach ($fieldsValues as $key => $value) {
            $query .= " :$key,";
        }
        $query = substr($query, 0, strlen($query) - 1);
        $query .= " )";

        $statement = $this->db->prepare($query);
        try {
            $statement->execute($fieldsValues);
        } catch (PDOException $e) {
            throw $e;
        }
        $statement->closeCursor();
        return $this->db->lastInsertId();
    }


    public function update(array $updateValues, array $conditions)
    {
        $fieldConditions = [];
        $cnt = 0;
        $query = "UPDATE " . $this->tableName . " SET ";
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

    public function delete(array $conditions)
    {
        $fieldsConditions = [];
        $query = "DELETE FROM $this->tableName WHERE";
        foreach ($conditions as $key => $value) {
            if (in_array($key, $this->fields)) {
                $fieldsConditions[$key] = $value;
                $query .= " $key=:$key AND";
            }
        }
        $query = substr($query, 0, strlen($query) - 4);

        $statement = $this->db->prepare($query);
        try {
            $statement->execute($fieldsConditions);
        } catch (PDOException $e) {
            throw $e;
        }
        $statement->closeCursor();
        return true;
    }
}
