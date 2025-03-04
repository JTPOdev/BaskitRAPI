<?php
require_once __DIR__ . '/../model/Store.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class StoreController
{   
    // --------- CREATE STORE REQUEST -------- //
    public static function create($userid, $data, $conn)
    {   
        $data['user_id'] = $userid;
        $storeName = trim($data['store_name'] ?? '');
        $ownerName = trim($data['owner_name'] ?? '');
        $storePhone = trim($data['store_phone_number'] ?? '');
        $storeAddress = trim($data['store_address'] ?? '');
        $storeOrigin = strtoupper(trim($data['store_origin'] ?? ''));
        $registeredStoreName = trim($data['registered_store_name'] ?? '');
        $registeredStoreAddress = trim($data['registered_store_address'] ?? '');
        $storeStatus = ucfirst(strtolower(trim($data['store_status'] ?? 'Standard')));

        $requiredFields = ['store_name', 'owner_name', 'store_phone_number', 'store_address', 'store_origin', 'registered_store_name', 'registered_store_address', 'store_status'];

        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                header('HTTP/1.1 400 Bad Request');
                return ['message' => "Missing required field: $field"];
            }
        }

        if (!preg_match('/^(09|\+639)\d{9}$/', $storePhone)) {
            header('HTTP/1.1 400 Bad Request');
            return ['message' => 'Invalid phone number format. Use 09123456789 or +639123456789.'];
        }

        $validOrigins = ['DAGUPAN', 'CALASIAO'];
        if (!in_array($storeOrigin, $validOrigins)) {
            header('HTTP/1.1 400 Bad Request');
            return ['message' => 'Invalid store origin. Allowed values: DAGUPAN or CALASIAO.'];
        }

        $validStatuses = ['Standard', 'Partner'];
        if (!in_array($storeStatus, $validStatuses)) {
            header('HTTP/1.1 400 Bad Request');
            return ['message' => 'Invalid store status. Allowed values: Standard or Partner.'];
        }

        $certificatePath = self::uploadFile($_FILES['certificate_of_registration'] ?? null, 'certificates');
        $validIdPath = self::uploadFile($_FILES['valid_id'] ?? null, 'valid_ids');

        if (isset($certificatePath['error']) || isset($validIdPath['error'])) {
            header('HTTP/1.1 400 Bad Request');
            return ['message' => 'File upload failed'];
        }

        $success = Store::createStoreRequest($conn, [
            'user_id' => $userid,
            'store_name' => $storeName,
            'owner_name' => $ownerName,
            'store_phone_number' => $storePhone,
            'store_address' => $storeAddress,
            'store_origin' => $storeOrigin,
            'registered_store_name' => $registeredStoreName,
            'registered_store_address' => $registeredStoreAddress,
            'store_status' => $storeStatus,
            'certificate_of_registration' => $certificatePath['success'],
            'valid_id' => $validIdPath['success']
        ]);

        if ($success) {
            header('HTTP/1.1 201 Created');
            return ['message' => 'Store request submitted successfully.'];
        }

        header('HTTP/1.1 500 Internal Server Error');
        return ['message' => 'Error submitting store request.'];
    }

    // --------- APPROVE STORE REQUEST WITH EMAIL -------- //
    public static function approve($storeId, $conn)
    {
        $storeId = (int) $storeId;
        $store = Store::getStoreRequestById($conn, $storeId);
    
        if (!$store) {
            header('HTTP/1.1 404 Not Found');
            return ['message' => 'Store request not found.'];
        }
    
        $approvalResult = Store::approveStoreRequest($conn, $storeId);
        if (isset($approvalResult['error'])) {
            header('HTTP/1.1 500 Internal Server Error');
            return $approvalResult;
        }
    
        self::sendApprovalEmail($store['user_email'], $store['store_name']);
        header('HTTP/1.1 200 OK');
        return ['message' => 'Store request approved successfully. Email notification sent.'];
    }
    
    //--------- DECLINE STORE REQUEST WITH EMAIL ---------//
    public static function decline($storeId, $conn, $reason = "Your store request has been declined due to missing or incorrect information.")
    {
        $storeId = (int) $storeId;
        $store = Store::getStoreRequestById($conn, $storeId);

        if (!$store) {
            header('HTTP/1.1 404 Not Found');
            return ['message' => 'Store request not found.'];
        }
        
        $declineResult = Store::declineStoreRequest($conn, $storeId);
        if (isset($declineResult['error'])) {
            header('HTTP/1.1 500 Internal Server Error');
            return $declineResult;
        }

        self::sendDeclineEmail($store['user_email'], $store['store_name'], $reason);
        header('HTTP/1.1 200 OK');
        return ['message' => 'Store request declined successfully. Email notification sent.'];
    }


    // --------- SEND APPROVAL EMAIL -------- //
    private static function sendApprovalEmail($email, $storeName)
    {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'baskitofficial@gmail.com';
            $mail->Password = 'dpbqebttludcaeqo';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('baskitofficial@gmail.com', 'Baskit');
            $mail->addAddress($email);
            $mail->Subject = 'Store Request Approved!';
            $mail->isHTML(true);
            $mail->Body = "<p>Congratulations! 🎉</p>
                        <p>Your store request for <b>$storeName</b> has been approved. You are now a seller on our platform.</p>
                        <p>Start listing your products and selling today!</p>";

            $mail->send();
        } catch (Exception $e) {
            return ['message' => 'Failed to send approval email: ' . $mail->ErrorInfo];
        }
        return ['message' => 'Approved email sent.'];
    }

    // --------- SEND DECLINE EMAIL -------- //
    private static function sendDeclineEmail($email, $storeName, $reason)
    {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'baskitofficial@gmail.com';
            $mail->Password = 'dpbqebttludcaeqo';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('baskitofficial@gmail.com', 'Baskit');
            $mail->addAddress($email);
            $mail->Subject = 'Store Request Declined';
            $mail->isHTML(true);
            $mail->Body = "<p>Hello,</p>
                        <p>Unfortunately, your store request for <b>$storeName</b> has been declined.</p>
                        <p>Reason: $reason</p>
                        <p>Please review your request and submit again if needed.</p>";

            $mail->send();
        } catch (Exception $e) {
            return ['message' => 'Failed to send decline email: ' . $mail->ErrorInfo];
        }
        return ['message' => 'Declined email sent.'];
    }

    // --------- GET ALL STORES -------- //
    public static function list($conn)
    {
        $stores = Store::getAllStores($conn);

        if ($stores) {
            header('HTTP/1.1 200 OK');
            return $stores;
        }

        header('HTTP/1.1 404 Not Found');
        return ['message' => 'No stores found.'];
    }

    // --------- GET ALL STORES BY ORIGIN -------- //
    public static function listByOrigin($origin, $conn)
    {
        $origin = strtoupper(trim($origin));
        $validOrigins = ['DAGUPAN', 'CALASIAO'];

        if (!in_array($origin, $validOrigins)) {
            header('HTTP/1.1 400 Bad Request');
            return ['message' => 'Invalid store origin. Allowed values: DAGUPAN or CALASIAO.'];
        }

        $stores = Store::getStoresByOrigin($conn, $origin);

        if ($stores) {
            header('HTTP/1.1 200 OK');
            return $stores;
        }

        header('HTTP/1.1 404 Not Found');
        return ['message' => 'No stores found for the specified origin.'];
    }

    // --------- ADD STORE IMAGE -------- //
    public static function uploadStoreImage($storeId, $conn)
    {
        $storeId = (int) $storeId;
        $storeImagePath = self::uploadFile($_FILES['store_image'] ?? null, 'store_images');

        if (isset($storeImagePath['error'])) {
            header('HTTP/1.1 400 Bad Request');
            return ['message' => 'File upload failed'];
        }

        if (Store::updateStoreImage($conn, $storeId, $storeImagePath['success'])) {
            header('HTTP/1.1 200 OK');
            return ['message' => 'Store image updated successfully.'];
        }

        header('HTTP/1.1 500 Internal Server Error');
        return ['message' => 'Error updating store image.'];
    }

    // --------- UPLOAD FILES -------- //
    private static function uploadFile($file, $folder)
    {
        if (!$file || empty($file['tmp_name'])) {
            return ['error' => 'No file uploaded'];
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
        $fileType = mime_content_type($file['tmp_name']);
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($fileType, $allowedTypes)) {
            return ['error' => 'Invalid file type. Allowed: JPG, PNG, PDF'];
        }

        $uploadDir = __DIR__ . "/../public/uploads/$folder/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = uniqid() . "_" . basename($file['name']);
        $targetPath = $uploadDir . $filename;

        return move_uploaded_file($file['tmp_name'], $targetPath) ?
            ['success' => "/uploads/$folder/" . $filename] :
            ['error' => 'Failed to upload file'];
    }
}
?>