<?php

require_once __DIR__ . '/../model/Cart.php';
require_once __DIR__ . '/../model/Product.php';

class CartController
{
    public static function addToCart($userId, $data, $conn)
    {
        return Cart::addToCart($userId, $data['product_id'], $data['product_quantity'], $data['product_portion'], $conn);
    }

    public static function viewCart($userId, $conn)
    {
        return Cart::getUserCart($userId, $conn);
    }

    public static function updateCart($userId, $data, $conn)
    {
        return Cart::updateCart($userId, $data['product_id'], $data['product_quantity'], $data['product_portion'], $conn);
    }

    public static function removeFromCart($userId, $data, $conn)
    {
        return Cart::removeFromCart($userId, $data['product_id'], $data['product_portion'], $conn);
    }
}
