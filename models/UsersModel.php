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
    
}