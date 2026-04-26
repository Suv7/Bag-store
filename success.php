<?php
include("config.php");
session_start();

if (isset($_GET['q']) && $_GET['q'] === 'su') {
    $order_id = isset($_GET['oid']) ? intval($_GET['oid']) : 0;
    
    if ($order_id > 0) {
        // Update order status to confirmed
        $conn->query("UPDATE orders SET status = 'confirmed', payment_status = 'paid' WHERE id = $order_id");
        
        // Get order details
        $order_result = $conn->query("SELECT * FROM orders WHERE id = $order_id");
        $order = $order_result->fetch_assoc();
        
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Payment Successful</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    min-height: 100vh;
                    margin: 0;
                    background-color: #f0f9f0;
                }
                .success-container {
                    text-align: center;
                    padding: 40px;
                    background: white;
                    border-radius: 10px;
                    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                }
                .checkmark {
                    color: #28a745;
                    font-size: 60px;
                }
                h1 {
                    color: #28a745;
                    margin: 20px 0;
                }
                .order-details {
                    margin: 20px 0;
                    padding: 15px;
                    background: #f8f9fa;
                    border-radius: 5px;
                }
                a {
                    display: inline-block;
                    margin-top: 20px;
                    padding: 10px 20px;
                    background: #902c7e;
                    color: white;
                    text-decoration: none;
                    border-radius: 5px;
                }
                a:hover {
                    background: #7a256a;
                }
            </style>
        </head>
        <body>
            <div class="success-container">
                <div class="checkmark">✓</div>
                <h1>Payment Successful!</h1>
                <div class="order-details">
                    <p><strong>Order ID:</strong> #<?php echo $order_id; ?></p>
                    <p><strong>Total Amount:</strong> Rs. <?php echo number_format($order['total_price'], 2); ?></p>
                    <p><strong>Status:</strong> Confirmed</p>
                </div>
                <a href="index.php">Continue Shopping</a>
                <a href="viewOrders.php">View Orders</a>
            </div>
        </body>
        </html>
        <?php
    } else {
        echo "Invalid order ID.";
    }
} else {
    header("Location: index.php");
    exit();
}
?>
