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

                <?= get_template_part( 'wp-templates/sections/reset-password-form' ) ?>
                <div class="text-center mb-3">
                  <p class="mt-3"><span class="lead">Already have a log in?</span><br/>Use it <a href="<?= home_url('sign-in') ?>">here</a>, and make your listing shine!</p>
                </div>

                </div>
              </div>
            </div><!-- .accordion-item -->


          </div><!-- .accordion -->
        </div><!-- .col-md-6 -->
      </div>
    </div>
  </main>
