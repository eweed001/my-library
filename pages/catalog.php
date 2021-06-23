<?php include("includes/init.php");

$title = "Catalog";
$nav_catalog_class = "active";


$current_user = current_user();

//setting some conditionals and variables for remembering searching, filtering and sorting
$sql_has_where = false;

$sql_base_query = 'SELECT books.*, genres.genre, book_covers.citation, book_covers.extension FROM books LEFT OUTER JOIN books_and_genres ON books.id = books_and_genres.book_id LEFT OUTER JOIN genres ON books_and_genres.genre_id = genres.id LEFT OUTER JOIN book_covers ON books.id = book_covers.book_id';

//Searching

//search values entered
$search_terms = Null;

$search_sticky = '';

$searched = False;
$sql_search = '';

$sql_params = array();

//checking if the search has been submitted
if (isset($_GET['q'])) {
  //trim the entered search term
  $search_terms = trim($_GET['q']);


  //checking if its empty
  if (empty($search_terms)) {
    $search_terms = NULL;
  } else {
    $searched = True;

    //setting to sticky
    $search_sticky = $search_terms; //tainted

    //sql
    $sql_search = "((books.title LIKE '%' || :term || '%') OR (books.author_first_name LIKE '%' || :term || '%') OR (books.author_last_name LIKE '%' || :term || '%') OR (books.ISBN LIKE '%' || :term || '%') OR (genres.genre LIKE '%' || :term || '%') OR (books.publication_year LIKE '%' || :term || '%') OR (books.average_rating LIKE '%' || :term || '%')) ";

    $sql_params = array(':term' => $search_terms);
  }
}



//Filtering


$filter_sticky = array();
$avail_sticky = '';
$not_avail_sticky = '';

//sql expressions
$sql_filter_expressions = '';
$should_filter_genre = False;
$filter_genre = false;
$should_filter_avail = False;
$filtering = False;
$has_or = False;
$sql_filter_query = '';
$sql_filter = '';

$availability = $_GET['availability'];

if (!empty($availability) || $availability == '0') {
  $should_filter_avail = True;
  $filtering = True;
}


$avail_sticky = ($availability == '1' ? 'checked' : '');
$not_avail_sticky = ($availability == '0' ? 'checked' : '');

foreach ($genres as $genre) {
  //converting to lower case
  $genre_parameter = strtolower($genre);

  $should_filter_genre = (bool)$_GET[$genre_parameter];
  if ((bool)$_GET[$genre_parameter]) {
    $filter_genre = True;
  }


  $filter_sticky[$genre_parameter] = ($should_filter_genre ? 'checked' : '');


  if ($should_filter_genre) {
    $filtering = True;
    if (!$has_or) {
      $has_or = True;
      $sql_filter_expressions = $sql_filter_expressions . ' (genres.genre == "' . $genre . '")';
    } else {
      $sql_filter_expressions = $sql_filter_expressions . ' OR (genres.genre == "' . $genre . '")';
    }
  }
}

if ($filtering) {

  if ($should_filter_avail && $filter_genre) {

    $sql_filter = "((" . $sql_filter_expressions . ") AND (books.available == '" . $availability . "'))";
  } else if (!$filter_genre && $should_filter_avail) {
    $sql_filter = "(books.available == " . $availability . ")";
  } else {
    $sql_filter = "(" . $sql_filter_expressions . ")";
  }
}


//Sorting

$sortings = array(
  "A-Z", "Z-A", "Highest Rated", "Lowest Rated", "Newest", "Oldest"
);

$sort = $_GET['sort']; //untrusted
$sort_sticky = $sort; //tainted

$sql_sort_query = '';

$sort_query_string = '';



$a_z_css_class = '';
$z_a_css_class = '';
$highest_css_class = '';
$lowest_css_class = '';
$recent_css_class = '';
$old_css_class = '';

