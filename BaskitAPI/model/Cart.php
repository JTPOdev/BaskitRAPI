<?php

class Cart
{
    public static function addToCart($userId, $productId, $quantity, $portion, $conn)
    {

        AuthMiddleware::checkAuth();
        $userQuery = "SELECT * FROM users WHERE id = ?";
        $stmt = $conn->prepare($userQuery);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $userResult = $stmt->get_result(); //this or lagay ko nalang yung userexists ko sa user model
    
        if ($userResult->num_rows === 0) {
            return ['error' => 'User not found'];
        }
    
        // Fetch product details
        $product = Product::getProductById($productId, $conn);
        if (!$product) {
            return ['error' => 'Product not found'];
        }
        $sql = "SELECT * FROM cart WHERE user_id = ? AND product_id = ? AND product_portion = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $userId, $productId, $portion);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            $sql = "UPDATE cart SET product_quantity = product_quantity + ? WHERE user_id = ? AND product_id = ? AND product_portion = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiis", $quantity, $userId, $productId, $portion);
        } else {
            $query = "INSERT INTO cart (user_id, product_id, product_name, product_price, product_quantity, product_portion, product_origin) 
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("iisdiss", $userId, $productId, $product['product_name'], $product['product_price'], $quantity, $portion, $product['product_origin']);  // Correct binding types
        }
    
        return $stmt->execute() ? ['success' => 'Added to cart'] : ['error' => 'Failed to add to cart'];
    }
    

    public static function getUserCart($userId, $conn)
    {
        $query = "SELECT * FROM cart WHERE user_id = ?";
        
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $cartItems = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            return $cartItems;
        } else {
            return ['error' => 'Failed to prepare statement.'];
        }
    }

    public static function updateCart($userId, $productId, $quantity, $portion, $conn)
    {
        AuthMiddleware::checkAuth();
        $sql = "UPDATE cart SET product_quantity = ? WHERE user_id = ? AND product_id = ? AND product_portion = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiis", $quantity, $userId, $productId, $portion);
        return $stmt->execute() ? ['success' => 'Cart updated'] : ['error' => 'Failed to update cart'];
    }

    public static function removeFromCart($userId, $productId, $portion, $conn)
    {
        AuthMiddleware::checkAuth();
        $sql = "DELETE FROM cart WHERE user_id = ? AND product_id = ? AND product_portion = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $userId, $productId, $portion);
        return $stmt->execute() ? ['success' => 'Removed from cart'] : ['error' => 'Failed to remove from cart'];
    }
}
