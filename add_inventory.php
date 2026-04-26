<?php
// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$db = "crud"; // change to your DB name

$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$message = "";
// Insert form data into inventory table
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $name = $_POST['name'];
  $locationId = $_POST['location_id'];

  $stmt = $conn->prepare("INSERT INTO inventory (name, location_id) VALUES (?, ?)");
  $stmt->bind_param("si", $name, $locationId);

  if ($stmt->execute()) {
    $message = "Inventory added successfully!";
    // header("Location : /")
  } else {
    echo " Error: " . $stmt->error;
  }

  $stmt->close();
}

// Fetch locations for dropdown
$locations = $conn->query("SELECT id, name FROM location");
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Add Inventory</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="">
  <?php
  include("./components/adminNavbar.php");
  ?>
  <div class="px-32 py-10 space-y-5">

    <div class="flex w-full">
      <?php

      include("./components/adminSidebar.php");
      ?>
      <div class="bg-white p-6 rounded-2xl shadow w-96">
        <h1 class="text-xl font-semibold mb-4">Add Inventory</h1>

        <form method="POST" action="add_inventory.php" class="space-y-4">
          <!-- Inventory Name -->
          <div>
            <label class="block text-sm font-medium mb-1">Inventory Name</label>
            <input type="text" name="name" required
              class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:border-black focus:ring-0" />
          </div>

          <!-- Location Dropdown -->
          <div>
            <label class="block text-sm font-medium mb-1">Select Location</label>
            <select name="location_id" required
              class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:border-black focus:ring-0">
              <option value="">-- Choose Location --</option>
              <?php while ($row = $locations->fetch_assoc()): ?>
                <option value="<?= $row['id']; ?>">
                  <?= htmlspecialchars($row['name']); ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>

          <!-- Submit -->
          <button type="submit"
            class="w-full bg-black text-white rounded-xl py-2.5 font-medium hover:bg-gray-900 active:scale-[0.98] transition">
            Save Inventory
          </button>
        </form>
        <?php
          if(isset($message))
            echo $message;
        ?>
      </div>
    </div>
  </div>
</body>

</html>

<?php $conn->close(); ?>