<?php

namespace Controllers;

use Error;
use Models\RolesModel;
use Models\UsersModel;
use Util\JWT;


class AuthController extends BaseController
{
    private $usersModel;
    private $roleModel;
    private $requestMethod;

    public function __construct()
    {
        $this->usersModel = new UsersModel;
        $this->roleModel = new RolesModel;
    }

    public function signupAction()
    {
        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];

        if (strtoupper($requestMethod) == 'POST') {
            try {

                $userModel = $this->usersModel;

                $inputUser = (array) json_decode(file_get_contents('php://input'), TRUE);

                $hash = password_hash($inputUser['PasswordHash'], PASSWORD_DEFAULT);
                $inputUser['PasswordHash'] = $hash;

                $userModel->insert($inputUser);

                $responseData = $inputUser;
            } catch (Error $e) {
                $strErrorDesc = $e->getMessage() . 'Something went wrong! Please contact support';
                $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
            }
        } else {
            $strErrorDesc = 'Method not supported';
            $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
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

    public function loginAction()
    {
        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];

        if (strtoupper($requestMethod) == 'POST') {
            try {

                $userModel = $this->usersModel;

                $credentials = (array) json_decode(file_get_contents('php://input'), TRUE);

                $username = $credentials['Username'];
                $user = $userModel->fetchBy(['Username' => $username]);

                if (password_verify($credentials['PasswordHash'], $user[0]['PasswordHash'])) {
                    $role = $user[0]['Role'];
                    $role = $this->roleModel->fetchBy(['Id' => $role]);
                    $responseData = ['token' => JWT::encode($user, SECRET_KEY), 'role' => $role[0]['Name']];
                } else throw new Error('Invalid Password! ');
            } catch (Error $e) {
                $strErrorDesc = $e->getMessage() . 'Something went wrong! Please contact support';
                $strErrorHeader = 'HTTP/1.1 500 Internal Server Error';
            }
        } else {
            $strErrorDesc = 'Method not supported';
            $strErrorHeader = 'HTTP/1.1 422 Unprocessable Entity';
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
