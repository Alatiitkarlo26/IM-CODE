<?php
// process_actions.php
header('Content-Type: application/json');
require_once '../db/db_connection.php';

if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'Database layer link failed to respond.']);
    exit();
}

$requestInput = file_get_contents('php://input');
$payload = json_decode($requestInput, true);

if (!$payload || !isset($payload['action'])) {
    echo json_encode(['status' => 'error', 'message' => 'Malformed structural data payload.']);
    exit();
}

$action = $payload['action'];

// Session safety tracking fallback for active user reference logs
session_start();
$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1; 

try {
    switch ($action) {
        
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

         // Query 7: Provision a New Product Asset Records a newly registered instrument into the active inventory database, 
         // assigning its core metadata, pricing, and physical location.
            $stmt = $conn->prepare("INSERT INTO tbl_products (product_name, category_id, brand_id, unit_price, quantity_on_hand, location) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("siidis", $name, $catId, $brandId, $price, $qty, $location);
            $stmt->execute();
            $stmt->close();

            echo json_encode(['status' => 'success', 'message' => 'Asset provisioned and cataloged successfully.']);
            break;

        case 'CREATE_BRAND':
            $brandName = trim($payload['brand_name']);
            $phone = trim($payload['phone']);
            $email = trim($payload['email']);
            $address = trim($payload['address']);

            if (empty($brandName) || empty($phone) || empty($email) || empty($address)) {
                throw new Exception("All manufacturer corporate fields are required.");
            }

        //  Query 8: Establish a New Brand Profile to the brand directory, storing their corporate contact information.
            $stmt = $conn->prepare("INSERT INTO tbl_brands (brand_name, phone, email, address) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $brandName, $phone, $email, $address);
            $stmt->execute();
            $stmt->close();

            echo json_encode(['status' => 'success', 'message' => 'Manufacturer entity initialized and operational nodes established.']);
            break;

      case 'EXECUTE_ADJUSTMENT':
            $productId = (int)$payload['product_id'];
            $type = trim($payload['type']); // Verified matching key
            $qty = (int)$payload['quantity'];

            if ($qty <= 0) {
                throw new Exception("Volume unit processing changes must be positive integers.");
            }

            // Initialize atomic transaction safety block
            $conn->begin_transaction();

            // Validate item availability state prior to stock alterations
            $checkStmt = $conn->prepare("SELECT quantity_on_hand FROM tbl_products WHERE product_id = ? FOR UPDATE");
            $checkStmt->bind_param("i", $productId);
            $checkStmt->execute();
            $res = $checkStmt->get_result()->fetch_assoc();
            $checkStmt->close();

            if (!$res) {
                throw new Exception("Target asset record could not be localized on current schema.");
            }

            if ($type === 'Stock-Out' && $res['quantity_on_hand'] < $qty) {
                throw new Exception("Insufficient ledger balances available for complete execution disbursal.");
            }

            // STEP A: Insert entry into transaction log ledger
            // Query 9: Record Audit Ledger Entry Permanently logs a stock action(whether it’s Stock-in or Stock-out) linked to the user who authorized it.
            $logStmt = $conn->prepare("INSERT INTO tbl_stock_history (product_id, user_id, stock_type, quantity) VALUES (?, ?, ?, ?)");
            $logStmt->bind_param("iisi", $productId, $userId, $type, $qty);
            $logStmt->execute();
            $logStmt->close();

            // STEP B: Perform dynamic balancing adjustment updates against production metrics
            // Query 10: Dynamic Stock Balance Adjustment (UPDATE) While not an INSERT query, 
            // this critical UPDATE runs in the same transaction block as Query 9 to actively 
            // adjust the total units of a specific product based on the transaction type.
            if ($type === 'Stock-In') {
                $updateStmt = $conn->prepare("UPDATE tbl_products SET quantity_on_hand = quantity_on_hand + ? WHERE product_id = ?");
            } else {
                $updateStmt = $conn->prepare("UPDATE tbl_products SET quantity_on_hand = quantity_on_hand - ? WHERE product_id = ?");
            }
            $updateStmt->bind_param("ii", $qty, $productId);
            $updateStmt->execute();
            $updateStmt->close();

            // ─── NEW FEATURE LAYER: DYNAMIC IS_AVAILABLE STATUS FLIPPER ───
            // Fetch the updated quantity directly from the database to see the final calculation result
            $finalCheckStmt = $conn->prepare("SELECT quantity_on_hand FROM tbl_products WHERE product_id = ?");
            $finalCheckStmt->bind_param("i", $productId);
            $finalCheckStmt->execute();
            $finalResult = $finalCheckStmt->get_result()->fetch_assoc();
            $finalCheckStmt->close();

            if ($finalResult) {
                $newQuantity = (int)$finalResult['quantity_on_hand'];
                // If quantity drops to 0, set availability flag to 0. Otherwise, ensure it is 1.
                $isAvailableFlag = ($newQuantity === 0) ? 0 : 1;

                $availabilityStmt = $conn->prepare("UPDATE tbl_products SET is_available = ? WHERE product_id = ?");
                $availabilityStmt->bind_param("ii", $isAvailableFlag, $productId);
                $availabilityStmt->execute();
                $availabilityStmt->close();
            }
            // ──────────────────────────────────────────────────────────────

            // Commit safely executed ledger changes
            $conn->commit();
            echo json_encode(['status' => 'success', 'message' => 'Stock history ledger updated and database availability flags recorded successfully.']);
            break;

        default:
            throw new Exception("Specified command pipeline action not found on server routing.");
    }
} catch (Exception $e) {
    if ($action === 'EXECUTE_ADJUSTMENT') {
        $conn->rollback(); // Revert backplane mutations if atomic framework snaps
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>