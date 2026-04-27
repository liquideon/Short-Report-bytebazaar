<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
// SESSION SECURITY CHECK

// First time user loads page → store IP + browser
if (!isset($_SESSION['ip'])) {
    $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
}

// Every request → verify it matches
if ($_SESSION['ip'] !== $_SERVER['REMOTE_ADDR'] ||
    $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {

    // Destroy session if mismatch (possible hijack)
    session_unset();
    session_destroy();

    // Send user back to login
    header("Location: login.php");
    exit();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (isset($_POST['submit'])) {
  include("include/connect.php");
  //  VALIDATE PRODUCT ID
$pid = filter_input(INPUT_GET, 'pid', FILTER_VALIDATE_INT);
if ($pid === false || $pid <= 0) {
    die("Invalid product ID");
}

//  VALIDATE USER SESSION
$aid = isset($_SESSION['aid']) ? (int)$_SESSION['aid'] : 0;
if ($aid <= 0) {
    header("Location: login.php");
    exit();
}

//  VALIDATE QUANTITY
$qty = filter_input(INPUT_POST, 'qty', FILTER_VALIDATE_INT);
if ($qty === false || $qty < 1 || $qty > 10) {
    $qty = 1; // safe default
}



  $stmt = $con->prepare("SELECT * FROM cart WHERE aid = ? AND pid = ?");
$stmt->bind_param("ii", $aid, $pid);
$stmt->execute();
$result = $stmt->get_result();
  $row = mysqli_fetch_assoc($result);

  if ($row) {
    echo "<script> alert('item already added to cart') </script>";

    header("Location: cart.php");
    exit();
  } else {

    $stmt = $con->prepare("INSERT INTO cart (aid, pid, cqty) VALUES (?, ?, ?)");
$stmt->bind_param("iii", $aid, $pid, $qty);
$stmt->execute();
    header("Location: shop.php");
    exit();
  }

}
if (isset($_GET['w'])) {
  include("include/connect.php");
  $aid = $_SESSION['aid'];
  if ($aid < 0) {
    header("Location: login.php");
    exit();
  }
  $pid = $_GET['w'];

  $stmt = $con->prepare("INSERT INTO wishlist (aid, pid) VALUES (?, ?)");
$stmt->bind_param("ii", $aid, $pid);
$stmt->execute();

  $result = mysqli_query($con, $query);
  header("Location: sproduct.php?pid=$pid");
  exit();
}
if (isset($_GET['nw'])) {
  include("include/connect.php");
  $aid = $_SESSION['aid'];
  $pid = $_GET['nw'];

  $stmt = $con->prepare("DELETE FROM wishlist WHERE aid = ? AND pid = ?");
$stmt->bind_param("ii", $aid, $pid);
$stmt->execute();

  $result = mysqli_query($con, $query);
  header("Location: sproduct.php?pid=$pid");
  exit();
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ByteBazaar</title>
  <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />
  <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />

  <link rel="stylesheet" href="style.css" />

  <style>
    .heart {
      margin-left: 25px;
      display: inline-flex;
      justify-content: center;
      align-items: center;
    }
    .star i {
  font-size: 12px;
  color: rgb(243, 181, 25);
}

.tb {
        max-height: 400px;
        overflow-x: auto;
        overflow-y: auto;
    }



    .tb tr {
        height: 60px;
        margin: 10px;
    }

    .tb td {
        text-align: center;
        margin: 10px;
        padding-left: 40px;
        padding-right: 40px;
    }

    .rev{
      margin: 70px;
    }

  </style>

</head>

<body>
  <section id="header">
    <a href="index.php"><img src="img/logo.png" class="logo" alt="" /></a>

    <div>
      <ul id="navbar">
        <li><a href="index.php">Home</a></li>
        <li><a class="active" href="shop.php">Shop</a></li>
        <li><a href="about.php">About</a></li>
        <li><a href="contact.php">Contact</a></li>

        <?php

        if ($_SESSION['aid'] < 0) {
          echo "   <li><a href='login.php'>login</a></li>
            <li><a href='signup.php'>SignUp</a></li>
            ";
        } else {
          echo "   <li><a href='profile.php'>profile</a></li>
          ";
        }
        ?>
        <li><a href="admin.php">Admin</a></li>
        <li id="lg-bag">
          <a href="cart.php"><i class="far fa-shopping-bag"></i></a>
        </li>
        <a href="#" id="close"><i class="far fa-times"></i></a>
      </ul>
    </div>
    <div id="mobile">
      <a href="cart.php"><i class="far fa-shopping-bag"></i></a>
      <i id="bar" class="fas fa-outdent"></i>
    </div>
  </section>

  <?php
  include("include/connect.php");

  if (isset($_GET['pid'])) {
    $pid = isset($_GET['pid']) ? (int)$_GET['pid'] : 0;

	$stmt = $con->prepare("SELECT * FROM products WHERE pid = ?");
	$stmt->bind_param("i", $pid);
	$stmt->execute();
	$result = $stmt->get_result();
    $row = mysqli_fetch_assoc($result);
	if (!$row) {
    	echo "Product not found";
    	exit();
		}

    $pidd = $row['pid'];
    $pname = $row['pname'];

    $desc = $row['description'];
    $qty = $row['qtyavail'];
    $price = $row['price'];
    $cat = $row['category'];
    $img = $row['img'];
    $brand = $row['brand'];

    $aid = $_SESSION['aid'];
    $query = "select * from wishlist where aid = $aid and pid = $pid";
    $result = mysqli_query($con, $query);
    $row = mysqli_fetch_assoc($result);


  ?>

<section id="prodetails" class="section-p1">
<div class="single-pro-image">
    <img src="product_images/<?php echo $img; ?>" width="100%" id="MainImg" alt="">
</div>

<div class="single-pro-details">
    <h2><?php echo htmlspecialchars($pname, ENT_QUOTES, 'UTF-8'); ?></h2>
    <h4><?php echo htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') . " - " . htmlspecialchars($brand, ENT_QUOTES, 'UTF-8'); ?></h4>
    <h4>$<?php echo $price; ?></h4>

    <form method="post">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="number" name="qty" value="1" min="1" max="<?php echo $qty; ?>">
        <button class="normal" name="submit">Add to Cart</button>
    </form>

    <h4>Product Details</h4>
    <span><?php echo htmlspecialchars($desc, ENT_QUOTES, 'UTF-8'); ?></span>
</div>

<?php

 if ($row) {
    echo "<a class='heart' href='sproduct.php?nw=$pid'><img src='img/full.png' style='margin:auto;width:40px;height:40px;' alt='' /></a>";
} else {
    echo "<a class='heart' href='sproduct.php?w=$pid'><img src='img/empty.png' style='margin:auto;width:40px;height:40px;' alt='' /></a>";
}

?>
</form>
<h4>Product Details</h4>
<span><?php echo $desc; ?></span>
<?php

   

  echo "</div></section>";
}

$stmt = $con->prepare("
SELECT * FROM reviews 
JOIN orders ON reviews.oid = orders.oid 
JOIN accounts ON orders.aid = accounts.aid 
WHERE reviews.pid = ?
");
$stmt->bind_param("i", $pid);
$stmt->execute();
$result = $stmt->get_result();
$result = mysqli_query($con, $query);

$row = mysqli_fetch_assoc($result);

if (!empty($row))
{
  $result = mysqli_query($con, $query);

echo "
<div class = 'rev'>
<h2>Reviews</h2>
<div class='tb'>
<table><thead><tr><th>username</th>
<th style='min-width: 100px;'>rating</th>
<th>text</th></thead><tbody>";

while ($row = mysqli_fetch_assoc($result)) {
  $user = $row['username'];
  $rtext = $row['rtext'];
  $stars = $row['rating'];

  $empty = 5 - $stars;

 echo "<td>" . htmlspecialchars($user, ENT_QUOTES, 'UTF-8') . "</td>";
echo "<td style='min-width: 200px;'><div class='star'>";
  for ($i = 1; $i <= $stars; $i++) {
    echo "<i class='fas fa-star'></i>";

  }
  for ($i = 1; $i <= $empty; $i++) {
    echo "<i class='far fa-star'></i>";

  }
  echo "</div></td>";
echo "<td><span>" . htmlspecialchars($rtext, ENT_QUOTES, 'UTF-8') . "</span></td></tr>";
}

echo "</tbody></table></div></div>";

}
  ?>


  <footer class="section-p1">
    <div class="col">
      <img class="logo" src="img/logo.png" />
      <h4>Contact</h4>
      <p>
        <strong>Address: </strong> Street 2, Johar Town Block A,Lahore

      </p>
      <p>
        <strong>Phone: </strong> +92324953752
      </p>
      <p>
        <strong>Hours: </strong> 9am-5pm
      </p>
    </div>

    <div class="col">
      <h4>My Account</h4>
      <a href="cart.php">View Cart</a>
      <a href="wishlist.php">My Wishlist</a>
    </div>
    <div class="col install">
      <p>Secured Payment Gateways</p>
      <img src="img/pay/pay.png" />
    </div>
    <div class="copyright">
      <p>2021. byteBazaar. HTML CSS </p>
    </div>
  </footer>

  <script>
    var MainImg = document.getElementById("MainImg");
    var smallimg = document.getElementsByClassName("small-img");

    smallimg[0].onclick = function () {
      MainImg.src = smallimg[0].src;
    };
    smallimg[1].onclick = function () {
      MainImg.src = smallimg[1].src;
    };
    smallimg[2].onclick = function () {
      MainImg.src = smallimg[2].src;
    };
    smallimg[3].onclick = function () {
      MainImg.src = smallimg[3].src;
    };
  </script>
  <script src="script.js"></script>
</body>

</html>

<script>
    window.addEventListener("unload", function () {
      // Call a PHP script to log out the user
      var xhr = new XMLHttpRequest();
      xhr.open("GET", "logout.php", false);
      xhr.send();
    });
  </script>
