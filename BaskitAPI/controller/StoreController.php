<?php
require_once __DIR__ . '/../model/Store.php';

class StoreController
{
    public static function create($data, $conn)
    {
        $storeName = $data['store_name'] ?? null;
        $ownerName = $data['owner_name'] ?? null;
        $storePhone = $data['store_phone_number'] ?? null;
        $storeOrigin = strtoupper($data['store_origin'] ?? null);
        $storeStatus = ucfirst(strtolower($data['store_status'] ?? 'Standard'));
    
        // Required fields validation
        $requiredFields = ['store_name', 'owner_name', 'store_phone_number', 'store_origin', 'store_status'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                header('HTTP/1.1 400 Bad Request');
                return ['message' => "Missing required field: $field"];
            }
        }
    
        // Phone number validation
        if (!preg_match('/^(09|\+639)\d{9}$/', $storePhone)) {
            header('HTTP/1.1 400 Bad Request');
            return ['message' => 'Invalid phone number format. Use 09123456789 or +639123456789.'];
        }
    
        // Validate store_origin
        if (!in_array($storeOrigin, ['DAGUPAN', 'CALASIAO'])) {
            header('HTTP/1.1 400 Bad Request');
            return ['message' => 'Invalid store origin. Allowed values are DAGUPAN or CALASIAO.'];
        }
    
        // Validate store_status
        if (!in_array($storeStatus, ['Standard', 'Partner'])) {
            header('HTTP/1.1 400 Bad Request');
            return ['message' => 'Invalid store status. Allowed values are Standard or Partner.'];
        }
    
        // Create the store
        if (Store::createStore($conn, $storeName, $ownerName, $storePhone, $storeOrigin, $storeStatus)) {
            header('HTTP/1.1 201 Created');
            return ['message' => 'Store created successfully'];
        }
    
        header('HTTP/1.1 500 Internal Server Error');
        return ['message' => 'Failed to create store'];
    }

    public static function list($conn)
    {
        $stores = Store::getStores($conn);

        if ($stores) {
            header('HTTP/1.1 200 OK');
            return $stores;
        } 

        header('HTTP/1.1 404 Not Found');
        return ['message' => 'No stores found'];
    }

    public static function listByOrigin($origin, $conn)
    {
        $validOrigins = ['DAGUPAN', 'CALASIAO'];
        $origin = strtoupper($origin);

        if (!in_array($origin, $validOrigins)) {
            header('HTTP/1.1 400 Bad Request');
            return ['message' => 'Invalid origin. Allowed values are DAGUPAN or CALASIAO.'];
        }

        $stores = Store::getStoresByOrigin($conn, $origin);

        if ($stores) {
            header('HTTP/1.1 200 OK');
            return $stores;
        }

        header('HTTP/1.1 404 Not Found');
        return ['message' => 'No stores found for the specified origin'];
    }

}
?>