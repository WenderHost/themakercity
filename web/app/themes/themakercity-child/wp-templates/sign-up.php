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
.fa-1, .fa-2{
  margin-right: .5rem;
  border-radius: 50%;
  background-color: #3b7ddd;
  padding: .35rem .6rem;
  color: #fff;
}
</style>
  <main class="d-flex w-100">
    <div class="container d-flex flex-column">
      <div class="row mt-6 text-center">
        <a href="<?= home_url() ?>" alt="Return home" style="display: block; margin: 0 auto 40px auto;"><img src="<?= MAKR_STYLESHEET_DIR_URI ?>lib/img/maker-icon_512x512.png" style="width: 100px;" /></a>
        <h1>The Maker City Directory Listing</h1>
      </div>
      <div class="row mt-4 justify-content-md-center">
        <div class="col-md-5">
          <div class="accordion" id="directory-steps">


            <div class="accordion-item">

              <h2 class="accordion-header" id="headingOne">
                <button class="accordion-button fs-4 fw-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                  <i class="fa-solid fa-1"></i> Need a new listing?
                </button>
              </h2>

              <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#directory-steps">
                <div class="accordion-body">
                  <p class="lead">
                    If you're not listed in The Maker City Directory, click here:
                  </p>
                  <div class="d-grid"><a href="<?= home_url('apply') ?>" role="button" class="btn btn-primary btn-lg" style="padding: .4rem 1rem; font-size: 1.1875rem; border-radius: .3rem;">Apply for a Maker Account</a></div>
                </div><!-- .accordion-body -->
              </div>
            </div><!-- .accordion-item -->


            <div class="accordion-item">

              <h2 class="accordion-header" id="headingTwo">
                <button class="accordion-button fs-4 fw-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                  <i class="fa-solid fa-2"></i> Already have a listing, but need a password?
                </button>
              </h2>

              <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#directory-steps">
                <div class="accordion-body">
                  <p class="lead">Submit your email and we’ll help you create a login to keep your listing fresh.</p>

                  <!-- START Reset Form -->
                  <div class="alert" id="reset-message" role="alert">
                    <div class="alert-message">Alert goes here...</div>
                  </div>

                  <div class="card mb-2">
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
                    <p class="mt-3"><span class="lead">Already have a log in?</span><br/>Use it <a href="<?= home_url('sign-in') ?>">here</a>, and make your listing shine!</p>
                  </div>
                  <!-- END Reset Form -->

                </div>
              </div>
            </div><!-- .accordion-item -->


          </div><!-- .accordion -->
        </div><!-- .col-md-6 -->
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