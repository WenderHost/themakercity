<?php

?>
<div class="container-lg">
  <div class="row justify-content-center pt-5 pb-5">
    <div class="col-lg-10">
      <a href="<?= home_url() ?>" alt="Return home" class="text-center" style="display: block; margin: 0 auto 40px auto;"><img src="<?= MAKR_STYLESHEET_DIR_URI ?>lib/img/maker-icon_512x512.png" style="width: 100px;" /></a>
      <h1 class="text-center">Apply for a Maker Account</h1>
        <style>
          .acf-form-submit {
            margin-top: 3rem;
          }
          .acf-form-submit button{
            width: 100%;
          }
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
<?php
acf_form([
  'id'        => 'acf-form',
  'post_id'   => 'new_post',
  'post_title'  => true,
  'new_post'  => array(
    'post_type'   => 'maker',
    'post_status' => 'pending',
  ),
  'fields'    => [ 'name', 'email', 'collaborator', 'maker_category', 'primary_image', 'additional_images', 'description', 'social_profiles', 'avatar'  ],
  'instruction_placement' => 'field',
  'updated_message'       => __('We have received your Maker Profile submission. Thank you!', 'acf'),
  'html_updated_message'  => '<div class="alert alert-success" role="alert"><div class="alert-message">%s</div></div>',
  'html_submit_button'    => '<button type="submit" class="btn btn-primary btn-lg" style="display: block; font-size: 1.65rem;">%s</button>',
  'submit_value'          => __( 'Apply', 'acf' ),
]);
?>
    </div><!-- .col -->
  </div><!-- .row -->
</div><!-- .container -->