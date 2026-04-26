<?php
session_start();
include("config.php");
require_once("haversine.php");

// FIXED: redirect + exit if not logged in
if (!isset($_SESSION["uid"])) {
    header("Location: index.php");
    exit();
}

// FIXED: validate order_id
if (!isset($_GET["order_id"]) || !ctype_digit((string)$_GET["order_id"])) {
    header("Location: index.php");
    exit();
}

$order_id = intval($_GET["order_id"]);
$uid      = intval($_SESSION["uid"]);

// FIXED: prepared statement, was raw SQL injection
$stmt = $conn->prepare("
    SELECT o.*, u.firstName, u.lastName, u.address, u.location_id,
           l.latitude, l.longitude, l.name AS location_name
    FROM orders o
    INNER JOIN users u ON o.uid = u.id
    LEFT JOIN location l ON l.id = u.location_id
    WHERE o.id = ? AND u.id = ?
    ORDER BY o.createdDate DESC LIMIT 1
");
$stmt->bind_param("ii", $order_id, $uid);
$stmt->execute();
$order_result = $stmt->get_result();

if ($order_result->num_rows == 0) {
    header("Location: index.php");
    exit();
}
$order_row = $order_result->fetch_assoc();

// FIXED: correct join via products.pnp (was products.id — wrong FK)
$stmt2 = $conn->prepare("
    SELECT *, order_product.quantity AS item_quantity
    FROM order_product
    INNER JOIN products ON order_product.bid = products.id
    JOIN product_name_price pnp ON pnp.id = products.pnp
    WHERE oid = ?
");
$stmt2->bind_param("i", $order_id);
$stmt2->execute();
$order_items = $stmt2->get_result();

// --- Haversine: find nearest inventory ---
$nearest_inventory = null;
$inventories = [];
if (!empty($order_row["latitude"]) && !empty($order_row["longitude"])) {
    $inv_stmt = $conn->prepare("
        SELECT i.id, i.name AS inventory_name, l.name AS location_name,
               l.latitude, l.longitude
        FROM inventory i
        JOIN location l ON i.location_id = l.id
    ");
    $inv_stmt->execute();
    $inv_result = $inv_stmt->get_result();
    while ($inv_row = $inv_result->fetch_assoc()) {
        $dist = haversineDistance(
            (float)$inv_row["latitude"], (float)$inv_row["longitude"],
            (float)$order_row["latitude"], (float)$order_row["longitude"]
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
    <link rel="stylesheet" href="/bags_store/index.css" />
    <script src="https://fontawesome.com/" crossorigin="anonymous"></script>
    <link href="https://fontawesome.com/icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Document</title>
</head>

<body>
    <div class="container">
        <?php
        include ("./components/navbar.php");
        ?>

        <div class="max-w-3xl mx-auto px-4 py-8">
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Invoice</h3>
                </div>
                <div class="border-t border-gray-200">
                    <dl>
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Invoice Number</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2"><?php echo htmlspecialchars((string)$order_id); ?></dd>
                        </div>
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Date</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2"><?php echo htmlspecialchars((string)$order_row["createdDate"]); ?></dd>
                        </div>
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Customer Name</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2"><?php echo htmlspecialchars($order_row["firstName"] . " " . $order_row["lastName"]); ?></dd>
                        </div>
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Address</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2"><?php echo htmlspecialchars((string)$order_row["address"]); ?></dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- ===== HAVERSINE: Nearest Inventory ===== -->
            <?php if ($nearest_inventory): ?>
            <div class="mt-6 rounded-lg border border-purple-200 bg-purple-50 p-4">
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-purple-700 text-lg">&#128205;</span>
                    <h4 class="font-semibold text-purple-800 text-sm uppercase tracking-wide">Nearest Pickup Inventory</h4>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-base font-bold text-gray-900"><?php echo htmlspecialchars($nearest_inventory["inventory_name"]); ?></p>
                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($nearest_inventory["location_name"]); ?></p>
                    </div>
                    <span class="inline-block bg-purple-700 text-white text-sm font-semibold px-3 py-1 rounded-full">
                        <?php echo $nearest_inventory["distance_km"]; ?> km away
                    </span>
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
            <?php endif; ?>
            <!-- ===== END HAVERSINE ===== -->
            <div class="mt-8">
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Item
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Quantity
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Price
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php
                            // FIXED: use $order_items already fetched above with correct join (products.pnp)
                            $total = 0;
                            if ($order_items && $order_items->num_rows > 0) {
                                while ($row = $order_items->fetch_assoc()) {
                                    $cost   = $row["item_quantity"] * $row["price"];
                                    $total += $cost;
                            ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars((string)$row["name"]); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars((string)$row["item_quantity"]); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars((string)$row["price"]); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars((string)$cost); ?></td>
                                </tr>
                            <?php
                                }
                            } else { ?>
                                <tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">No items found.</td></tr>
                            <?php } ?>
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-right text-sm font-medium">Total:</td>
                                <td class="px-6 py-4 text-left text-sm font-medium"><?php echo htmlspecialchars((string)$total); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="mt-6 flex justify-end">
                <a class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" href="./pages/shop.php">
                    Browse More Products
                </a>
            </div>
        </div>
    </div>
</body>

</html>