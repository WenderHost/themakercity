<?php
use function TheMakerCity\utilities\get_alert;
use function TheMakerCity\users\check_maker_profile_id;

$current_user = wp_get_current_user();
$maker_profile_id = get_user_meta( $current_user->ID, 'maker_profile_id', true );
if( empty( $maker_profile_id ) )
  $maker_profile_id = check_maker_profile_id();
?>

  <div class="container-fluid">
    <div class="row justify-content-start">
      <div class="col-12">
        <div class="card" style="max-width: 1100px;">



          <form id="acf-form" class="acf-form" method="post">
          <div class="card-header bg-theme-dark text-white">
            <div class="row align-items-center">
              <div class="col"><span class="fs-1 fw-bold">Your Profile</span></div>
              <div class="col" style="text-align: right;">
                <?php
                $profile_permalink = get_permalink( $maker_profile_id );
                ?>
                <a href="<?= $profile_permalink ?>" target="_blank" class="btn btn-light btn-lg"><i class="align-middle fas fa-arrow-up-right-from-square"></i> View</a>

                <button type="submit" class="btn btn-primary btn-lg">Update</button>
              </div>
            </div>

          </div>
          <div class="card-body">
        <?php
        if( $current_user ){

          $settings = [
            'post_title'            => true,
            'updated_message'       => __('Your profile has been updated. <a href="' . get_permalink( $maker_profile_id ) . '" target="_blank">View</a> your profile.', 'acf'),
            'html_updated_message'  => get_alert([ 'description' => '%s', 'type' => 'success' ]),
            'html_submit_button'    => '<div class="d-grid mt-3"><button type="submit" class="btn btn-primary fw-bold fs-3">%s</button></div>',
            'instruction_placement' => 'field',
            'fields'  => [ 'name', 'email', 'collaborator', 'maker_category', 'logo', 'primary_image', 'avatar', 'additional_images', 'description', 'social_profiles', 'show_location', 'business_address' ],
            'form'                  => false,
          ];

          if( empty( $maker_profile_id ) || ! $maker_profile_id ){
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
          <div class="acf-form-submit">
            <div class="d-grid mt-3">
              <button type="submit" class="btn btn-primary fw-bold fs-3">Update</button>
            </div>       
            <span class="acf-spinner"></span>
          </div>

          </div><!-- .card-body -->
          </form>


        </div><!-- .card -->
      </div><!-- .col-12 -->
    </div><!-- .row.justify-content-start -->
  </div>

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