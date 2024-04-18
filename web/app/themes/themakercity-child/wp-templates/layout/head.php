<?php
$title = ( isset( $args ) && array_key_exists( 'title', $args ) )? $args['title'] : get_bloginfo( 'title' );
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="preconnect" href="https://fonts.gstatic.com">
  <link rel="shortcut icon" href="<?= MAKR_STYLESHEET_DIR_URI ?>lib/img/maker-icon_512x512.png" />
  <link rel="canonical" href="<?= home_url() ?>" />
  <title><?= $title ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
  <?php acf_form_head() ?>
  <?php wp_head() ?>
</head>
<body data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-layout="default">