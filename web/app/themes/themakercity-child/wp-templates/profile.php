<h1>Your Profile</h1>
<?php
$current_user = wp_get_current_user();
if( $current_user ){
  $settings = [
    'updated_message'       => __('Your profile has been updated.', 'acf'),
    'html_updated_message'  => '<div class="alert alert-success" role="alert"><div class="alert-message">%s</div></div>',
    'html_submit_button'    => '<button type="submit" class="btn btn-primary">%s</button>',
    'instruction_placement' => 'field',
    'fields'  => [ 'name', 'email', 'collaborator', 'maker_category', 'primary_image', 'additional_images', 'description', 'social_profiles', 'avatar'  ],
  ];

  $maker_profile_id = get_user_meta( $current_user->ID, 'maker_profile_id', true );
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