if (!(empty($sort))) {
  if ($sort == 'A-Z') {
    $sql_sort_query = 'ORDER BY books.author_last_name ASC';
    $a_z_css_class = 'active';
    $sort_query_string = "&sort=a-z";
  } else if ($sort == 'Z-A') {
    $sql_sort_query = 'ORDER BY books.author_last_name DESC';
    $z_a_css_class = 'active';
    $sort_query_string = "&sort=z-a";
  } else if ($sort == 'Highest Rated') {
    $sql_sort_query = 'ORDER BY books.average_rating DESC';
    $highest_css_class = 'active';
    $sort_query_string = "&sort=highest";
  } else if ($sort == 'Lowest Rated') {
    $sql_sort_query = 'ORDER BY books.average_rating ASC';
    $lowest_css_class = 'active';
    $sort_query_string = "&sort=lowest";
  } else if ($sort == 'nNwest') {
    $sql_sort_query = 'ORDER BY books.publication_year DESC';
    $recent_css_class = 'active';
    $sort_query_string = "&sort=newest";
  } else if ($sort == 'Oldest') {
    $sql_sort_query = 'ORDER BY books.publication_year ASC';
    $old_css_class = 'active';
    $sort_query_string = "&sort=oldest";
  } else {
    $sort = Null; //in case of untrusted value
  }
}

// making searching, sorting, and filtering remember

//building base sql query
if (!(empty($sql_search)) && !(empty($sql_filter)) && !(empty($sql_sort_query))) {
  //need to do all three
  $sql_query = ($sql_base_query . " WHERE " . $sql_search . " AND " . $sql_filter . "  " . $sql_sort_query . ";");
} else if (!(empty($sql_search)) && !(empty($sql_filter)) && (empty($sql_sort_query))) {
  //just searching and filtering
  $sql_query = ($sql_base_query . " WHERE " . $sql_search . " AND " . $sql_filter . ";");
} else if (!(empty($sql_search)) && (empty($sql_filter)) && !(empty($sql_sort_query))) {
  //just searching and sorting
  $sql_query = ($sql_base_query . " WHERE " . $sql_search . "  " . $sql_sort_query . ";");
} else if ((empty($sql_search)) && !(empty($sql_filter)) && !(empty($sql_sort_query))) {
  // just sorting and filtering
  $sql_query = ($sql_base_query . " WHERE " . $sql_filter . "  " . $sql_sort_query . ";");
} else if (!(empty($sql_search)) && (empty($sql_filter)) && (empty($sql_sort_query))) {
  //just searching
  $sql_query = ($sql_base_query . " WHERE " . $sql_search .  ";");
} else if ((empty($sql_search)) && !(empty($sql_filter)) && (empty($sql_sort_query))) {
  //just filtering
  $sql_query = ($sql_base_query . " WHERE " . $sql_filter .  ";");
  // echo $sql_query;
} else if ((empty($sql_search)) && (empty($sql_filter)) && !(empty($sql_sort_query))) {
  //just sorting
  $sql_query = ($sql_base_query . "  " . $sql_sort_query .  ";");
  // echo $sql_query;
} else {
  //none of the above, i.e. no filtering, sorting, or searching
  $sql_query = ($sql_base_query . ";");
  // echo $sql_query;
}

// remember searches and filtering by building the query string URL

// url for query string params.
$query_string_url = '/catalog?';


// filter and search params to query string
$filter_query_string = http_build_query(
  array(
    'q' => $search_terms,
    'fiction' => (bool)$_GET["fiction"],
    'mystery' => (bool)$_GET["mystery"],
    'thriller' => (bool)$_GET["thriller"],
    'non-fiction' => (bool)$_GET["non-fiction"],
    'horror' => (bool)$_GET["horror"],
    'romance' => (bool)$_GET["romance"],
    'sci-fi' => (bool)$_GET["sci-fi"],
    'fantasy' => (bool)$_GET["fantasy"],
    'other' => (bool)$_GET["other"],
    'availability' => $_GET["availability"]
  )
);

// concatenating
$query_string_url = $query_string_url . $filter_query_string . $sort_query_string;

$page_url = "'" . $query_string_url . "'";

?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" type="text/css" href="/public/styles/site.css" />

  <title>Catalog</title>
</head>

