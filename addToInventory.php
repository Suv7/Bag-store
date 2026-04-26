<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "crud"; // change this

$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$inventories = $conn->query("SELECT id, name FROM inventory");

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add Inventory Quantity</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="">
    <?php
    include("./components/adminNavbar.php");
    ?>
    <div class="px-32 py-10 space-y-5">

        <div class="flex ">
            <?php

            include("./components/adminSidebar.php");
            ?>
            <div class="bg-white p-6 rounded-2xl shadow w-96">
                <h1 class="text-xl font-semibold mb-4">Inventory Quantity</h1>

                <form method="POST" action="add_inventory_quantity.php" class="space-y-4">
                    <input type="number" name="product_id" hidden value="<?= $_POST["id"] ?>" />

                    <!-- Quantity -->
                    <div>
                        <label class="block text-sm font-medium mb-1">Quantity</label>
                        <input type="number" name="quantity" min="0" required
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:border-black focus:ring-0" />
                    </div>
                    <!-- Inventory Dropdown -->
                    <div>
                        <label class="block text-sm font-medium mb-1">Select Inventory</label>
                        <select name="inventory_id" required
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:border-black focus:ring-0">
                            <option>asfd</option>
                            <?php while ($row = $inventories->fetch_assoc()): ?>
                                <option value="<?= $row['id']; ?>">
                                    <?= htmlspecialchars($row['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>



                    <!-- Submit -->
                    <button type="submit"
                        class="w-full bg-black text-white rounded-xl py-2.5 font-medium hover:bg-gray-900 active:scale-[0.98] transition">
                        Save Quantity
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>

<?php $conn->close(); ?>