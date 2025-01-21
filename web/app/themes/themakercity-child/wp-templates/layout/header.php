<?php
$title = ( isset( $args ) && array_key_exists( 'title', $args ) )? $args['title'] : false ;
?>
  <div class="wrapper">
    <?= get_template_part( 'wp-templates/layout/sidebar' ); ?>

    <div class="main">
      <?php //echo get_template_part( 'wp-templates/layout/navbar-top' ); ?>
      <main class="content maker-background">
        <?php if( $title ): ?>
        <div class="mb-3">
          <h1 class="h3 d-inline align-middle"><?= $title ?></h1>
        </div>
        <?php endif; ?>