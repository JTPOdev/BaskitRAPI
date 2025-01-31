<?php

require_once __DIR__ . '/../model/Cart.php';
require_once __DIR__ . '/../model/Product.php';

class CartController
{
    public static function addToCart($data, $conn)
    {
        return Cart::addToCart($data['user_id'], $data['product_id'], $data['product_quantity'], $data['product_portion'], $conn);
    }

    public static function viewCart($userId, $conn)
    {
        return Cart::getUserCart($userId, $conn);
    }

    public static function updateCart($data, $conn)
    {
        return Cart::updateCart($data['user_id'], $data['product_id'], $data['product_quantity'], $data['product_portion'], $conn);
    }

    public static function removeFromCart($data, $conn)
    {
        return Cart::removeFromCart($data['user_id'], $data['product_id'], $data['product_portion'], $conn);
    }
}
