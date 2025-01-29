<?php 

class Store
{
    public static function createStore($conn, $storeName, $ownerName, $storePhone)
    {
        $sql = "INSERT INTO stores (store_name, owner_name, store_phone_number) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $storeName, $ownerName, $storePhone);
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
}
?>