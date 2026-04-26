<?php
session_start();

if (!isset($_SESSION["username"]) || !isset($_SESSION["uid"]) || !isset($_SESSION["role"]))
    header("location: index.php");

if ($_SESSION["role"] !== "admin")
    header("location: index.php");

$conn = new mysqli('localhost', 'root', '', 'crud');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT id, name FROM inventory";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body>
    <?php
    include("./components/adminNavbar.php");
    ?>
    <div class="px-32 py-10 space-y-5">

        <div class="flex w-full">
            <?php

            include("./components/adminSidebar.php");
            ?>

            <div class="px-20">
                <h1 class="text-3xl font-bold text-[#902c7e]"> Add Products </h1>
                <form action="prodAdd.php" method="POST" enctype="multipart/form-data"
                    class="space-y-8 flex flex-col w-[500px]  py-10">
                    <div>
                        <label class=""> Name </label> <br />
                        <input type="text" name="name" class="w-full border-2 rounded-lg py-2 px-1 " required />
                    </div>

                    <div>
                        <label> price </label>
                        <input type="number" name="price" class="w-full border-2 rounded-lg py-2 px-1 " required />

                    </div>

                    <!-- <div>
                        <label for="inventoryNumber" class="block text-sm font-medium text-gray-700 mb-1">
                            Inventory Number
                        </label>
                        <input type="number" id="inventoryNumber" name="inventoryNumber" inputmode="numeric" min="0"
                            step="1" required
                            class="w-full rounded-xl border border-gray-300 focus:border-black focus:ring-0 px-4 py-2.5 placeholder-gray-400"
                            placeholder="e.g., 100234" />
                        <p class="mt-1 text-xs text-gray-500">Unique number for the inventory item.</p>
                    </div> -->

                    <div class="">
                        <label for="inventory" class="block text-sm font-medium text-gray-700 mb-2">
                            Select Inventory
                        </label>
                        <select id="inventory" name="inventoryNumber"
                            class="w-full rounded-xl border border-gray-300 focus:border-black focus:ring-0 px-4 py-2.5"
                            required>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <option value="<?= $row['id']; ?>"><?= htmlspecialchars($row['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- Product Total Count -->
                    <!-- <div>
                        <label for="productTotalCount" class="block text-sm font-medium text-gray-700 mb-1">
                            Product Total Count
                        </label>
                        <input type="number" id="productTotalCount" name="productTotalCount" inputmode="numeric" min="0"
                            step="1" required
                            class="w-full rounded-xl border border-gray-300 focus:border-black focus:ring-0 px-4 py-2.5 placeholder-gray-400"
                            placeholder="e.g., 50" />
                        <p class="mt-1 text-xs text-gray-500">How many units are available.</p>
                    </div> -->
                    <div>
                        <label> brand </label>
                        <input type="text" name="brand" class="w-full border-2 rounded-lg py-2 px-1 " required />

                    </div>

                    <div>
                        <label> quantity </label>
                        <input type="number" name="quantity" class="w-full border-2 rounded-lg py-2 px-1 " required />
                    </div>
                    <input type="file" name="image" class="text-white" required />
                    <button type="submit" name="submit"
                        class="w-full py-3 border-2 border-green-500 rounded-lg text-green-500 hover:bg-green-500 hover:text-white duration-300">
                        add </button>
                </form>

            </div>
        </div>
    </div>
</body>

</html>