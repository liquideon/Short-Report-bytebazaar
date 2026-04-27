<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include("include/connect.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ByteBazaar Shop</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<h2>Shop</h2>

<!-- 🔍 SEARCH FORM -->
<form method="POST" action="shop.php">
    <input type="text" name="search" placeholder="Search products...">

    <select name="cat">
        <option value="all">All</option>
        <option value="laptop">Laptop</option>
        <option value="phone">Phone</option>
        <option value="accessories">Accessories</option>
    </select>

    <button type="submit" name="search1">Search</button>
</form>

<hr>

<?php

// 🧠 STEP 1: Check if search was submitted
if (isset($_POST['search1'])) {

    $search = $_POST['search'];
    $category = $_POST['cat'];

    // 🧠 STEP 2: Base query
    $query = "SELECT * FROM products WHERE 1";

    // 🧠 STEP 3: Add search condition
    if (!empty($search)) {
        $query .= " AND (
            pname LIKE '%$search%' OR 
            brand LIKE '%$search%' OR 
            description LIKE '%$search%'
        )";
    }

    // 🧠 STEP 4: Add category filter
    if ($category != "all") {
        $query .= " AND category = '$category'";
    }

} else {
    // 🟢 DEFAULT (no search)
    $query = "SELECT * FROM products WHERE qtyavail > 0";
}

// 🧠 STEP 5: Run query
$result = mysqli_query($con, $query);

// 🧪 DEBUG (optional — you can remove later)
echo "<p>Number of results: " . mysqli_num_rows($result) . "</p>";

// 🧠 STEP 6: Display products
if ($result && mysqli_num_rows($result) > 0) {

    while ($row = mysqli_fetch_assoc($result)) {

        $pname = $row['pname'];
        $price = $row['price'];
        $img = $row['img'];
	$pid = $row['pid'];

echo "
<a href='sproduct.php?pid=$pid' style='text-decoration:none; color:black;'>
<div style='border:1px solid #ccc; padding:10px; margin:10px; width:200px; display:inline-block;'>
    <img src='product_images/$img' width='150'><br>
    <strong>$pname</strong><br>
    Price: $price
</div>
</a>
";
    }

} else {
    echo "<p>No products found.</p>";
}

?>

</body>
</html>

