<?php
// defining navigation class variables
$nav_home_class = '';
$nav_catalog_class = '';
$nav_form_class = '';

$page_url = '';


//include provided dp.php file
include_once("includes/db.php");

$db = init_sqlite_db("db/site.sqlite", 'db/init.sql');

include_once("includes/sessions.php");
$session_messages = array();
process_session_params($db, $session_messages);


// define librarian
define('ADMIN_GROUP_ID', 1); // see init.sql, I defined the librarian group to have id of 1
$is_librarian = is_user_member_of($db, ADMIN_GROUP_ID);

//getting list of genres
$genres = [];
$genres_result = exec_sql_query(
  $db,
  "select genre from genres;"
)->fetchAll();
foreach ($genres_result as $val) {
  $genres[] = ($val[0]);
}
