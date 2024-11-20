<?php
/**
 * Reset password screen.
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
.alert.alert-success,.alert.alert-warning{
    display:flex;
}
</style>
  <main class="d-flex w-100">
    <div class="container d-flex flex-column">
      <div class="row vh-100">
        <div class="col-sm-10 col-md-8 col-lg-6 col-xl-5 mx-auto d-table h-100">
          <div class="d-table-cell align-middle">

            <div class="text-center mt-4 mb-3">
              <a href="<?= home_url() ?>" alt="Return home" style="display: block; margin: 0 auto 20px auto;"><img src="<?= MAKR_STYLESHEET_DIR_URI ?>lib/img/maker-icon_512x512.png" style="width: 100px;" /></a>
              <h1 class="h2">Password Reset</h1>
            </div>

            <?= get_template_part( 'wp-templates/sections/reset-password-form' ) ?>

            <div class="text-center mt-3 mb-3">
              <p class="lead">Don't have an account? <a href="<?= home_url('sign-up') ?>">Sign up</a></p>
              <p><a href="<?= home_url() ?>">&larr; Return to The Maker City</a>.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
