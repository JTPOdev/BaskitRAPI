<?php 
require_once __DIR__ . '/../model/Store.php';

class StoreController
{
    public static function create($data, $conn)
    {
        $storeName = $data['store_name'];
        $ownerName = $data['owner_name'];
        $storePhone = $data['store_phone_number'];

        if (empty($storeName) || empty($ownerName) || empty($storePhone)) {
            return ['error' => 'Missing required fields'];
        }

        if (Store::createStore($conn, $storeName, $ownerName, $storePhone)) {
            return ['success' => 'Store created successfully'];
        }
        return ['error' => 'Failed to create store'];
    }

    public static function list($conn)
    {
        return Store::getStores($conn);
    }
}
?>