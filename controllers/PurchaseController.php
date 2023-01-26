<?php

namespace Controllers;

use Error;
use InvalidArgumentException;
use Models\ProductsModel;
use Models\PurchasedItemsModel;
use Models\PurchasesModel;

class PurchaseController extends BaseController
{
    private $purchasesModel;
    private $purchasedItemsModel;
    private $productsModel;

    public function __construct()
    {
        parent::__construct();
        $this->purchasesModel = new PurchasesModel;
        $this->purchasedItemsModel = new PurchasedItemsModel;
        $this->productsModel = new ProductsModel;
    }

    public function addAction()
    {
        $strErrorDesc = '';
        $requestMethod = $_SERVER["REQUEST_METHOD"];

        if ($this->validateToken()) {
            if (strtoupper($requestMethod) == 'POST') {
                try {
                    $inputPurchase = (array) json_decode(file_get_contents('php://input'), TRUE);
                    // UserId, CreatedAt and Items array. Each will be inserted to their repective models.
                    $items = [];
                    foreach ($inputPurchase as &$item) {
                        array_push($items, [$item['Product'] => $item['Quantity']]);
                    }

                    $productList = [];
                    $itemPrices = [];
                    $totalPrice = 0;
                    // Creates prod price based on its quantity on a single purchase
                    foreach ($items as $productId => $productQuantity) {
                        array_push($productList, $this->productsModel->fetchBy(['Id' => $productId]));
                        $currentItem = end($productList);
                        if ($productQuantity >= $currentItem['BulkCondition']) {
                            $itemPrice = $currentItem['BulkPrice'] * $productQuantity;
                        }
                        $itemPrice = $currentItem['Price'] * $productQuantity;
                        array_push($itemPrices, $itemPrice);
                        $totalPrice += $itemPrice;
                    }

                    $currentDateTime = date('d-m-y h:i:s');
                    $insertPurchase = ['TotalPrice' => $totalPrice, 'CreatedAt' => $currentDateTime, 'User' => $this->getUserId()];
                    $this->purchasesModel->insert($insertPurchase);

                    $purchase = $this->purchasesModel->fetchBy(['CreatedAt' => $currentDateTime]);

                    for ($i = 0; sizeof($items); $i++) {

                        $insertPurchasedItem = ['Price' => $itemPrices[$i], 'Quantity' => $items[$i], 'Purchase' => $purchase['Id'], 'Product' => $productList[$i]['Id']];
                        $this->purchasedItemsModel->insert($insertPurchasedItem);
                    }

                    $responseData = 'Purchased Successfully!';
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