<body>
  <?php include("includes/header.php"); ?>
  <div class="banner"></div>
  <section>

    <!-- Search function -->
    <div class="search_form">
      <form action="/catalog" method="get" novalidate>
        <label for="search">Search:</label>

        <input id="search" type="text" name="q" required value="<?php echo htmlspecialchars($search_sticky); ?>" />
        <button type="submit" class="button_text">Go</button>

        <!-- remembering the filtering -->
        <input type="hidden" name="sort" value="<?php echo $sort; ?>" />

        <input type="hidden" name="fiction" value="<?php echo (bool)$_GET['fiction']; ?>" />
        <input type="hidden" name="mystery" value="<?php echo (bool)$_GET['mystery']; ?>" />
        <input type="hidden" name="thriller" value="<?php echo (bool)$_GET['thriller']; ?>" />
        <input type="hidden" name="non-fiction" value="<?php echo (bool)$_GET['non-fiction']; ?>" />
        <input type="hidden" name="horror" value="<?php echo (bool)$_GET['horror']; ?>" />
        <input type="hidden" name="romance" value="<?php echo (bool)$_GET['romance']; ?>" />
        <input type="hidden" name="sci-fi" value="<?php echo (bool)$_GET['sci-fi']; ?>" />
        <input type="hidden" name="fantasy" value="<?php echo (bool)$_GET['fantasy']; ?>" />
        <input type="hidden" name="other" value="<?php echo (bool)$_GET['other']; ?>" />
        <input type="hidden" name="availability" value="<?php echo $_GET['availability']; ?>" />


      </form>
    </div>



  </section>

  <div class="sidebar">

    <form class="filter" action="/catalog" method="get" novalidate>
      <h3>Filter By Genre:</h3>

      <?php
      foreach ($genres as $genre) {
        //converting all values to lower cases (there's no spaces in the genre values)
        $genre_parameter = strtolower($genre);
      ?>
        <label> <input type="checkbox" name="<?php echo htmlspecialchars($genre_parameter); ?>" <?php echo htmlspecialchars($filter_sticky[$genre_parameter]); ?> value="1" />
          <?php echo htmlspecialchars($genre) ?>
        </label>
        <br />
      <?php } ?>
      <br />
      Availability:
      <br />
      <input id="available" type="radio" name="availability" value="1" <?php echo $avail_sticky; ?> />
      <label for="available">Available</label>
      <input id="notavailable" type="radio" name="availability" value="0" <?php echo $not_avail_sticky; ?> />
      <label for="notavailable">Not Available</label>

      <br />
      <br />
      <button type="submit">Filter</button>

      <!-- remembering the sorting and the searching, hidden input ideas is from
