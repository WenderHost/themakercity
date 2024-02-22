<h1>Your Profile</h1>
<?php
$current_user = wp_get_current_user();
//uber_log('🪵 $current_user->ID = ' . $current_user->ID );
if( $current_user ){
  $maker_profile_id = get_user_meta( $current_user->ID, 'maker_profile_id', true );
  $settings = [
    'post_id' => $maker_profile_id,
    'updated_message' => __('Your profile has been updated.', 'acf'),
    'html_updated_message'  => '<div class="alert alert-success" role="alert"><div class="alert-message">%s</div></div>',
    'html_submit_button'  => '<button type="submit" class="btn btn-primary">%s</button>',
    'instruction_placement' => 'field',
    'fields'  => [ 'name', 'email', 'collaborator', 'primary_image', 'additional_images', 'description', 'social_profiles', 'avatar'  ],
  ];
  acf_form( $settings );
}
?>