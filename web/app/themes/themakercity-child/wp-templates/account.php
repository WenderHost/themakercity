<?php
use function TheMakerCity\utilities\get_alert;
?>
<div class="card" style="max-width: 1200px;">
  <div class="card-header bg-theme-dark text-white"><span class="fs-1 fw-bold">Your Account</span></div>
  <div class="card-body">
<?php
$current_user = wp_get_current_user();
$settings = [
  'post_id'               => 'user_' . $current_user->ID,
  'field_groups'          => [ 'group_65c53ab8349d6' ],
  'html_submit_button'    => '<div class="d-grid mt-3"><button type="submit" class="btn btn-primary fw-bold fs-3">%s</button></div>',
  'html_updated_message'  => get_alert([ 'description' => 'Your user account has been updated.', 'type' => 'success' ]),
];
acf_form( $settings );
?>
  </div>
</div>