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

// If form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $name = $_POST['name'];
  $latitude = $_POST['latitude'];
  $longitude = $_POST['longitude'];

  // Prepare statement
  $stmt = $conn->prepare("INSERT INTO location (name, latitude, longitude) VALUES (?, ?, ?)");
  $stmt->bind_param("sdd", $name, $latitude, $longitude);

  if ($stmt->execute()) {
    $message = "Location added successfully!";
  } else {
    echo "Error: " . $stmt->error;
  }

  $stmt->close();
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Add Location</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<!-- min-h-screen bg-gray-50 flex items-center justify-center -->
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
        <h1 class="text-xl font-semibold mb-4">Add Location</h1>
        <form method="POST" action="add_location.php" class="space-y-4">
          <!-- Location Name -->
          <div>
            <label class="block text-sm font-medium mb-1">Location Name</label>
            <input type="text" name="name" required
              class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:border-black focus:ring-0" />
          </div>

          <!-- Latitude -->
          <div>
            <label class="block text-sm font-medium mb-1">Latitude</label>
            <input type="text" name="latitude" required
              class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:border-black focus:ring-0" />
          </div>

          <!-- Longitude -->
          <div>
            <label class="block text-sm font-medium mb-1">Longitude</label>
            <input type="text" name="longitude" required
              class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:border-black focus:ring-0" />
          </div>

          <!-- Submit Button -->
          <button type="submit"
            class="w-full bg-black text-white rounded-xl py-2.5 font-medium hover:bg-gray-900 active:scale-[0.98] transition">
            Save Location
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