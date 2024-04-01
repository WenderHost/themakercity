<h1>Your Profile</h1>
<?php
$current_user = wp_get_current_user();
if( $current_user ){
  $maker_profile_id = get_user_meta( $current_user->ID, 'maker_profile_id', true );

  $settings = [
    'post_title'            => true,
    'updated_message'       => __('Your profile has been updated. <a href="' . get_permalink( $maker_profile_id ) . '" target="_blank">View</a> your profile.', 'acf'),
    'html_updated_message'  => '<div class="alert alert-success" role="alert"><div class="alert-message">%s</div></div>',
    'html_submit_button'    => '<button type="submit" class="btn btn-primary">%s</button>',
    'instruction_placement' => 'field',
    'fields'  => [ 'name', 'email', 'collaborator', 'maker_category', 'primary_image', 'additional_images', 'description', 'social_profiles', 'avatar'  ],
  ];

  if( empty( $maker_profile_id ) ){
    $settings['post_id'] = 'new_post';
    $settings['new_post'] = [
      'post_type'   => 'maker',
      'post_status' => 'publish',
      'post_author' => $current_user->ID,
    ];
  } else {
    $settings['post_id'] = $maker_profile_id;
  }

  acf_form( $settings );
}
?>
<style>
  /* This CSS file is to hide the original Title label */
  .acf-field .acf-label label[for="acf-_post_title"] {
    display: none; /* Hide the original label */
  }
</style>
<script type="text/javascript">
jQuery(document).ready(function($) {
  // Change the Title field placeholder to "Business Name"
  $('input[name="acf[_post_title]"]').attr('placeholder', 'Business Name');

  // Optionally, add a new label element if you want it to be visible and not just a placeholder
  $('input[name="acf[_post_title]"]').before('<div class="acf-label"><label for="acf[_post_title]">Business Name <span class="acf-required">*</span></label></div>');
});
</script>