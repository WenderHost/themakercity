<h1>Your Account</h1>
<?php
$current_user = wp_get_current_user();
$settings = [
  'post_id' => 'user_' . $current_user->ID,
];
acf_form( $settings );
?>