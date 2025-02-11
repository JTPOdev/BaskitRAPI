<?php 

class Store
{
    public static function createStore($conn, $storeName, $ownerName, $storePhone, $storeOrigin, $storeStatus)
    {
        $sql = "INSERT INTO stores (store_name, owner_name, store_phone_number, store_origin, store_status) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $storeName, $ownerName, $storePhone, $storeOrigin, $storeStatus);
        return $stmt->execute();
    }

    public static function getStores($conn)
    {
        $sql = "SELECT * FROM stores";
        $result = $conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public static function getStoreById($conn, $storeId)
    {
        $sql = "SELECT * FROM stores WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $storeId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public static function getStoresByOrigin($conn, $origin)
{
    $origin = strtoupper($origin);

    $sql = "SELECT * FROM stores 
            WHERE store_origin = ? 
            ORDER BY 
                CASE 
                    WHEN store_status = 'Partner' THEN 1 
                    ELSE 2 
                END, 
                store_name ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $origin);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}
}
?>