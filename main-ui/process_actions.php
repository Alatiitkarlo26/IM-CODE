<?php
// process_actions.php
header('Content-Type: application/json');
require_once '../db/db_connection.php';

// Ensure standard database connection state is active
if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'Database layer link failed to respond.']);
    exit();
}

// Read raw input stream payload
$requestInput = file_get_contents('php://input');
$payload = json_decode($requestInput, true);

if (!$payload || !isset($payload['action'])) {
    echo json_encode(['status' => 'error', 'message' => 'Malformed structural data payload.']);
    exit();
}

$action = $payload['action'];

try {
    switch ($action) {
        
        // ─── 1. CORE PRODUCT SPECIFICATION PROVISIONING ───
        case 'CREATE_PRODUCT':
            $name = trim($payload['product_name']);
            $catId = (int)$payload['category_id'];
            $brandId = (int)$payload['brand_id'];
            $price = (float)$payload['unit_price'];
            $qty = (int)$payload['quantity_on_hand'];
            $location = trim($payload['location']);
            
            if (empty($name) || empty($location)) {
                throw new Exception("All core product specification fields are required.");
            }

            $stmt = $conn->prepare("INSERT INTO tbl_products (product_name, category_id, brand_id, unit_price, quantity_on_hand, location, is_available) VALUES (?, ?, ?, ?, ?, ?, 1)");
            $stmt->bind_param("siidis", $name, $catId, $brandId, $price, $qty, $location);
            
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => "Product catalog entry '$name' committed successfully."]);
            } else {
                throw new Exception($stmt->error);
            }
            $stmt->close();
            break;

        // ─── 2. BRAND MANUFACTURING LIFECYCLE INITIALIZATION ───
        case 'CREATE_BRAND':
            $name = trim($payload['brand_name']);
            $phone = trim($payload['phone']);
            $email = trim($payload['email']);
            $address = trim($payload['address']);

            if (empty($name) || empty($phone) || empty($email) || empty($address)) {
                throw new Exception("All structural manufacturing profile parameters must be satisfied.");
            }

            $stmt = $conn->prepare("INSERT INTO tbl_brands (brand_name, phone, email, address, is_active) VALUES (?, ?, ?, ?, 1)");
            $stmt->bind_param("ssss", $name, $phone, $email, $address);

            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => "Manufacturer Entity '$name' provisioned successfully."]);
            } else {
                throw new Exception($stmt->error);
            }
            $stmt->close();
            break;

        // ─── 3. BALANCED STOCK AUDIT ADJUSTMENT LEDGER (TRANSACTION SAFE) ───
        case 'EXECUTE_ADJUSTMENT':
            $productId = (int)$payload['product_id'];
            $type = $payload['stock_type']; // Expected: 'Stock-In' or 'Stock-Out'
            $qty = (int)$payload['quantity'];
            $userId = 1; // Default context fallback targeting Super Admin user entry (Karlo)

            if ($qty <= 0 || ($type !== 'Stock-In' && $type !== 'Stock-Out')) {
                throw new Exception("Invalid operational metrics designated for ledger calculation.");
            }

            // Enforce hard validation safety rule for Outbound Disbursals
            if ($type === 'Stock-Out') {
                $checkStmt = $conn->prepare("SELECT quantity_on_hand FROM tbl_products WHERE product_id = ?");
                $checkStmt->bind_param("i", $productId);
                $checkStmt->execute();
                $res = $checkStmt->get_result()->fetch_assoc();
                $checkStmt->close();

                if (!$res || $res['quantity_on_hand'] < $qty) {
                    throw new Exception("Operation Aborted: Insufficient stock assets available in warehouse holding.");
                }
            }

            // ATOMIC SAFEPOINT INVOCATION
            $conn->begin_transaction();

            // STEP A: Append record into historical ledger tracking rows
            $logStmt = $conn->prepare("INSERT INTO tbl_stock_history (product_id, user_id, stock_type, quantity) VALUES (?, ?, ?, ?)");
            $logStmt->bind_param("iisi", $productId, $userId, $type, $qty);
            $logStmt->execute();
            $logStmt->close();

            // STEP B: Perform mathematical delta calculation directly against assets table
            if ($type === 'Stock-In') {
                $updateStmt = $conn->prepare("UPDATE tbl_products SET quantity_on_hand = quantity_on_hand + ? WHERE product_id = ?");
            } else {
                $updateStmt = $conn->prepare("UPDATE tbl_products SET quantity_on_hand = quantity_on_hand - ? WHERE product_id = ?");
            }
            $updateStmt->bind_param("ii", $qty, $productId);
            $updateStmt->execute();
            $updateStmt->close();

            // Commit atomic stack securely
            $conn->commit();
            echo json_encode(['status' => 'success', 'message' => 'Stock history ledger updated and inventory balances updated.']);
            break;

        default:
            throw new Exception("Specified command pipeline action not found on server routing.");
    }
} catch (Exception $e) {
    // Structural cleanup fallback block: rollback tracking if transaction block collapses
    if ($action === 'EXECUTE_ADJUSTMENT') {
        $conn->rollback();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>