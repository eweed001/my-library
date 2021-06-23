<?php include("includes/init.php");
$title = "Form";
$nav_form_class = "active";
$page_url = '/form';

//include the file written for us
include_once("includes/db.php");

define("MAX_FILE_SIZE", 1000000); // 1 MB


if ($is_librarian) {

  //form conditional values
  $show_form = true;
  $show_confirmation = false;
  $book_inserted = False;
  $failed_insert = False;


  //feedback message classes
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
  $file_big_feedback_class = 'hidden';
  $citation_feedback_class = 'hidden';
  $file_feedback_class = 'hidden';
  $file_wrong_type_feedback_class = 'hidden';


  //sticky values
  $first_name_sticky = '';
  $last_name_sticky = '';
  $title_sticky = '';
  $isbn_sticky = '';
  $genre_sticky = array(); //checkboxes
  $pub_year_sticky = '';
  $rating_sticky = '';
  $yes_available_sticky = '';
  $not_available_sticky = '';
  $description_sticky = '';

  //form values
  $first_name = null;
  $last_name = null;
  $book_title =  null;
  $isbn = null;
  $genre = null;
  $pub_year = null;
  $rating = null;
  $availability = null;
  $description = null;
  $citation = null;
  $file_ext = null;

  //check if user submitted a book
  if (isset($_POST['submit'])) {
    //var_dump($_POST['genre']);

    //trimming leading or trailing spaces
    $first_name = trim($_POST['first_name']); //untrusted
    $last_name = trim($_POST['last_name']); //untrusted
    $book_title = trim($_POST['title']); //untrusted
    $isbn = trim($_POST['isbn']); //untrusted
    $genre = ($_POST['genre']); //untrusted
    $pub_year = trim($_POST['publication_year']); //untrusted
    $rating = trim($_POST['rating']); //untrusted
    $description = trim($_POST['description']);

    $availability = trim($_POST['availability']); //untrusted
    $citation = trim($_POST['citation']); //untrusted

    //get info about file
    $file = $_FILES['cover-image'];

    //setting form to be valid initially
    $form_valid = true;

    //Every value is required except description so checking them, also converting types when necessary
    if (empty($first_name)) {
      $form_valid = False;
      $first_name_feedback_class = '';
    }
    if (empty($last_name)) {
      $form_valid = False;
      $last_name_feedback_class = '';
    }
    if (empty($book_title)) {
      $form_valid = False;
      $title_feedback_class = '';
    }
    if (empty($isbn)) {
      $form_valid = False;
      $isbn_feedback_class = '';
    }
    //isbn must be a number
    else if (((int)$isbn) == 0) {
      $form_valid = False;
      $isbn_int_feedback_class = '';
    } else {
      $isbn = (int)$isbn;
    }
    //genre must be in the genres array I defined above
    if (empty($genre)) {
      $form_valid = false;
      $genre_feedback_class = '';
    }
    if (empty($pub_year)) {
      $form_valid = False;
      $pub_year_feedback_class = '';
    }
    //publication year must be an int and a valid year (not in the future)
    else if (((int)($pub_year)) == 0 || ((int)$pub_year) > 2021) {
      $form_valid = False;
      $pub_year_int_feedback_class = '';
    } else {
      $pub_year = (int)$pub_year;
    }
    if (empty($rating)) {
      $form_valid = False;
      $rating_feedback_class = '';
    }
    // rating must be a valid float/int on a 1-5 scale
    else if ((float)$rating == 0  || (float)$rating < 1.0 || (float)$rating > 5.0) {
      $form_valid = False;
      $rating_number_feedback_class = '';
    } else {
      $rating = (float)$rating;
    }
    if (empty($availability)) {
      $form_valid = False;
      $available_feedback_class = '';
    } else {
      if ($availability == "Yes") {
        $availability = 1;
      } else {
        $availability = 0;
      }
    }
    if (empty($citation)) {
      $form_valid = False;
      $citation_feedback_class = '';
    }
    //checking uploaded file



    if ($file['error'] == UPLOAD_ERR_OK) {
      $filename = basename($file['name']);

      $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));


      //checking if file is an acceptable type
      if (!in_array($file_ext, array('jpg', 'png'))) {
        $form_valid = false;
        $file_wrong_type_feedback_class = '';
      }
    } else {

      $form_valid = false;
      $file_feedback_class = '';
    }
  }



  if ($form_valid) {
    // now that we are dealing with files, need to start a transaction
    $db->beginTransaction();

    // set form to hidden and confirmation to show

    $show_form = False;
    $show_confirmation = True;

    //insert book into the catalog

    $result = exec_sql_query(
      $db,
      "insert into books (author_first_name, author_last_name, title, publication_year, isbn, average_rating, available, description) values (:first_name, :last_name, :title, :pub_year, :isbn, :rating, :available, :description);",
      array(
        ':first_name' => $first_name, //tainted
        ':last_name' => $last_name, //tainted
        ':title' => $book_title, //tainted
        ':pub_year' => $pub_year, //tainted
        ':isbn' => $isbn, //tainted
        ':rating' => $rating, //tainted
        ':available' => $availability, //tainted
        ':description' => $description //tainted
      )
    );

    if ($result) {
      //getting id of book just inserted
      $book_id = $db->lastInsertId('id');
      //concatenating the id with the path and appropriate file extension
      $id_filename = 'public/uploads/book_covers/' . $book_id . '.' . $file_ext;
      //move the file to the correct folder
      $flag = move_uploaded_file($file['tmp_name'], $id_filename);

      //insert the citation, extension into book covers table
      $cover_result = exec_sql_query(
        $db,
        'insert into book_covers (book_id, citation, extension) values (:book_id, :citation, :ext);',
        array(
          ':book_id' => $book_id,
          ':citation' => $citation,
          ':ext' => $file_ext
        )
      );
    }

    foreach ($genres as $g) {

      if ($genre[strtolower($g)] == 1) {

        $genre_id = exec_sql_query(
          $db,
          "select id from genres where genre = :genre",
          array(':genre' => $g)
        )->fetchAll();
        $genre_id_value = $genre_id[0]['id'];
        $genre_inserted = exec_sql_query(
          $db,
          "INSERT INTO books_and_genres (book_id, genre_id) values (:id, :genre_id)",
          array(
            ':id' => $book_id,
            ':genre_id' => $genre_id_value
          )
        )->fetchAll();
      }
    }
    //need to check if it was inserted successfully
    if (!is_null($result) && !is_null($genre_inserted)) {
      $book_inserted = true;
    } else {
      $failed_insert = true;
    }
    $db->commit();
  } else {

    // form was not valid, set all the sticky values
    $first_name_sticky = $first_name; //tainted
    $last_name_sticky = $last_name; //tainted
    $title_sticky = $book_title; //tainted
    $isbn_sticky = $isbn; //tainted

    $pub_year_sticky = $pub_year; //tainted
    $rating_sticky = $rating; //tainted
    $yes_available_sticky = ($availability == '1' ? 'checked' : '');
    $not_available_sticky = ($availability == '0' ? 'checked' : '');
    $citation_sticky = $citation; //tainted
    foreach ($genres as $val) {
      $genre_sticky[$val] = ($genre[strtolower($val)] == '1' ? 'checked' : '');
    }
    $description_sticky = $description; //tainted
  }


  //adding genre form
  $genre_form_valid = false;
  $genre_show_form  = true;
  $genre_show_confirmation = false;

  $adding_genre_feedback_class = 'hidden';
  $adding_genre_dup_feedback_class = 'hidden';

  $new_genre_sticky = '';

  $new_genre = null;

  if (isset($_POST['add'])) {

    //trimming spaces
    $new_genre = trim($_POST['new_genre']); //untrusted
    $genre_form_valid = true;

    //checking if it's empty
    if (empty($new_genre)) {
      $genre_form_valid = false;
      $adding_genre_feedback_class = '';
    }
    //checking if it's already in the array
    if (in_array(strtolower($new_genre), array_map('strtolower', $genres))) {
      $genre_form_valid = false;
      $adding_genre_dup_feedback_class = '';
    }
  }
  if ($genre_form_valid) {

    //set confirmation to show
    $genre_show_form = false;
    $genre_show_confirmation = true;

    $result = exec_sql_query(
      $db,
      "insert into genres (genre) values (:genre);",
      array(
        ':genre' => $new_genre //tainted
      )
    )->fetchAll();
    if (!is_null($result)) {
      $new_genre_inserted = true;
    }
  } else {
    $new_genre_sticky = $new_genre;
  }
}


