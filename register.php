<?php
include("config.php");

if (isset($_POST["submit"])) {
    $firstName = $_POST["firstName"];
    $lastName = $_POST["lastName"];
    $address = $_POST["address"];
    $username = $_POST["username"];
    $password = md5($_POST["password"]);
    $email = $_POST["email"];
    $phone = $_POST["mobile"];

    $error = [];


    $userResult = $conn->query("SELECT * FROM users where username='$username'");

    if (empty($firstName)) {
        $error["firstName"] = "First Name mustn't be empty";
    }

    if (empty($lastName)) {
        $error["lastName"] = "Last Name mustn't be empty";
    }

    if (empty($address)) {
        $error["address"] = "Address mustn't be empty";
    }

    if (empty($password)) {
        $error["password"] = "Password mustn't be empty";
    }

    if (empty($phone)) {
        $error["phone"] = "phone number  mustn't be empty";
    }

    if ($userResult->num_rows > 0) {
        $error["username"] = "Username already exists";
    }

    if (!preg_match("/^[a-zA-Z0-9]+@[a-zA-Z0-9]+.(com|org|net)$/i", $email)) {
        $error["email"] = "Email invalid";
    }

    $emailResult = $conn->query("SELECT * FROM users where email='$email'");
    if ($emailResult->num_rows > 0) {
        $error["email"] = "Email already exists";
    }

    if (count($error) == 0) {

        $sql = "insert into users(firstName,lastName,address,username,password,email,phoneNumber) values(?,?,?,?,?,?,?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssss", $firstName, $lastName, $address, $username, $password, $email, $phone);
        $stmt->execute();


        header("Location: login.php");
    }
}

$locations = $conn->query("SELECT id, name FROM location");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="css/login.css">
    <script src="https://cdn.tailwindcss.com"></script>

</head>

<body>
    <div class="container register">
        <div class="box form-box w-[700px]  shadow-xl mx-auto px-10 py-5 mt-10 ">
            <header class="text-3xl font-bold text-center mb-5 text-[#902c7e]">Register</header>
            <div class="wrapper">
                <form action="" method="post" class="grid grid-cols-2 gap-8">
                    <div class="field  input  space-y-3">
                        <label for="firstName" class="text-[#902c7e]">First Name</label>
                        <input type="text" name="firstName" id="firstName" placeholder="First Name" required
                            class="w-full px-3 py-2 border-2 rounded-lg">
                        <p class="text-red-900"><?php if (isset($error["firstName"]))
                            echo $error["firstName"]; ?></p>

                    </div>

                    <div class="field  input space-y-3 ">
                        <label class="text-[#902c7e]" for="lastName">Last Name</label>
                        <input type="text" name="lastName" id="lastName" placeholder="Last Name" required
                            class="w-full px-3 py-2 border-2 rounded-lg">
                        <p class="text-red-900"><?php if (isset($error["lastName"]))
                            echo $error["lastName"]; ?></p>


                    </div>

                    <div class="field  input space-y-3">
                        <label class="text-[#902c7e]" for="address">Address</label>
                        <input type="text" name="address" id="address" placeholder="Address " required
                            class="w-full px-3 py-2 border-2 rounded-lg">

                        <p class="text-red-900"><?php if (isset($error["address"]))
                            echo $error["lastName"]; ?></p>


                    </div>
                    <div class="field  input space-y-3">
                        <label class="text-[#902c7e]" for="mobile">Phone Number</label>
                        <input type="number" name="mobile" id="mobile" placeholder="Phone Number" required
                            class="w-full px-3 py-2 border-2 rounded-lg">
                        <p class="text-red-900"><?php if (isset($error["phone"]))
                            echo $error["lastName"]; ?></p>

                    </div>

                    <div class="field  input space-y-3 col-span-full">
                        <label class="text-[#902c7e]" for="email">Email</label>
                        <input type="email" name="email" id="email" placeholder="Email" required
                            class="w-full px-3 py-2 border-2 rounded-lg">
                        <p class="text-red-900"><?php if (isset($error["email"]))
                            echo $error["email"]; ?></p>

                    </div>
                    <div class="field  input space-y-3">
                        <label class="text-[#902c7e]" for="email">username</label>
                        <input type="username" name="username" id="email" placeholder="username" required
                            class="w-full px-3 py-2 border-2 rounded-lg">
                        <p class="text-red-900"><?php if (isset($error["username"]))
                            echo $error["username"]; ?></p>

                    </div>


                    <div class="field  input space-y-3">
                        <label class="text-[#902c7e]" for="password">Password</label>
                        <input type="password" name="password" id="password" placeholder="Password" required
                            class="w-full px-3 py-2 border-2 rounded-lg">
                        <p class="text-red-900"><?php if (isset($error["password"]))
                            echo $error["password"]; ?></p>


                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Select Location</label>
                        <select name="location_id" required
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:border-black focus:ring-0">
                            <?php while ($row = $locations->fetch_assoc()): ?>
                                <option value="<?= $row['id']; ?>">
                                    <?= htmlspecialchars($row['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="field col-span-full justify-self-end">
                        <input
                            class="capitalize border-2 px-5 py-1 border-[#902c7e] text-[#902c7e] rounded-lg hover:text-white hover:bg-[#902c7e] duration-300 hover:shadow-xl cursor-pointer"
                            type="submit" class="btn" name="submit" value="sign Up" autocomplete="off" required>
                    </div>

                    <div class="links col-span-full text-center">
                        Already a member? <a href="login.php"
                            class="text-purple-900 hover:text-purple-700 hover:underline duration-300">Sign In</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>

</html>