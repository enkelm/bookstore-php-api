<?php

namespace Controllers;

use Error;
use Exception;
use InvalidArgumentException;
use Models\ProductsModel;
use Models\PurchasedItemsModel;
use Util\Helpers;

class ProductsController extends BaseController
{
    private $productsModel;
    private $requestMethod;
    private $purchasedItemsModel;

    public function __construct()
    {
        parent::__construct();
        $this->productsModel = new ProductsModel;
        $this->purchasedItemsModel = new PurchasedItemsModel;
    }

    public function addAction()
    {

        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];

        if ($this->validateToken('ADMIN')) {
            if (strtoupper($requestMethod) == 'POST') {
                try {
                    $inputProduct = (array) $_POST;

                    // Randomizes file name
                    $fileName = explode(".", $inputProduct['CoverImageUrl']);
                    $fileName = Helpers::generateRandomString(20) . '.' . $fileName[sizeof($fileName) - 1];
                    $inputProduct['CoverImageUrl'] = $fileName;
                    $inputProduct['CreatedAt'] = date('d-m-y h:i:s');

                    //Generate Image URL
                    $path = str_replace('controllers', 'images\\', __DIR__) . $inputProduct['CoverImageUrl'];
                    $inputProduct['CoverImageUrl'] = "http://localhost/bookstore-php-api/images/" . $inputProduct['CoverImageUrl'];

                    // Save File
                    $inputProduct['CoverImage'] = (array) $_FILES;
                    $inputProduct['CoverImage']['CoverImage']['name'] = $fileName;
                    if (is_uploaded_file($inputProduct['CoverImage']['CoverImage']['tmp_name'])) {
                        move_uploaded_file($inputProduct['CoverImage']['CoverImage']['tmp_name'], $path);
                    }
                    unset($inputProduct['CoverImage']);

                    $this->productsModel->insert($inputProduct);

                    $responseData = $inputProduct;
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


    public function getAllAction()
    {
        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];

        if (strtoupper($requestMethod) == 'GET') {
            try {
                $products = $this->productsModel->getAll();

                $responseData = $products;
            } catch (Error $e) {
                $strErrorDesc = $e->getMessage() . 'Something went wrong! Please contact support.';
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
                array('Content-Type: application/json', 'HTTP/1.1 200 OK')
            );
        } else {
            $this->sendOutput(
                json_encode(array('error' => $strErrorDesc)),
                array('Content-Type: application/json', $strErrorHeader)
            );
        }
    }

    function putAction()
    {
        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];

        if ($this->validateToken('ADMIN')) {
            if (strtoupper($requestMethod) == 'POST') {
                try {
                    $data = (array) json_decode(file_get_contents('php://input'), TRUE);
                    $conditions = array('Id' => $data["Id"]);
                    $result = $this->productsModel->update($data, $conditions);
                    $responseData = $data;
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
                    $result = $this->purchasedItemsModel->delete(["Product" => $data["Id"]]);
                    $result = $this->productsModel->delete($data);
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
