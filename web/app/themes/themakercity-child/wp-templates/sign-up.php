<?php
/**
 * Sign In screen for the application.
 *
 * Uses:
 * - [HTMX API WP]https://github.com/TCattd/HTMX-API-WP
 * - [HX-Trigger Response Header](https://htmx.org/headers/hx-trigger/)
 *
 * @author     Mwender
 * @since      2024
 */
?>
<style>
.alert{
    display:none;
}
.alert.alert-success,.alert.alert-warning,.alert-danger{
    display:flex;
}
</style>
  <main class="d-flex w-100">
    <div class="container d-flex flex-column">
      <div class="row mt-6 text-center">
        <a href="<?= home_url() ?>" alt="Return home" style="display: block; margin: 0 auto 40px auto;"><img src="<?= MAKR_STYLESHEET_DIR_URI ?>lib/img/maker-icon_512x512.png" style="width: 100px;" /></a>
        <h1>Sign Up for The Makers Directory</h1>
      </div>
      <div class="row mt-6">
        <div class="col-sm-10 col-md-8 col-lg-6 col-xl-5 mx-auto d-table h-100">
          <div class="d-table-cell align-top">

            <div class="text-center mt-4">
              <h2>Already listed in our Directory?</h2>
              <p class="lead" style="text-align: center;">
                If you have a listing in our <a href="<?= home_url( '/makers/' ) ?>" target="_blank">Maker Directory</a>, then you already have an account. Gain access by entering your sign up email, and we'll send you a password reset:
              </p>
            </div>
            <div class="alert" id="reset-message" role="alert">
              <div class="alert-message">Alert goes here...</div>
            </div>

            <div class="card">
              <div class="card-body">
                <div class="m-sm-3">
                  <form id="password-reset" hx-post="/wp-htmx/v1/noswap/resetpassword" hx-swap="none">
                    <input type="hidden" name="action" value="htmx_passwordreset">
                    <div class="mb-3">
                      <label class="form-label">Email</label>
                      <input class="form-control form-control-lg" type="email" name="email" placeholder="Enter your email" />
                    </div>
                    <div class="d-grid gap-2 mt-3">
                      <input type="submit" class="btn btn-lg btn-primary" value="Reset Password" />
                    </div>
                  </form>
                </div>
              </div>
            </div><!-- .card -->
            <div class="text-center mb-3">
              <p class="lead">Need to <a href="<?= home_url('sign-in') ?>">sign in</a>?</p>
            </div>
          </div>
        </div>
        <div class="col-sm-10 col-md-8 col-lg-6 col-xl-5 mx-auto d-table h-100">
          <div class="d-table-cell align-top">

            <div class="text-center mt-4">
              <h2>Need an account?</h2>
              <p class="lead">
                If you're not listed in our Maker Directory, fill out the form below. One of our staff will review your submission. Once approved, you can create and edit your profile.
              </p>
            </div><!-- .text-center mt-4 -->
            <div class="alert" id="newAccount-message" role="alert">
              <div class="alert-message">Alert goes here...</div>
            </div>

            <div class="card">
              <div class="card-body">
                <div class="m-sm-3">
                  <form id="new-account" hx-post="/wp-htmx/v1/noswap/register" hx-swap="none">
                    <input type="hidden" name="action" value="htmx_register">
                    <div class="mb-3">
                      <label class="form-label">Name</label>
                      <input class="form-control form-control-lg" type="text" name="name" placeholder="Enter your name" />
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Email</label>
                      <input class="form-control form-control-lg" type="email" name="email" placeholder="Enter your email" />
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Describe Your Business</label>
                      <textarea class="form-control form-control-lg" name="business_description" id="business_description" cols="30" rows="4"></textarea>
                    </div>
                    <div class="d-grid gap-2 mt-3">
                      <input type="submit" class="btn btn-lg btn-primary" value="Apply For a Maker Account" />
                    </div>
                  </form>
                </div>
              </div>
            </div><!-- .card -->

          </div>
        </div>
      </div>
    </div>
  </main>
<script>
  const resetMsgContainer = document.getElementById('reset-message');
  const resetMsgText = document.querySelector('#reset-message .alert-message');
  document.body.addEventListener('passwordReset', function(evt){
    resetMsgContainer.classList.remove('alert-success','alert-danger');
    resetMsgContainer.classList.add(evt.detail.css);
    resetMsgText.innerHTML = evt.detail.message;
  });

  const newAccountMsgContainer = document.getElementById('newAccount-message');
  const newAccountMsgText = document.querySelector('#newAccount-message .alert-message');
  document.body.addEventListener('newAccount', function(evt){
    newAccountMsgContainer.classList.remove('alert-success','alert-danger');
    newAccountMsgContainer.classList.add(evt.detail.css);
    newAccountMsgText.innerHTML = evt.detail.message;
    if( 'alert-success' == evt.detail.css ){
      setTimeout((evt) => {
        newAccountMsgContainer.classList.remove('alert-success','alert-danger');
        newAccountMsgText.innerHTML = '';
      }, 15000);
    }
  });

  const newAccountForm = document.getElementById('new-account');
  document.body.addEventListener('resetRegistrationForm', function(evt){
    newAccountForm.reset();
  });
</script>