<?php

?>
<div class="container-lg">
  <div class="row justify-content-center pt-5 pb-5">
    <div class="col-md-8">
      <a href="<?= home_url() ?>" alt="Return home" class="text-center" style="display: block; margin: 0 auto 40px auto;"><img src="<?= MAKR_STYLESHEET_DIR_URI ?>lib/img/maker-icon_512x512.png" style="width: 100px;" /></a>
      <h1 class="text-center">Apply for a Maker Account</h1>
        <style>
          .alert{
              display:none;
          }
          .alert li{
            margin-bottom: .5rem;
          }
          .alert.alert-success,.alert.alert-warning,.alert-danger{
              display:flex;
          }
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
          .flash-effect{
            animation: flashAnimation 1s;
          }
          @keyframes flashAnimation {
            0%, 100% { background-color: transparent; }
            50% { background-color: yellow; }
          }
          .form-label{
            font-weight: bold;
          }
          .required{
            color: #FF0000;
          }
          .choices{
            margin-bottom: .25em;
          }
          .choices__list--multiple .choices__item{
            background-color: #0482c4;
            border-color: #999;
          }
          /**/
        </style>

        <div class="alert" id="createMaker-message" role="alert">
          <div class="alert-message">Alert goes here...</div>
        </div>

        <form id="create-maker-form" hx-post="/wp-htmx/v1/noswap/maker/create" hx-swap="none" hx-encoding="multipart/form-data">
          <input type="hidden" name="action" value="htmx_maker_create">
          <div class="mb-3">
            <label for="business_name" class="form-label">Business Name <span class="required">*</span></label>
            <input type="text" class="form-control" id="business_name" name="maker_business_name" value="" requiredAAA>
          </div>
          <div class="row">
            <div class="col-md-6">
              <label for="name" class="form-label">Your Name <span class="required">*</span></label>
              <input type="text" class="form-control" id="name" name="maker_name" value="" requiredAAA>
            </div>
            <div class="col-md-6">
              <label for="email" class="form-label">Your Email <span class="required">*</span></label>
              <input type="text" class="form-control" id="email" name="maker_email" value="" requiredAAA>
              <div class="form-text">We'll use this to create your user account.</div>
            </div>
          </div>
          <div class="form-check-inline-row mb-3">
            <div><label class="form-label">Are you open to collaborating? <span class="required">*</span></label></div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="maker_collaborator" id="collaborator_yes" value="yes">
              <label class="form-check-label" for="collaborator_yes">Yes</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="maker_collaborator" id="collaborator_no" value="no">
              <label class="form-check-label" for="collaborator_no">No</label>
            </div>
          </div><!-- .form-check-inline-row -->
          <div class="row mb-3">
            <div><label class="form-label">Maker Category(s) <span class="required">*</span></label></div>
            <?php
            $terms = get_terms(array(
              'taxonomy' => 'maker-category',
              'hide_empty' => false, // Change to true if you want to hide empty terms.
            ));
            // Check if any terms are found and no error occurred.
            if (!is_wp_error($terms) && !empty($terms)) {
              $terms_array = array();

              // Loop through each term and add it to the $terms_array.
              foreach ($terms as $term) {
                //$term_options[] = '<option value="' . $term->term_id . '">' . $term->name . '</option>';
                $terms_array[] = [ 'value' => $term->term_id, 'label' => $term->name, 'id' => $term->term_id ];
              }
              $terms_json = json_encode( $terms_array );
            }
            ?>

            <select multiple="multiple" name="maker_categories[]" id="maker_categories" class="form-control choices-multiple"></select>
            <div class="form-text">Choose up to 3 Maker Categories.</div>
            <script>
              new Choices(document.querySelector(".choices-multiple"),{maxItemCount: 3,choices: <?= $terms_json ?>});
            </script>
          </div><!-- .row -->
          <div class="mb-3">
            <label for="description" class="form-label">Description <span class="required">*</span></label>
            <textarea class="form-control" id="description" name="maker_description" rows="5"></textarea>
          </div>
          <div class="mb-3">
            <label for="logo" class="form-label">Logo <span class="required">*</span></label>
            <input type="file" class="form-control filepond" name="maker_logo" accept="image/png, image/jpeg, image/gif" />
          </div>
          <div>
            <input type="submit" class="btn btn-lg btn-primary" value="Submit" />
          </div>
        </form>
    </div><!-- .col -->
  </div><!-- .row -->
</div><!-- .container -->

<script>
  const createMakerMsgContainer = document.getElementById('createMaker-message');
  const createMakerMsgText = document.querySelector('#createMaker-message .alert-message');
  const createMakerForm = document.getElementById('create-maker-form');
  document.body.addEventListener('createMaker', function(evt){
    // Reset the alert status of the message container
    createMakerMsgContainer.classList.remove('alert-success','alert-danger');

    // Add our alert CSS and message
    createMakerMsgContainer.classList.add(evt.detail.css);
    createMakerMsgText.innerHTML = evt.detail.message;

    // Smoothscroll to the message container
    createMakerMsgContainer.scrollIntoView({
      behavior: 'smooth',
      block: 'start'
    });

    // Flash the Maker Msg Container
    const originalBackgroundColor = window.getComputedStyle(createMakerMsgContainer).backgroundColor;
    createMakerMsgContainer.classList.add('flash-effect');
    createMakerMsgContainer.addEventListener('animationend', () => {
      createMakerMsgContainer.classList.remove('flash-effect');
      createMakerMsgContainer.style.backgroundColor = originalBackgroundColor;
    }, {once: true});

    // Reset the form
    if( 'alert-success' == evt.detail.css ){
      createMakerForm.reset();
    }
  });
</script>