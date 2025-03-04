<?php
require_once __DIR__ . '/../model/Cart.php';
require_once __DIR__ . '/../model/Product.php';

class CartController
{   
    // --------- ADD TO CART -------- //
    public static function addToCart($userId, $data, $conn)
    {
        if (!isset($data['product_id'], $data['product_quantity'], $data['product_portion'])) {
            header('HTTP/1.1 400 Bad Request');
            return ['message' => 'Missing required fields: product_id, product_quantity, or product_portion'];
        }

        $result = Cart::addToCart(
            $userId,
            $data['product_id'],
            $data['product_quantity'],
            $data['product_portion'],
            $conn
        );

        if ($result) {
            header('HTTP/1.1 201 Created');
            return ['message' => 'Product added to cart successfully'];
        }

        header('HTTP/1.1 500 Internal Server Error');
        return ['message' => 'Failed to add product to cart'];
    }

    // --------- VIEW CART BASICALLY ETO MAG DIDISPLAY SA CART -------- //
    public static function viewCart($userId, $conn)
    {
        if (!$userId) {
            header('HTTP/1.1 400 Bad Request');
            return ['message' => 'User ID is required'];
        }

        $cart = Cart::getUserCart($userId, $conn);
        if ($cart) {
            header('HTTP/1.1 200 OK');
            return $cart;
        }

        header('HTTP/1.1 404 Not Found');
        return ['message' => 'Cart is empty or user not found'];
    }

    // --------- UPDATE CART, QUANTITY TAPOS ANONG PORTION PERO QUANTITY LANG SA BACKEND NA NG APP YUNG PORTION -------- //
    public static function updateCart($userId, $data, $conn)
    {
        if (!isset($data['product_id'], $data['product_quantity'], $data['product_portion'])) {
            header('HTTP/1.1 400 Bad Request');
            return ['message' => 'Missing required fields: product_id, product_quantity, or product_portion'];
        }

        $result = Cart::updateCart(
            $userId,
            $data['product_id'],
            $data['product_quantity'],
            $data['product_portion'],
            $conn
        );

        if ($result) {
            header('HTTP/1.1 200 OK');
            return ['message' => 'Cart updated successfully'];
        }

        header('HTTP/1.1 500 Internal Server Error');
        return ['message' => 'Failed to update cart'];
    }

    // --------- REMOVE PRODUCT FROM CART -------- //
    public static function removeFromCart($userId, $data, $conn)
    {
        if (!isset($data['product_id'], $data['product_portion'])) {
            header('HTTP/1.1 400 Bad Request');
            return ['message' => 'Missing required fields: product_id or product_portion'];
        }

        $result = Cart::removeFromCart(
            $userId,
            $data['product_id'],
            $data['product_portion'],
            $conn
        );

        if ($result) {
            header('HTTP/1.1 200 OK');
            return ['message' => 'Product removed from cart successfully'];
        }

        header('HTTP/1.1 500 Internal Server Error');
        return ['message' => 'Failed to remove product from cart'];
    }
}
?>
