<?php
// FIXED: session_start() must be first, before include
session_start();
include("config.php");

// FIXED: Validate session — redirect if not logged in
if (!isset($_SESSION["uid"]) || !isset($_SESSION["username"])) {
    header("Location: index.php");
    exit();
}

$uid = intval($_SESSION["uid"]); // FIXED: Cast to int, never trust session values in queries

// FIXED: Use prepared statement — $uid was used raw in query (SQL injection)
$stmt = $conn->prepare("
    SELECT SUM(c.quantity * pnp.price) AS total_price 
    FROM cart c 
    INNER JOIN products p ON c.bid = p.id 
    JOIN product_name_price pnp ON pnp.id = p.pnp 
    WHERE c.uid = ?
");
$stmt->bind_param("i", $uid);
$stmt->execute();
$cart_result = $stmt->get_result();
$total_price = $cart_result->fetch_assoc()["total_price"] ?? 0;

// FIXED: If cart is empty, don't create a ghost order — redirect back
if ($total_price <= 0) {
    header("Location: cart.php");
    exit();
}

// FIXED: Use prepared statement for INSERT ORDER
$stmt2 = $conn->prepare("INSERT INTO orders(uid, total_price) VALUES(?, ?)");
$stmt2->bind_param("id", $uid, $total_price);
$stmt2->execute();
$order_id = $conn->insert_id; // FIXED: Use OOP style consistent with rest of code

// FIXED: Verify order was actually created before continuing
if ($order_id === 0) {
    header("Location: cart.php");
    exit();
}

// FIXED: Use prepared statement for fetching cart items
$stmt3 = $conn->prepare("
    SELECT *, cart.quantity AS cart_quantity 
    FROM cart 
    INNER JOIN products ON cart.bid = products.id 
    WHERE cart.uid = ?
");
$stmt3->bind_param("i", $uid);
$stmt3->execute();
$result = $stmt3->get_result();

// FIXED: Check cart has items before looping
if ($result->num_rows === 0) {
    header("Location: cart.php");
    exit();
}

// FIXED: Use prepared statements for INSERT and UPDATE inside loop
$insert_stmt  = $conn->prepare("INSERT INTO order_product(oid, bid, quantity) VALUES(?, ?, ?)");
$update_stmt  = $conn->prepare("UPDATE products SET quantity = ? WHERE id = ?");

while ($row = $result->fetch_assoc()) {
    $bid            = intval($row["bid"]);
    $quantity       = intval($row["cart_quantity"]);
    $stock_quantity = intval($row["quantity"]);

    // Cap quantity at available stock
    if ($quantity > $stock_quantity) {
        $quantity = $stock_quantity;
    }

    // FIXED: Prepared insert for order_product
    $insert_stmt->bind_param("iii", $order_id, $bid, $quantity);
    $insert_stmt->execute();

    // FIXED: Prepared update for stock
    $new_quantity = $stock_quantity - $quantity;
    $update_stmt->bind_param("ii", $new_quantity, $bid);
    $update_stmt->execute();
}

// FIXED: Use prepared statement for DELETE cart
$stmt4 = $conn->prepare("DELETE FROM cart WHERE uid = ?");
$stmt4->bind_param("i", $uid);
$stmt4->execute();

// Fetch order items to display invoice preview
$stmt5 = $conn->prepare("
    SELECT pnp.name, pnp.price, op.quantity AS item_quantity,
           (op.quantity * pnp.price) AS line_total,
           p.image
    FROM order_product op
    INNER JOIN products p ON op.bid = p.id
    INNER JOIN product_name_price pnp ON pnp.id = p.pnp
    WHERE op.oid = ?
");
$stmt5->bind_param("i", $order_id);
$stmt5->execute();
$order_items = $stmt5->get_result();

// Fetch user details + their location coordinates for Haversine
$stmt6 = $conn->prepare("
    SELECT u.*, l.latitude, l.longitude, l.name AS location_name
    FROM users u
    LEFT JOIN location l ON l.id = u.location_id
    WHERE u.id = ?
");
$stmt6->bind_param("i", $uid);
$stmt6->execute();
$user = $stmt6->get_result()->fetch_assoc();

// --- Haversine: find nearest inventory to the user ---
require_once("haversine.php");

$nearest_inventory = null;
$inventories = [];
if (!empty($user["latitude"]) && !empty($user["longitude"])) {
    $user_coords = [(float)$user["latitude"], (float)$user["longitude"]];
    $inv_stmt = $conn->prepare("
        SELECT i.id, i.name AS inventory_name, l.name AS location_name,
               l.latitude, l.longitude
        FROM inventory i
        JOIN location l ON i.location_id = l.id
        ORDER BY i.id
    ");
    $inv_stmt->execute();
    $inv_result = $inv_stmt->get_result();
    while ($inv_row = $inv_result->fetch_assoc()) {
        $dist = haversineDistance(
            (float)$inv_row["latitude"], (float)$inv_row["longitude"],
            $user_coords[0], $user_coords[1]
        );
        $inv_row["distance_km"] = round($dist, 2);
        $inventories[] = $inv_row;
    }
    usort($inventories, fn($a, $b) => $a["distance_km"] <=> $b["distance_km"]);
    $nearest_inventory = $inventories[0] ?? null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation #<?php echo htmlspecialchars((string)$order_id); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<?php include("./components/navbar.php"); ?>

<div class="max-w-3xl mx-auto px-4 py-10">

    <!-- Success banner -->
    <div class="bg-green-50 border border-green-200 rounded-lg px-6 py-4 mb-6 flex items-center gap-3">
        <span class="text-green-500 text-2xl">&#10003;</span>
        <div>
            <p class="font-semibold text-green-800">Order placed successfully!</p>
            <p class="text-sm text-green-600">Please review your order below before proceeding to payment.</p>
        </div>
    </div>

    <!-- Invoice card -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-800">Order Invoice</h2>
            <span class="text-sm text-gray-500">Order #<?php echo htmlspecialchars((string)$order_id); ?></span>
        </div>

        <!-- Customer details -->
        <dl class="divide-y divide-gray-100">
            <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4 bg-gray-50">
                <dt class="text-sm font-medium text-gray-500">Customer</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2">
                    <?php echo htmlspecialchars($user["firstName"] . " " . $user["lastName"]); ?>
                </dd>
            </div>
            <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
                <dt class="text-sm font-medium text-gray-500">Address</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2">
                    <?php echo htmlspecialchars((string)$user["address"]); ?>
                </dd>
            </div>
            <div class="px-6 py-4 sm:grid sm:grid-cols-3 sm:gap-4 bg-gray-50">
                <dt class="text-sm font-medium text-gray-500">Date</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2">
                    <?php echo htmlspecialchars(date("Y-m-d H:i")); ?>
                </dd>
            </div>
        </dl>


        <!-- ===== HAVERSINE: Nearest Inventory Section ===== -->
        <?php if ($nearest_inventory): ?>
        <div class="mx-6 my-4 rounded-lg border border-purple-200 bg-purple-50 p-4">
            <div class="flex items-center gap-2 mb-3">
                <span class="text-purple-700 text-lg">&#128205;</span>
                <h4 class="font-semibold text-purple-800 text-sm uppercase tracking-wide">Nearest Pickup Inventory</h4>
            </div>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-base font-bold text-gray-900"><?php echo htmlspecialchars($nearest_inventory["inventory_name"]); ?></p>
                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($nearest_inventory["location_name"]); ?></p>
                </div>
                <div class="text-right">
                    <span class="inline-block bg-purple-700 text-white text-sm font-semibold px-3 py-1 rounded-full">
                        <?php echo $nearest_inventory["distance_km"]; ?> km away
                    </span>
                </div>
            </div>
            <?php if (count($inventories) > 1): ?>
            <div class="mt-3 border-t border-purple-200 pt-3">
                <p class="text-xs text-gray-500 mb-2">All inventory locations:</p>
                <div class="space-y-1">
                    <?php foreach ($inventories as $i => $inv): ?>
                    <div class="flex justify-between text-xs <?php echo $i === 0 ? 'text-purple-700 font-semibold' : 'text-gray-500'; ?>">
                        <span><?php echo htmlspecialchars($inv["inventory_name"]); ?> &mdash; <?php echo htmlspecialchars($inv["location_name"]); ?></span>
                        <span><?php echo $inv["distance_km"]; ?> km</span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php elseif (empty($user["location_id"])): ?>
        <div class="mx-6 my-4 rounded-lg border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800">
            &#9888; No location set on your account. <a href="register.php" class="underline">Update your profile</a> to see the nearest inventory.
        </div>
        <?php endif; ?>
        <!-- ===== END HAVERSINE ===== -->
        <!-- Order items table -->
        <table class="min-w-full divide-y divide-gray-200 mt-2">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Image</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php while ($row = $order_items->fetch_assoc()): ?>
                <tr>
                    <td class="px-6 py-4">
                        <img src="<?php echo htmlspecialchars((string)$row['image']); ?>"
                             alt="product" class="w-16 h-16 object-cover rounded">
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        <?php echo htmlspecialchars((string)$row['name']); ?>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        <?php echo htmlspecialchars((string)$row['item_quantity']); ?>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        Rs. <?php echo number_format($row['price'], 2); ?>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        Rs. <?php echo number_format($row['line_total'], 2); ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
            <tfoot class="bg-gray-50">
                <tr>
                    <td colspan="4" class="px-6 py-4 text-right text-sm font-semibold text-gray-700">Grand Total:</td>
                    <td class="px-6 py-4 text-sm font-bold text-gray-900">
                        Rs. <?php echo number_format($total_price, 2); ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Action buttons -->
    <div class="mt-6 flex justify-between items-center">
        <a href="pages/shop.php"
           class="text-sm text-purple-600 hover:underline">
            &larr; Continue Shopping
        </a>
        <a href="pay.php?order_id=<?php echo urlencode((string)$order_id); ?>&amount=<?php echo urlencode((string)$total_price); ?>"
           class="bg-purple-700 hover:bg-purple-800 text-white font-semibold py-2 px-6 rounded">
            Proceed to Payment &rarr;
        </a>
    </div>

</div>
</body>
</html>