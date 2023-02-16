<?php

namespace Controllers;

define("SECRET_KEY", "test123");

use Error;
use Models\UsersModel;
use Models\PurchasesModel;
use Models\PurchasedItemsModel;

class AdminController extends BaseController
{
    private $requestMethod;
    private $purchasedItemsModel;
    private $purchasesModel;
    private $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->purchasesModel = new PurchasesModel;
        $this->purchasedItemsModel = new PurchasedItemsModel;
        $this->userModel = new UsersModel;
    }

    public function getAllAction()
    {
        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];

        if ($this->validateToken('ADMIN')) {
            if (strtoupper($requestMethod) == 'GET') {
                try {
                    $userModel = new UsersModel();

                    $arrUsers = $userModel->getAll();

                    $responseData = $arrUsers;
                } catch (Error $e) {
                    $strErrorDesc = $e->getMessage() . 'Something went wrong! Please contact support.';
                    $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
                }
            } else {
                $strErrorDesc = 'Method not supported';
                $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
            }
        } else {
            $strErrorDesc = 'User not authorized';
            $strErrorHeader = 'HTTP/1.1 402 Not Authorized';
        }

        // send output
        if (!$strErrorDesc) {
            $this->sendOutput(
                $responseData,
                array('Content-Type: application/json', 'HTTP/1.1 200 OK')
            );
        } else {
            $this->sendOutput(
                json_encode(array('error' => $strErrorDesc)),
                array('Content-Type: application/json', $strErrorHeader)
            );
        }
    }

    function deleteAction()
    {
        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];

        if ($this->validateToken('ADMIN')) {
            if (strtoupper($requestMethod) == 'POST') {
                try {
                    $lalalal=[];
                    $data = (array) json_decode(file_get_contents('php://input'), TRUE); 
                    $lalalal= $this->userModel->getPurchaseId($data["Id"]);

                    foreach($lalalal as $key => $value){
                        $result = $this->purchasedItemsModel->delete(["Purchase" => $value]);
                    }
                    $result = $this->purchasesModel->delete(["User" => $data["Id"]]);
                    $result = $this->userModel->delete(["Id" => $data["Id"]]);
                    $responseData = $result;
                } catch (Error $e) {
                    $strErrorDesc = $e->getMessage() . 'Something went wrong! Please contact support.';
                    $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
                }
            } else {
                $strErrorDesc = 'Method not supported';
                $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
            }
        } else {
            $strErrorDesc = 'User not authorized';
            $strErrorHeader = 'HTTP/1.1 402 Not Authorized';
        }

        // send output
        if (!$strErrorDesc) {
            $this->sendOutput(
                $responseData,
                array('Content-Type: application/json', 'HTTP/1.1 200 OK')
            );
        } else {
            $this->sendOutput(
                json_encode(array('error' => $strErrorDesc)),
                array('Content-Type: application/json', $strErrorHeader)
            );
        }
    }
}
