<?php

namespace Controllers;

use Error;
use Exception;
use InvalidArgumentException;
use Models\ProductsModel;


class ProductsController extends BaseController
{
    private $productsModel;
    private $requestMethod;

    public function __construct()
    {
        parent::__construct();
        $this->productsModel = new ProductsModel;
    }

    public function addAction()
    {
        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];

        if ($this->validateToken(true, 'ADMIN')) {
            if (strtoupper($requestMethod) == 'POST') {
                try {

                    $inputProduct = (array) json_decode(file_get_contents('php://input'), TRUE);
                    $uri = str_replace('controllers', 'images\\', __DIR__);
                    $inputProduct['CoverImageUrl'] = $uri . $inputProduct['CoverImageUrl'];

                    $data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $inputProduct['CoverImage']));

                    if (!$data) throw new InvalidArgumentException('Image could not be saved. ');

                    file_put_contents($inputProduct['CoverImageUrl'], $data);

                    // $this->productsModel->insert($inputProduct);

                    $responseData = '';
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
