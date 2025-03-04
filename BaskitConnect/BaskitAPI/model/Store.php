<?php 

// class Store
// {
//     public static function createStore($conn, $storeName, $ownerName, $storePhone, $storeOrigin, $storeStatus)
//     {
//         $sql = "INSERT INTO stores (store_name, owner_name, store_phone_number, store_origin, store_status) 
//                 VALUES (?, ?, ?, ?, ?)";
//         $stmt = $conn->prepare($sql);
//         $stmt->bind_param("sssss", $storeName, $ownerName, $storePhone, $storeOrigin, $storeStatus);
//         return $stmt->execute();
//     }

//     public static function getStores($conn)
//     {
//         $sql = "SELECT * FROM stores";
//         $result = $conn->query($sql);
//         return $result->fetch_all(MYSQLI_ASSOC);
//     }

//     public static function getStoreById($conn, $storeId)
//     {
//         $sql = "SELECT * FROM stores WHERE id = ?";
//         $stmt = $conn->prepare($sql);
//         $stmt->bind_param("i", $storeId);
//         $stmt->execute();
//         return $stmt->get_result()->fetch_assoc();
//     }

//     public static function getStoresByOrigin($conn, $origin)
// {
//     $origin = strtoupper($origin);

//     $sql = "SELECT * FROM stores WHERE store_origin = ? 
//             ORDER BY 
//                 CASE 
//                     WHEN store_status = 'Partner' THEN 1 
//                     ELSE 2 
//                 END, store_name ASC";

//     $stmt = $conn->prepare($sql);
//     $stmt->bind_param("s", $origin);
//     $stmt->execute();
//     $result = $stmt->get_result();
//     return $result->fetch_all(MYSQLI_ASSOC);
// }
// }

require_once __DIR__ . '/../config/database.php';

class Store
{
    //--------- CREATE STORE REQUEST ---------//
    public static function createStoreRequest($conn, $data)
    {
        $sql = "INSERT INTO store_requests 
                (user_id, store_name, owner_name, store_phone_number, store_address, store_origin, registered_store_name, registered_store_address, certificate_of_registration, valid_id, store_status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssssssss",
            $data['user_id'],
            $data['store_name'],
            $data['owner_name'],
            $data['store_phone_number'],
            $data['store_address'],
            $data['store_origin'],
            $data['registered_store_name'],
            $data['registered_store_address'],
            $data['certificate_of_registration'],
            $data['valid_id'],
            $data['store_status']
        );

        return $stmt->execute();
    }

    //--------- GET ALL STORE REQUEST ---------//
    public static function getAllStoreRequests($conn)
    {
        // SQL query to fetch all store requests with user data
        $sql = "SELECT sr.*, 
                    u.username, 
                    u.firstname, 
                    u.lastname, 
                    u.mobile_number, 
                    u.email AS user_email 
                FROM store_requests sr
                JOIN users u ON sr.user_id = u.id";
        
        // Prepare and execute the query
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        // Fetch all results
        $result = $stmt->get_result();
        
        // If there are no records, return an empty array
        if ($result->num_rows === 0) {
            return [];
        }
        
        // Fetch all rows and return them
        return $result->fetch_all(MYSQLI_ASSOC);
}

    //--------- GET STORE REQUEST BY ID (INCLUDING USER EMAIL) ---------//
    public static function getStoreRequestById($conn, $storeId)
    {
        $sql = "SELECT sr.*, u.email AS user_email 
                FROM store_requests sr
                JOIN users u ON sr.user_id = u.id
                WHERE sr.id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $storeId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    //--------- MOVES STORE_REQUEST TO STORES WHEN APPROVED AND SETS USER ROLE TO SELLER ---------//
    public static function approveStoreRequest($conn, $storeId)
    {
        $store = self::getStoreRequestById($conn, $storeId);

        if (!$store) {
            return ['error' => 'Store request not found'];
        }

        $sql = "INSERT INTO stores 
                (user_id, store_name, owner_name, store_phone_number, store_address, store_origin, registered_store_name, registered_store_address, certificate_of_registration, valid_id, store_status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "issssssssss",
            $store['user_id'],
            $store['store_name'],
            $store['owner_name'],
            $store['store_phone_number'],
            $store['store_address'],
            $store['store_origin'],
            $store['registered_store_name'],
            $store['registered_store_address'],
            $store['certificate_of_registration'],
            $store['valid_id'],
            $store['store_status']
        );

        if (!$stmt->execute()) {
            return ['error' => 'Error approving store'];
        }

        $updateUserSql = "UPDATE users SET role = 'Seller' WHERE id = ?";
        $updateStmt = $conn->prepare($updateUserSql);
        $updateStmt->bind_param("i", $store['user_id']);
        
        if (!$updateStmt->execute()) {
            return ['error' => 'Failed to update user role'];
        }

        $updateSql = "UPDATE store_requests SET request_status = 'approved' WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("i", $storeId);
        $updateStmt->execute();

        return ['message' => 'Store approved successfully.'];
    }

    public static function declineStoreRequest($conn, $storeId){

        $updateSql = "UPDATE store_requests SET request_status = 'rejected' WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("i", $storeId);
        $updateStmt->execute();

        return ['message' => 'Store declined successfully.'];
    }

    //--------- GET ALL STORES WHICH APPROVED NA ---------//
    public static function getAllStores($conn)
    {
        $sql = "SELECT * FROM stores";
        $result = $conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    //--------- GET STORES BY ID ---------//
    public static function getStoreById($conn, $storeId)
    {
        $sql = "SELECT * FROM stores WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $storeId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

     //--------- DELETE STORE THEN RESET USER ROLE TO CONSUMER ---------//
     public static function deleteStore($conn, $storeId)
     {
        $store = self::getStoreById($conn, $storeId);
        
        if (!$store) {
            return ['error' => 'Store not found'];
        }

        $userId = $store['user_id'];

        $sql = "DELETE FROM stores WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $storeId);
        
        if (!$stmt->execute()) {
            return ['error' => 'Error deleting store'];
        }

        $sqlUpdateUser = "UPDATE users SET role = 'Consumer' WHERE id = ?";
        $stmtUpdate = $conn->prepare($sqlUpdateUser);
        $stmtUpdate->bind_param("i", $userId);
        $stmtUpdate->execute();

        return ['message' => 'Store deleted successfully. User role reset to Consumer.'];
     }

    //--------- GET STORES BY ORIGIN ---------//
    public static function getStoresByOrigin($conn, $origin)
    {
        $origin = strtoupper($origin);

        $sql = "SELECT * FROM stores WHERE store_origin = ? 
                ORDER BY 
                    CASE 
                        WHEN store_status = 'Partner' THEN 1 
                        ELSE 2 
                    END, store_name ASC";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $origin);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    //--------- ADD STORE IMAGE ---------//
    public static function updateStoreImage($conn, $storeId, $storeImage)
    {
        $sql = "UPDATE stores SET store_image = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $storeImage, $storeId);
        return $stmt->execute();
    }
}
?>