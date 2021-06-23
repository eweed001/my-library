<?php include("includes/init.php");

$title = "Home";
$nav_home_class = "active";
$page_url = "/";

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" type="text/css" href="/public/styles/site.css" />
  <title><?php echo $title; ?></title>
</head>

<body class="home_body">

  <?php include("includes/header.php"); ?>
  <!-- I drew this graphic -->
  <img src="/public/images/home_page_graphic.jpg" class="home_image" alt="computer with books graphic">
  <div class="home_content">
    <h1>Emily's Online Library Catalog</h1>
    <p>Welcome!</p>
    <p>You can find all the books listed on the catalog page. It will say whether or not the copy is available to borrow and you can click on the book for more information. Sign in above if you would like. Happy browsing! </p>
    <br />
    <br />
    <form action="/catalog" method="get" novalidate>
      <label for="search">Search The Catalog:</label>
      <input id="search" type="text" name="q" required />

      <button type="submit" class="button_text">Go</button>
    </form>
  </div>

</body>

</html>
