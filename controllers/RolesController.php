<?php

namespace Controllers;

use Error;
use Models\RolesModel;

class RolesController extends BaseController
{
    private $rolesModel;

    public function __construct()
    {
        parent::__construct();
        $this->rolesModel = new RolesModel;
    }

    public function addAction()
    {
        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];

        if ($this->validateToken('ADMIN')) {
            if (strtoupper($requestMethod) == 'POST') {
                try {
                    $inputRole = (array) json_decode(file_get_contents('php://input'), TRUE);

                    $this->rolesModel->insert($inputRole);

                    $responseData = $inputRole;
                } catch (Error $e) {
                    $strErrorDesc = $e->getMessage() . 'Something went wrong! Please contact support';
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
                array('Content-Type: application/json', 'HTTP/1.1 200 Created')
            );
        } else {
            $this->sendOutput(
                json_encode(array('error' => $strErrorDesc)),
                array('Content-Type: application/json', $strErrorHeader)
            );
        }
    }
}
