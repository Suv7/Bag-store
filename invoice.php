<?php

include ("config.php");

if (!isset($_GET["order_id"]))
    header("location: index.php");


if(session_status() !== PHP_SESSION_ACTIVE) session_start();
$order_id = $_GET["order_id"];
// echo "Order Id:".$order_id;
$uid = $_SESSION["uid"];

$order = $conn->query("SELECT * FROM orders INNER JOIN users ON orders.uid=users.id WHERE orders.id=$order_id and users.id=$uid ORDER BY createdDate DESC LIMIT 1");
if ($order->num_rows == 0)
    header("location : index.php");

$order_row = $order->fetch_assoc();

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
                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2"><?= $order_id ?></dd>
                        </div>
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Date</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2"><?= $order_row["createdDate"] ?></dd>
                        </div>
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Customer Name</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2"><?= $order_row["firstName"]." ".$order_row["lastName"] ?></dd>
                        </div>
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Address</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2"><?= $order_row["address"] ?></dd>
                        </div>
                    </dl>
                </div>
            </div>
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
                            $order_items = $conn->query("SELECT *,order_product.quantity as item_quantity FROM order_product INNER JOIN products ON order_product.bid=products.id join product_name_price pnp on pnp.id=products.id where oid=$order_id") or die("" . $conn->error);
                            $total = 0;
                            while ($row = $order_items->fetch_assoc()) {
                                $cost = $row["item_quantity"]*$row["price"];
                                $total += $cost;
                                ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?=$row["name"] ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?=$row["item_quantity"]?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?=$row["price"] ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?= $cost?>
                                    </td>
                                </tr>
                                <?php
                            }
                            ?>

                            <!-- Add more rows for additional bill items if needed -->
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-right text-sm font-medium">Total:</td>
                                <td class="px-6 py-4 text-left text-sm font-medium"><?=$total?></td>
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