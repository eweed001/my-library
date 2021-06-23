<?php include("includes/init.php");
// this page will have the code to show the full record and the user will be
// redirected here when they click on a record from the catalog page

$title = "Book Details";
$nav_catalog_class = "active";

$show_record = True;
$show_edit_confirmation = False;
$delete_confirmation = false;

$book_id = (int)trim($_GET['id']);

// determining if we are in edit mode
$edit_mode = (bool)$_GET['edit'];

$url = "/catalog/full-record?" . http_build_query(array('id' => $book_id));
$page_url = "'" . $url . "'";
$current_user = current_user();

//finding the book
$query = "SELECT books.*, genres.genre, book_covers.citation, book_covers.extension FROM books LEFT OUTER JOIN books_and_genres ON books.id = books_and_genres.book_id LEFT OUTER JOIN genres ON books_and_genres.genre_id = genres.id LEFT OUTER JOIN book_covers ON books.id = book_covers.book_id WHERE books.id = :bookid;";

$record = exec_sql_query($db, $query, array(':bookid' => $book_id))->fetchAll();


$update_form_valid = true;

if ($edit_mode) {
  $url = $url . '&' . http_build_query(array('edit' => 'true'));

  //setting sticky values for edit mode

  $first_name_feedback_class = 'hidden';
  $last_name_feedback_class = 'hidden';
  $title_feedback_class = 'hidden';
  $isbn_feedback_class = 'hidden';
  $isbn_int_feedback_class = 'hidden';
  $genre_feedback_class = 'hidden';
  $pub_year_feedback_class = 'hidden';
  $pub_year_int_feedback_class = 'hidden';
  $rating_feedback_class = 'hidden';
  $rating_number_feedback_class = 'hidden';
  $available_feedback_class  = 'hidden';


  $sticky_title = $record[0]['title'];
  $sticky_first_name = $record[0]['author_first_name'];
  $sticky_last_name = $record[0]['author_last_name'];
  $sticky_isbn = $record[0]['isbn'];
  $sticky_pub_year = $record[0]['publication_year'];
  $sticky_rating = $record[0]['average_rating'];
  $sticky_available = $record[0]['availability'] == 1 ? 'checked' : '';
  $sticky_not_available = $record[0]['availability'] == 0 ? 'checked' : '';
  $sticky_description = $record[0]['description'];
  $sticky_genres = array();
  $record_genres = array();
  $i = 0;
  foreach ($record as $r) {
    $record_genres[$i] = $r['genre'];
    $i = $i + 1;
  }
  foreach ($genres as $genre) {
    $sticky_genres[$genre] = (in_array($genre, $record_genres) ? 'checked' : '');
  }


  //actually preform the updating..

  if (isset($_POST['update'])) {
    //trimming spaces

    $first_name = trim($_POST['first_name']); //untrusted
    $last_name = trim($_POST['last_name']); //untrusted
    $book_title = trim($_POST['title']); //untrusted
    $isbn = trim($_POST['isbn']); //untrusted
    $record_genres_edit = ($_POST['genre']); //untrusted
    $pub_year = trim($_POST['pub_year']); //untrusted
    $rating = trim($_POST['avg_rating']); //untrusted
    $availability = trim($_POST['availability']); //untrusted
    $description = trim($_POST['description']); //untrusted

    //updating the sticky values to be the ones entered
    $sticky_title = $book_title;
    $sticky_first_name = $first_name;
    $sticky_last_name = $last_name;
    $sticky_isbn = $isbn;
    $sticky_pub_year = $pub_year;
    $sticky_rating = $rating;
    $sticky_available = ($availability == "Yes" ? 'checked' : '');
    $sticky_not_available = ($availability == "No" ? 'checked' : '');
    $sticky_description = $description;
    $sticky_genres = array();

    foreach ($genres as $genre) {
      $sticky_genres[$genre] = $record_genres_edit[strtolower($genre)] == '1' ? 'checked' : '';
    }

    //Every value is required except description so checking them, also converting types when necessary
    if (empty($first_name)) {
      $update_form_valid = False;
      $first_name_feedback_class = '';
    }
    if (empty($last_name)) {
      $update_form_valid  = False;
      $last_name_feedback_class = '';
    }
    if (empty($book_title)) {
      $update_form_valid  = False;
      $title_feedback_class = '';
    }
    if (empty($isbn)) {
      $update_form_valid  = False;
      $isbn_feedback_class = '';
    }
    //isbn must be a number
    else if (((int)$isbn) == 0) {
      $update_form_valid  = False;
      $isbn_int_feedback_class = '';
    } else {
      $isbn = (int)$isbn;
    }
    //genre must be in the genres array I defined above
    if (empty($record_genres_edit)) {
      $update_form_valid  = false;
      $genre_feedback_class = '';
    }
    if (empty($pub_year)) {
      $update_form_valid  = False;
      $pub_year_feedback_class = '';
    }
    //publication year must be an int and a valid year (not in the future)
    else if (((int)($pub_year)) == 0 || ((int)$pub_year) > 2021) {
      $update_form_valid  = False;
      $pub_year_int_feedback_class = '';
    } else {
      $pub_year = (int)$pub_year;
    }
    if (empty($rating)) {
      $update_form_valid  = False;
      $rating_feedback_class = '';
    }
    // rating must be a valid float/int on a 1-5 scale
    else if ((float)$rating == 0  || (float)$rating < 1.0 || (float)$rating > 5.0) {
      $update_form_valid  = False;
      $rating_number_feedback_class = '';
    } else {
      $rating = (float)$rating;
    }
    if (empty($availability)) {
      $update_form_valid  = False;
      $available_feedback_class = '';
    } else {
      if ($availability == "Yes") {
        $availability = 1;
      } else {
        $availability = 0;
      }
    }
    if ($update_form_valid) {
      $show_edit_confirmation = True;
      $show_record = False;

      //updating the book info
      exec_sql_query(
        $db,
        "UPDATE books SET author_first_name = :first_name, author_last_name = :last_name, title = :title, publication_year = :pub_year, isbn = :isbn, average_rating = :rating, available = :avail, description = :descrip WHERE (id = :id);",
        array(
          ':first_name' => $first_name, //tainted
          ':last_name' => $last_name, //tainted
          ':title' => $book_title, //tainted
          ':pub_year' => $pub_year, //tainted
          ':isbn' => $isbn, //tainted
          ':rating' => $rating, //tainted
          ':avail' => $availability, //tainted
          ':descrip' => $description, //tainted
          ':id' => $book_id //tainted
        )
      );
      //updating the genres

      //deleting the corresponding genres from the books and genres table
      $genres_deleted = exec_sql_query($db, "DELETE FROM books_and_genres WHERE book_id = :id;", array(':id' => $book_id))->fetchAll();
      //reinserting each genre
      foreach ($genres as $genre) {
        if ($record_genres_edit[strtolower($genre)] == 1) {
          $genre_inserted = exec_sql_query($db, "INSERT INTO books_and_genres (book_id, genre_id) values (:id, (SELECT id from genres where genre = :genre))", array(':id' => $book_id, ':genre' => $genre))->fetchAll();
        }
      }
      $records = exec_sql_query(
        $db,
        "SELECT books.*, genres.genre, book_covers.citation, book_covers.extension FROM books LEFT OUTER JOIN books_and_genres ON books.id = books_and_genres.book_id LEFT OUTER JOIN genres ON books_and_genres.genre_id = genres.id LEFT OUTER JOIN book_covers ON books.id = book_covers.book_id WHERE books.id = :bookid;",
        array(':bookid' => $book_id)
      )->fetchAll();
      $updated_book = $records;
    }
  }

  //deleting a record
  if (isset($_POST['delete'])) {

    //preforming the delete
    //need to remove it from books and genres table first
    $deletion_bag = exec_sql_query(
      $db,
      'delete from books_and_genres where book_id = :book_id',
      array(':book_id' => $book_id) //tainted
    );
    //remove book entry
    $deletion_b = exec_sql_query(
      $db,
      'delete from books where id = :book_id',
      array(':book_id' => $book_id) //tainted
    );
    //remove cover image
    //get extension
    $file_ext = exec_sql_query(
      $db,
      'select extension from book_covers where book_id = :book_id',
      array(':book_id' => $book_id) //tainted
    )->fetchAll();
    $file_ext = $file_ext[0][0];

    $image_source = 'public/uploads/book_covers/' . $book_id . '.' . $file_ext;
    unlink($image_source);
  }
  if ((!is_null($deletion_bag)) && (!is_null($deletion_b))) {
    $delete_confirmation = true;
    $edit_mode = false;
  }
}

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
  <?php if ($edit_mode && $show_record && $is_librarian) { ?>
    <div class="full_record_editing">
      <a class="back_button" href="/catalog">
        &lt; Back</a>
      <br />
      <div class="cover">
        <img class="book_cover_full" src="/public/uploads/book_covers/<?php echo $book_id; ?>.<?php echo htmlspecialchars($record[0]['extension']); ?>" alt=" <?php echo htmlspecialchars($record[0]['title']); ?> book cover" />
        <p>
          <a href="<?php echo htmlspecialchars($record[0]['citation']) ?>">Cover Image Source</a>
        </p>
      </div>
      <br />
      <form id="deleting" action="<?php echo $url; ?>" method="post" novalidate>
        <div class="details_right_delete">
          <input type="submit" name="delete" value="Delete" />
        </div>
      </form>
      <br />
      <br /> <br />
      <form id="editing" action="<?php echo $url; ?>" method="post" novalidate>
        <div class="details_right">
          <p class="feedback <?php echo $pub_year_feedback_class; ?>">Publication Year can't be blank</p>
          <p class="feedback <?php echo $pub_year_int_feedback_class; ?>">Publication Year must be a valid year</p>
          <h2><label for="pub_year">Publication Year:</label>
            <input id="pub_year" type="text" name="pub_year" value="<?php echo htmlspecialchars($sticky_pub_year); ?>" required>
          </h2>
          <h2><label for="description">Description:</label>
            <br /><textarea id="description" name="description" rows="11" cols="51"><?php echo htmlspecialchars($sticky_description); ?></textarea>
          </h2>
        </div>
        <p class="feedback  <?php echo $title_feedback_class; ?>">Book title can't be blank</p>
        <h1><label for="title">Title:</label>
          <input id="title" type="text" name="title" value="<?php echo htmlspecialchars($sticky_title); ?>" required />
        </h1>
        <br />
        <p class="feedback <?php echo $first_name_feedback_class; ?>">Author's first name can't be blank</p>
        <h3><label for="first_name">Author First Name:</label>
          <input id="first_name" type="text" name="first_name" value="<?php echo htmlspecialchars($sticky_first_name); ?>" required>
        </h3>
        <p class="feedback <?php echo $last_name_feedback_class; ?>">Author's last name can't be blank </p>
        <h3><label for="last_name">Author Last Name:</label>
          <input id="last_name" type="text" name="last_name" value="<?php echo htmlspecialchars($sticky_last_name); ?>" required>
        </h3>
        <p class="feedback  <?php echo $isbn_feedback_class; ?>">ISBN can't be blank</p>
        <p class="feedback  <?php echo $isbn_int_feedback_class; ?>">ISBN must be a number! </p>
        <p><label for="isbn">ISBN:</label>
          <input id="isbn" type="text" name="isbn" value="<?php echo htmlspecialchars($sticky_isbn); ?>" required>
        </p>
        <p class="feedback  <?php echo $rating_feedback_class; ?>">Average rating can't be blank </p>
        <p class="feedback  <?php echo $rating_number_feedback_class; ?>">Rating must be a valid rating on a 1 - 5 scale (e.g. 2.4, 4.9, ...) </p>
        <p><label for="avg_rating">Average Rating:</label> <input id="avg_rating" name="avg_rating" type="text" value="<?php echo htmlspecialchars($sticky_rating); ?>" required></p>
        <p class="feedback  <?php echo $genre_feedback_class; ?>">Genre can't be blank</p>
        <label>Genre:</label>
        <?php
        foreach ($genres as $genre) {
          $genre_param = strtolower($genre);
        ?>
          <label><input class="genre-checkboxes" type="checkbox" name="genre[<?php echo htmlspecialchars($genre_param); ?>]" <?php echo htmlspecialchars($sticky_genres[$genre]); ?> value="1" /><?php echo htmlspecialchars($genre) ?></label>
        <?php
        }
        ?>
        <p class="feedback  <?php echo $available_feedback_class; ?>">Availability can't be blank</p>
        <p>Available?</p>
        <input class="available-radio" id="available" type="radio" name="availability" value="Yes" <?php echo $sticky_available; ?>>
        <label for="available">Yes</label>
        <input class="available-radio" id="not_available" type="radio" name="availability" value="No" <?php echo $sticky_not_available; ?>>
        <label for="not_available">No</label>
        <br />
        <input class="update-button" type="submit" name="update" value="Update" />

      </form>

    </div>
    <!-- confirmation page that shows if the book was updated successfully
    and if the user is a librarian -->
  <?php } else if ($is_librarian && $show_edit_confirmation) { ?>

    <div class="full_record">
      <a class="back_button" href="/catalog">
        &lt; Back</a>
      <br />
      <h2>Edit Successful</h2>
      <h3>Here is the new entry:</h3>
      <div class="cover">
        <img class="book_cover_full" src="/public/uploads/book_covers/<?php echo $book_id; ?>.<?php echo htmlspecialchars($updated_book[0]['extension']); ?>" alt=" <?php echo htmlspecialchars($updated_book[0]['title']); ?> book cover" />
        <p>
          <!-- source: citation link in the covers table. All the seed data book covers are  from goodreads.com. see the links in the database for each individual URL -->
          <a href="<?php echo htmlspecialchars($updated_book[0]['citation']) ?>">Cover Image Source</a>
        </p>
      </div>
      <div class="details">
        <div class="details_right">
          <h2><?php echo htmlspecialchars($updated_book[0]['publication_year']); ?></h2>
        </div>
        <h1><?php echo htmlspecialchars($updated_book[0]['title']); ?></h1>
        <br />
        <h2>By: <?php echo htmlspecialchars($updated_book[0]['author_first_name']) ?> <?php echo htmlspecialchars($updated_book[0]['author_last_name']); ?> </h2>
        <p>ISBN: <?php echo htmlspecialchars($updated_book[0]['isbn']); ?></p>
        <?php foreach ($updated_book as $r) { ?>
          <div class="tags <?php echo htmlspecialchars(strtolower($r['genre'])); ?>"><?php echo htmlspecialchars($r['genre']); ?></div>
        <?php } ?>
        <p>Average Rating: <?php echo htmlspecialchars($updated_book[0]['average_rating']); ?></p>
        <!-- Only want description to show if its not empty -->
        <?php if (!empty($updated_book[0]['description'])) { ?>
          <p><strong>Description:</strong>
            </br>
            <?php echo htmlspecialchars($updated_book[0]['description']); ?></p>
        <?php } ?>
      </div>
    </div>

  <?php } else if ($is_librarian && $delete_confirmation) {
  ?>
    <br />
    <br />
    <a class="back_button" href="/catalog">
      &lt; Back</a>
    <br />
    <div class="deleted">

      <h2>Delete Successful</h2>
      <p>You deleted <?php echo htmlspecialchars($record[0]['title']); ?> by <?php echo htmlspecialchars($record[0]['author_first_name']) . ' ' . htmlspecialchars($record[0]['author_last_name']); ?></p>
    </div>
  <?php } else if ($show_record) { ?>
    <div class=" full_record">
      <a class="back_button" href="/catalog">
        &lt; Back</a>
      <br />
      <div class="cover">
        <img class="book_cover_full" src="/public/uploads/book_covers/<?php echo $book_id; ?>.<?php echo htmlspecialchars($record[0]['extension']); ?>" alt=" <?php echo htmlspecialchars($record[0]['title']); ?> book cover" />
        <p>
          <!-- source: citation link in the covers table. All the seed data book covers are  from goodreads.com. see the links in the database for each individual URL -->
          <a href="<?php echo htmlspecialchars($record[0]['citation']) ?>">Cover Image Source</a>
        </p>
      </div>
      <div class="details">
        <div class="details_right">
          <h2><?php echo htmlspecialchars($record[0]['publication_year']); ?></h2>
        </div>
        <h1><?php echo htmlspecialchars($record[0]['title']); ?></h1>
        <br />
        <h2>By: <?php echo htmlspecialchars($record[0]['author_first_name']) ?> <?php echo htmlspecialchars($record[0]['author_last_name']); ?> </h2>
        <p>ISBN: <?php echo htmlspecialchars($record[0]['isbn']); ?></p>
        <?php foreach ($record as $r) { ?>
          <div class="tags <?php echo htmlspecialchars(strtolower($r['genre'])); ?>"><?php echo htmlspecialchars($r['genre']); ?></div>
        <?php } ?>
        <p>Average Rating: <?php echo htmlspecialchars($record[0]['average_rating']); ?></p>
        <!-- Only want description to show if its not empty -->
        <?php if (!empty($record[0]['description'])) { ?>
          <p><strong>Description:</strong>
            <br />
            <?php echo htmlspecialchars($record[0]['description']); ?>
          </p>
        <?php } ?>
      </div>
    </div>
  <?php } ?>


</body>
