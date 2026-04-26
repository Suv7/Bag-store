<?php
// Author : Samir Khanal
// Modified for dynamic cart checkout

session_start(); // FIXED: Must be called before anything else, including includes
include("config.php");

$error_message = "";
$khalti_public_key = "test_public_key_9e4479a9754e4276b33089d2cbd1649e";

// Get order details from URL parameters
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$amount   = isset($_GET['amount'])   ? floatval($_GET['amount'])   : 0;

// Redirect early if no valid order
if ($order_id === 0) {
    header("Location: index.php");
    exit();
}

// FIXED: Use prepared statement to prevent SQL injection
$order_details = null;
$product_names = [];

$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_result = $stmt->get_result();

if ($order_result && $order_result->num_rows > 0) {
    $order_details = $order_result->fetch_assoc();
    $amount = floatval($order_details['total_price']);

    // FIXED: Join product_name_price via products.pnp to get the name column
    $stmt2 = $conn->prepare("
        SELECT pnp.name 
        FROM order_product op 
        INNER JOIN products p ON op.bid = p.id
        INNER JOIN product_name_price pnp ON pnp.id = p.pnp
        WHERE op.oid = ?
    ");
    $stmt2->bind_param("i", $order_id);
    $stmt2->execute();
    $products_result = $stmt2->get_result();

    // FIXED: Check result before looping
    if ($products_result) {
        while ($row = $products_result->fetch_assoc()) {
            $product_names[] = $row['name'];
        }
    }
}

// Redirect if amount is still 0 after DB lookup
if ($amount == 0) {
    header("Location: index.php");
    exit();
}

// Set dynamic product info
$uniqueProductId   = "order_" . $order_id;
$uniqueUrl         = "http://localhost/bags_store/order/" . $order_id;
$uniqueProductName = !empty($product_names)
    ? implode(", ", array_slice($product_names, 0, 3)) . (count($product_names) > 3 ? "..." : "")
    : "Bag Store Order";
$successRedirect = "http://localhost/bags_store/success.php?q=su&oid=" . $order_id;
$failureRedirect = "http://localhost/bags_store/fail.php?q=fu";

// Store order in session for verification
$_SESSION['pending_order_id'] = $order_id;
$_SESSION['pending_amount']   = $amount;

// FIXED: Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ------------------------------------------------------------------------

function checkValid($data, $expected_amount)
{
    // FIXED: Use array_key_exists() instead of deprecated key_exists()
    if (!array_key_exists("amount", $data)) {
        return 0;
    }
    $received_amount    = floatval($data["amount"]);
    $expected_in_paisa  = round($expected_amount * 100);

    return ($received_amount === $expected_in_paisa) ? 1 : 0;
}

// ------------------------------------------------------------------------
// Handle mobile + MPIN submission (step 1)
// ------------------------------------------------------------------------

$token = "";
$price = $amount;

if (isset($_POST["mobile"], $_POST["mpin"])) {

    // FIXED: Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error_message = "Invalid request. Please try again.";
    } else {
        $mobile       = $_POST["mobile"];
        $amount_paisa = round((float)$amount * 100);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => 'https://khalti.com/api/v2/payment/initiate/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => json_encode([
                "public_key"       => $khalti_public_key,
                "mobile"           => $mobile,
                "transaction_pin"  => $_POST["mpin"],
                "amount"           => $amount_paisa,
                "product_identity" => $uniqueProductId,
                "product_name"     => $uniqueProductName,
                "product_url"      => $uniqueUrl,
            ]),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);
        $parsed = json_decode($response, true);

        if (array_key_exists("token", $parsed)) {
            $token = $parsed["token"];
            $_SESSION['khalti_token'] = $token;
            // FIXED: Do NOT store MPIN in session — it is not needed after this step
        } else {
            $error_message = "Incorrect mobile or MPIN";
            if (array_key_exists("non_field_errors", $parsed)) {
                $error_message = htmlspecialchars(implode(", ", $parsed["non_field_errors"]));
            }
        }
    }
}

// ------------------------------------------------------------------------
// Handle OTP verification (step 2)
// ------------------------------------------------------------------------