this past weeks modules -->
      <input type="hidden" name="sort" value="<?php echo $sort; ?>" />

      <input type="hidden" name="q" value="<?php echo htmlspecialchars($search_terms); ?>" />

    </form>
  </div>

  <div class="main">
    <!-- Sorting -->
    <div class="sorting">
      <form action="/catalog" method="get" novalidate>
        <label for="sorting-select">Sorting:</label>
        <select name="sort" id="sorting-select">
          <?php
          if (empty($sort_sticky)) {
            echo "<option value = '' selected>Choose one</option>";
          }
          foreach ($sortings as $sorting) {
            if ($sort_sticky == $sorting) {
              echo "<option value = '" . htmlspecialchars($sort_sticky) . "' selected>" . htmlspecialchars($sorting) . "</option>";
            } else {
              echo "<option value = '" . htmlspecialchars($sorting) . "' >" . htmlspecialchars($sorting) . "</option>";
            }
          } ?>

        </select>
        <input type="submit" value="sort" />
        <input type="hidden" name="fiction" value="<?php echo (bool)$_GET['fiction']; ?>" />
        <input type="hidden" name="mystery" value="<?php echo (bool)$_GET['mystery']; ?>" />
        <input type="hidden" name="thriller" value="<?php echo (bool)$_GET['thriller']; ?>" />
        <input type="hidden" name="non-fiction" value="<?php echo (bool)$_GET['non-fiction']; ?>" />
        <input type="hidden" name="horror" value="<?php echo (bool)$_GET['horror']; ?>" />
        <input type="hidden" name="romance" value="<?php echo (bool)$_GET['romance']; ?>" />
        <input type="hidden" name="sci-fi" value="<?php echo (bool)$_GET['sci-fi']; ?>" />
        <input type="hidden" name="fantasy" value="<?php echo (bool)$_GET['fantasy']; ?>" />
        <input type="hidden" name="other" value="<?php echo (bool)$_GET['other']; ?>" />
        <input type="hidden" name="availability" value="<?php echo $_GET['availability']; ?>" />
        <input type="hidden" name="q" value="<?php echo htmlspecialchars($search_terms); ?>" />
      </form>
    </div>
    <h2>Books Catalog</h2>


    <?php
    //query books based on my constructed sql query
    $result = exec_sql_query($db, $sql_query, $sql_params);
    $books = $result->fetchAll();

    $id_list = (array_column($books, 'id'));
    $books_displayed = array();



    ?>
    <?php
    // check if search has been submitted so i can display this to designate
    // that these are search results
    if ($searched) {
      echo "Search Results:";
    }
    if (empty($books)) {
      echo " No Results";
    } else {
      //display each book
    ?>

      <div class="tiled">
        <?php foreach ($books as $book) {
          //checking if we have already displayed this book
          //if we have already displayed it, then skip to next loop
          if (in_array($book['id'], $books_displayed)) {
            continue;
          } else {
            //add it to the books displayed array then display it
            array_push($books_displayed, $book['id']);
        ?>
            <div class="tile">
              <div class="avail_top">
                <?php if ($book['available'] == 1) { ?>
                  Available
                <?php } else { ?>
                  Not Available
                <?php } ?>
              </div>
              <a href="/catalog/full-record?id=<?php echo htmlspecialchars($book['id']) ?>"><img class="book_cover" src='public/uploads/book_covers/<?php echo htmlspecialchars($book['id']); ?>.<?php echo htmlspecialchars($book['extension']); ?>' alt="cover art"></a>
              <div class="right_content">
                <p><em><?php echo htmlspecialchars($book['publication_year']); ?></em>
                </p>
                <p><em>
                    <?php echo htmlspecialchars($book['average_rating']); ?> </em>
                </p>
              </div>
              <div class="title">
                <p><strong><?php echo htmlspecialchars($book['title']); ?></strong></p>
              </div>

              <!-- dealing with books with multiple genres -->
              <!-- if the book id is listed multiple times in our id list from the query
                this indicates that the book has multiple genres -->
              <?php if (array_count_values($id_list)[$book['id']] > 1) {
                // need to loop through all the
                // books again to find the duplicate and display the all the genres
              ?>
                <?php foreach ($books as $book_2) {
                  // if the book 2 matches the book from our big foreach loop then
                  // show the corresponding genre
                  if ($book_2['id'] == $book['id']) {
                ?>
                    <div class="tags <?php echo htmlspecialchars(strtolower($book_2['genre'])); ?>"><?php echo htmlspecialchars($book_2['genre']); ?></div>
                <?php }
                }
              } else {
                //doesn't have duplicate so just show the one genre
                ?>
                <div class="tags <?php echo htmlspecialchars(strtolower($book['genre'])); ?>"><?php echo htmlspecialchars($book['genre']); ?></div>
              <?php
              } ?>
              <div class="cover-citation">
                <!-- source: citation link in the book_covers table. All the seed data book covers are  from goodreads.com. see the links in the database for each individual URL -->
                <p><a href="<?php echo htmlspecialchars($book['citation']); ?>">Cover Image Source</a></p>
              </div>
              <?php
              if ($is_librarian) {
                $edit_url = "/catalog/full-record?" . http_build_query(array('id' => $book['id'], 'edit' => 'true')); ?>
                <div class="edit">
                  <p><a href="<?php echo $edit_url; ?>">EDIT</a></p>
                </div>
            <?php }
            } ?>
            </div>

          <?php } ?>

        <?php } ?>

      </div>

  </div>

</body>

</html>
