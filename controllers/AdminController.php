<?php

namespace Controllers;

define("SECRET_KEY", "test123");

use Error;
use Models\UsersModel;
use Models\PurchasesModel;

class AdminController extends BaseController
{
    private $requestMethod;

    private $purchasesModel;
    private $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->purchasesModel = new PurchasesModel;
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
                    $data = (array) json_decode(file_get_contents('php://input'), TRUE);
                    $result = $this->purchasesModel->delete(["Purchase" => $data["Id"]]);//product with that id
                    $result = $this->userModel->delete($data);//user with that id
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
