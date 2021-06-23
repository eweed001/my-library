<header>
  <nav>
    <ul>
      <li class="<?php echo $nav_home_class; ?>"><a href='/'>HOME</a></li>
      <li class="<?php echo $nav_catalog_class; ?>"><a href='/catalog'>CATALOG</a></li>
      <?php if (is_user_logged_in() && $is_librarian) { ?>
        <li class="<?php echo $nav_form_class; ?>"><a href='/form'>ADD A BOOK</a></li>
      <?php } ?>
      <?php if (is_user_logged_in()) { ?>
        <li class="log_out"><a href='<?php echo logout_url() ?>'>LOG OUT</a></li>
      <?php } ?>
    </ul>
  </nav>

  <?php
  if (!is_user_logged_in()) {
    echo_login_form($page_url, $session_messages);
  ?>
  <?php } ?>

</header>
