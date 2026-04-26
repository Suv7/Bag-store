<?php
include("config.php");
session_start();

if (isset($_GET['q']) && $_GET['q'] === 'fu') {
    $order_id = isset($_GET['oid']) ? intval($_GET['oid']) : 0;
    
    if ($order_id > 0) {
        // Update order status to failed
        $conn->query("UPDATE orders SET payment_status = 'failed' WHERE id = $order_id");
    }
    
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Payment Failed</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                margin: 0;
                background-color: #fff5f5;
            }
            .fail-container {
                text-align: center;
                padding: 40px;
                background: white;
                border-radius: 10px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            }
            .cross {
                color: #dc3545;
                font-size: 60px;
            }
            h1 {
                color: #dc3545;
                margin: 20px 0;
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
        <div class="fail-container">
            <div class="cross">✕</div>
            <h1>Payment Failed</h1>
            <p>Your payment could not be processed. Please try again.</p>
            <a href="pay.php?order_id=<?php echo $order_id; ?>">Try Again</a>
            <a href="index.php">Go to Home</a>
        </div>
    </body>
    </html>
    <?php
} else {
    header("Location: index.php");
    exit();
}
?>