?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" type="text/css" href="/public/styles/site.css" />

  <title>Form</title>
</head>

<body>
  <?php include("includes/header.php"); ?>
  <div class="banner"></div>
  <?php if (is_user_logged_in() && $is_librarian) { ?>
    <div class="form_position">
      <div class="tiled">

        <?php if ($show_form) { ?>
          <div class="tile_book">
            <h2>Enter Book Information Below:</h2>
            <form id="inserting" action="form" method="post" enctype="multipart/form-data" novalidate>
              <fieldset>

                <div class="form_left_content">
                  <p class="feedback <?php echo $first_name_feedback_class; ?>">Please provide the Author's first name</p>
                  <p>
                    <label for="Author_First_Name"><span>Author First Name:</span></label>
                    <input id="Author_First_Name" type="text" name="first_name" size="15" maxlength="30" value="<?php echo htmlspecialchars($first_name_sticky); ?>" required>
                  </p>
                  <p class="feedback <?php echo $last_name_feedback_class; ?>">Please provide the Author's last name </p>
                  <p>
                    <label for="Author_Last_Name"><span>Author Last Name:</span></label>
                    <input id="Author_Last_Name" type="text" name="last_name" size="15" maxlength="30" value="<?php echo htmlspecialchars($last_name_sticky); ?>" required>
                  </p>
                  <p class="feedback  <?php echo $title_feedback_class; ?>">Please provide the book's title </p>
                  <p><label for="title"><span>Title:</span></label>
                    <input id="title" type="text" name="title" size="15" maxlength="30" value="<?php echo htmlspecialchars($title_sticky); ?>" required>
                  </p>

                  <p class="feedback  <?php echo $isbn_feedback_class; ?>">Please provide the book's ISBN </p>
                  <p class="feedback  <?php echo $isbn_int_feedback_class; ?>">ISBN must be a number! </p>
                  <p>
                    <label for="isbn"><span>ISBN:</span></label>
                    <input id="isbn" type="text" name="isbn" size="15" maxlength="40" value="<?php echo htmlspecialchars($isbn_sticky); ?>" required>
                  </p>
                  <p class="feedback  <?php echo $pub_year_feedback_class; ?>">Please provide the publication year </p>
                  <p class="feedback  <?php echo $pub_year_int_feedback_class; ?>">Publication Year must be a valid year (e.g. 2020, 1996,...) </p>
                  <p>
                    <label for="pub_year"><span>Publication Year:</span></label>
                    <input id="pub_year" type="text" name="publication_year" size="8" value="<?php echo htmlspecialchars($pub_year_sticky); ?>" required>
                  </p>
                  <p class="feedback  <?php echo $rating_feedback_class; ?>">Please provide the average rating </p>
                  <p class="feedback  <?php echo $rating_number_feedback_class; ?>">Rating must be a valid rating on a 1 - 5 scale (e.g. 2.4, 4.9, ...) </p>
                  <p>
                    <label for="avg_rating"><span>Average Rating:</span></label>
                    <input class="input-styling" id="avg_rating" type="text" name="rating" size="8" value="<?php echo htmlspecialchars($rating_sticky); ?>" required>
                  </p>
                  <p class="feedback  <?php echo $available_feedback_class; ?>">Please select the availability </p>
                  <p>
                    Available?
                  </p>

                  <p>
                    <input id="available" type="radio" name="availability" value="Yes" <?php echo $yes_available_sticky; ?>>
                    <label for="available">Yes</label>
                    <input id="not_available" type="radio" name="availability" value="No" <?php echo $not_available_sticky; ?>>
                    <label for="not_available">No</label>
                    <br />

                    <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo MAX_FILE_SIZE; ?>" />
                  <p class="feedback <?php echo $file_big_feedback_class; ?>">File is too large</p>
                  <p class="feedback <?php echo $file_feedback_class; ?>">Please select a cover photo</p>
                  <p class="feedback <?php echo $file_wrong_type_feedback_class; ?>">File must be a JPG or PNG</p>

                  <label for="upload-file">Cover Image:</label>
                  <input id="upload-file" type="file" name="cover-image" accept=".jpg,.png" required />
                  <br />
                  <p class="feedback <?php echo $citation_feedback_class; ?>">Please provide the source</p>
                  <label for="citation"><span>Image Source URL:</span></label>
                  <input id="citation" type="text" name="citation" size="15" value="<?php echo htmlspecialchars($citation_sticky); ?>" required>
                  <br />
                  <br />
                  <input type="submit" name="submit" value="Submit" />

                </div>
                <p class="feedback  <?php echo $genre_feedback_class; ?>">Please select at least one genre</p>
                <p>
                  <label>Genre:</label>
                <ul class="checkboxes">
                  <?php
                  foreach ($genres as $genre) {
                    $genre_param = strtolower($genre);
                  ?>

                    <li>
                      <label>
                        <input type="checkbox" name="genre[<?php echo htmlspecialchars($genre_param); ?>]" <?php echo htmlspecialchars($genre_sticky[$genre]); ?> value="1" /><?php echo htmlspecialchars($genre) ?>
                      </label>
                    </li>

                  <?php
                  }
                  ?>
                </ul>

                <p>
                  <br />
                  <br />
                  <br />
                  <br />
                  <br />
                </p>
                <p> <label for="description"><br />Description:</label>
                  <br />
                  <textarea id="description" name="description" rows="9" cols="40" placeholder="optional..."><?php echo htmlspecialchars($description_sticky); ?></textarea>
                </p>

              </fieldset>

            </form>
          </div>
          <div class="tile_genre">
            <?php if ($genre_show_form) { ?>
              <h2>Add a Genre:</h2>
              <form id="inserting-genre" action="form" method="post" novalidate>
                <p class="feedback <?php echo $adding_genre_feedback_class; ?>">Please Enter a Genre</p>
                <?php if (empty($new_genre)) { ?>
                  <p class="feedback <?php echo $adding_genre_dup_feedback_class; ?>">Genre already exists</p>
                <?php } ?>
                <p>
                  <label for="new_genre">Genre:</label>
                  <input id="new_genre" type="text" name="new_genre" size="15" maxlength="30" required>
                  <input type="submit" name="add" value="Submit" />
                </p>
              </form>
          </div>
        <?php }
            if ($genre_show_confirmation && $new_genre_inserted) { ?>
          <h2>Confirmation</h2>
          <p>Refresh the page to see it on the left</p>
        <?php } ?>

      <?php } ?>
      </div>

      <?php if ($show_confirmation && $book_inserted) { ?>
        <!-- confirmation page -->
        <div class="confirmation">
          <h2>Thank You!</h2>
          <div class="cover">
            <img class="book_cover_add" src="/public/uploads/book_covers/<?php echo htmlspecialchars($book_id); ?>.<?php echo htmlspecialchars($file_ext); ?>" alt=" <?php echo htmlspecialchars($book_title); ?> book cover" />
            <p><a href="<?php echo htmlspecialchars($citation); ?>">Cover Image Source</a></p>
          </div>



          <p>You entered in <strong><?php echo htmlspecialchars($book_title); ?></strong>
            by <strong><?php echo htmlspecialchars($first_name); ?></strong> <strong>
              <?php echo htmlspecialchars($last_name); ?>
            </strong> </p>
          <p>ISBN: <strong><?php echo htmlspecialchars($isbn); ?></strong></p>
          <p>Genre: <strong><?php
                            $comma_counter = count($genre);
                            $i = 0;
                            foreach ($genres as $g) {
                              if ($genre[strtolower($g)] == 1) {
                                $i = $i + 1;
                                echo htmlspecialchars($g);
                                if ($comma_counter > $i) {
                                  echo ', ';
                                }
                              }
                            }
                            ?></strong></p>
          <p>Publication Year: <strong><?php echo htmlspecialchars($pub_year); ?></strong></p>
          <p>Average Rating: <strong> <?php echo htmlspecialchars($rating); ?></strong></p>
          <p>Availability: <strong><?php
                                    if (htmlspecialchars($availability) == 1) {
                                      echo ("Yes");
                                    } else {
                                      echo ("No");
                                    }; ?></strong></p>

          <form action="form">
            <input id="submit_another" type="submit" value="Enter Another Book" />
          </form>
        </div>

      <?php } ?>
      <?php if ($failed_insert) { ?>
        <p>Something went wrong entering the book... Please try again</p>
      <?php
      } ?>

    </div>
  <?php } else {
    echo "<h3>You don't have permission to access this page </h3>";
  } ?>

</body>

</html>