if (isset($_POST["otp"], $_POST["token"])) {

    // FIXED: Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error_message = "Invalid request. Please try again.";
    } else {
        $otp   = $_POST["otp"];
        $token = $_POST["token"];

        // FIXED: Retrieve MPIN from POST (user re-enters), not from session
        $mpin  = isset($_POST["mpin"]) ? $_POST["mpin"] : "";

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => 'https://khalti.com/api/v2/payment/confirm/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => json_encode([
                "public_key"        => $khalti_public_key,
                "transaction_pin"   => $mpin,
                "confirmation_code" => $otp,
                "token"             => $token,
            ]),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);
        $parsed = json_decode($response, true);

        if (array_key_exists("token", $parsed)) {
            $isvalid = checkValid($parsed, $amount);
            if ($isvalid) {
                // FIXED: Use prepared statement to prevent SQL injection
                $update = $conn->prepare("UPDATE orders SET payment_status = 'paid', payment_method = 'khalti' WHERE id = ?");
                $update->bind_param("i", $order_id);
                $update->execute();

                // FIXED: Clear Khalti token from session after successful use
                unset($_SESSION['khalti_token']);

                $error_message = "<span style='color:green'>Payment successful! Redirecting...</span>"
                    . "<script>setTimeout(function(){ window.location='" . $successRedirect . "'; }, 2000);</script>";
            } else {
                $error_message = "Payment amount verification failed.";
            }
        } else {
            $error_message = "Could not process the transaction at the moment.";
            if (array_key_exists("detail", $parsed)) {
                $error_message = htmlspecialchars($parsed["detail"]); // FIXED: escape server error
            }
        }
    }
}

// Resolve token to display (from current request or session)
$display_token = !empty($token) ? $token : ($_SESSION['khalti_token'] ?? "");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Khalti Payment</title>
</head>
<body>

<div class="khalticontainer">
    <center>
        <div><img src="khalti.png" alt="khalti" width="200"></div>
        <h3>Order #<?php echo htmlspecialchars((string)$order_id); ?></h3>
    </center>

    <?php if ($display_token === ""): ?>
        <!-- Step 1: Enter mobile & MPIN -->
        <form action="pay.php?order_id=<?php echo urlencode((string)$order_id); ?>&amount=<?php echo urlencode((string)$amount); ?>" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <input type="hidden" name="order_id"   value="<?php echo htmlspecialchars((string)$order_id); ?>">
            <input type="hidden" name="amount"      value="<?php echo htmlspecialchars((string)$price); ?>">

            <small>Mobile Number:</small><br>
            <input type="number" class="number" minlength="10" maxlength="10" name="mobile" placeholder="98xxxxxxxx" required>

            <small>Khalti MPIN:</small><br>
            <input type="password" class="mpin" name="mpin" minlength="4" maxlength="6" placeholder="xxxx" required>

            <small>Amount:</small><br>
            <input type="text" class="price" value="Rs. <?php echo number_format($price, 2); ?>" disabled>

            <span style="display:block;color:red;"><?php echo $error_message; ?></span>

            <button type="submit">Pay Rs. <?php echo number_format($price, 2); ?></button>
            <br>
            <small>We don't store your credentials for security reasons.</small>
        </form>

    <?php else: ?>
        <!-- Step 2: Enter OTP -->
        <form action="pay.php?order_id=<?php echo urlencode((string)$order_id); ?>&amount=<?php echo urlencode((string)$amount); ?>" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <input type="hidden" name="token"      value="<?php echo htmlspecialchars($display_token); ?>">
            <input type="hidden" name="order_id"   value="<?php echo htmlspecialchars((string)$order_id); ?>">

            <small>Enter your MPIN again to confirm:</small><br>
            <input type="password" class="mpin" name="mpin" minlength="4" maxlength="6" placeholder="xxxx" required>

            <small>OTP:</small><br>
            <input type="number" name="otp" placeholder="xxxx" required>

            <span style="display:block;color:red;"><?php echo $error_message; ?></span>

            <button type="submit">Pay Rs. <?php echo number_format($price, 2); ?></button>
        </form>
    <?php endif; ?>
</div>

<style>
.khalticontainer {
    width: 300px;
    border: 2px solid #5C2D91;
    margin: 0 auto;
    padding: 8px;
}
input {
    display: block;
    width: 98%;
    padding: 8px;
    margin: 2px;
}
button {
    display: block;
    background-color: #5C2D91;
    border: none;
    color: white;
    cursor: pointer;
    width: 98%;
    padding: 8px;
    margin: 2px;
}
button:hover {
    opacity: 0.8;
}
</style>

</body>
</html>