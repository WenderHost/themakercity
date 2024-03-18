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
.alert.alert-success,.alert.alert-warning{
    display:flex;
}
</style>
  <main class="d-flex w-100">
    <div class="container d-flex flex-column">
      <div class="row vh-100">
        <div class="col-sm-10 col-md-8 col-lg-6 col-xl-5 mx-auto d-table h-100">
          <div class="d-table-cell align-middle">

            <div class="text-center mt-4">
              <a href="<?= home_url() ?>" alt="Return home" style="display: block; margin: 0 auto 20px auto;"><img src="<?= MAKR_STYLESHEET_DIR_URI ?>lib/img/maker-icon_512x512.png" style="width: 100px;" /></a>
              <h1 class="h2">The Maker City</h1>
              <p class="lead">
                Sign in to your account to continue
              </p>
            </div>
            <div class="alert" id="login-message" role="alert">
              <div class="alert-message">Alert goes here...</div>
            </div>

            <div class="card">
              <div class="card-body">
                <div class="m-sm-3">
                  <form hx-post="/wp-htmx/v1/noswap/login" hx-swap="none">
                    <input type="hidden" name="action" value="htmx_login">
                    <div class="mb-3">
                      <label class="form-label">Email</label>
                      <input class="form-control form-control-lg" type="email" name="email" placeholder="Enter your email" />
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Password</label>
                      <input class="form-control form-control-lg" type="password" name="password" placeholder="Enter your password" />
                    </div>
                    <div>
                      <div class="form-check align-items-center">
                        <input id="customControlInline" type="checkbox" class="form-check-input" value="true" name="remember-me" checked>
                        <label class="form-check-label text-small" for="customControlInline">Remember me</label>
                      </div>
                    </div>
                    <div class="d-grid gap-2 mt-3">
                      <!--<a href="index.html" class="btn btn-lg btn-primary">Sign in</a>-->
                      <input type="submit" class="btn btn-lg btn-primary" value="Submit" />
                    </div>
                  </form>
                </div>
              </div>
            </div>
            <div class="text-center mb-3">
              <p class="lead">Don't have an account? <a href="<?= home_url('sign-up') ?>">Sign up</a></p>
              <p><a href="<?= home_url() ?>">&larr; Return to The Maker City</a>.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
<script type="text/javascript">
const loginMsgContainer = document.getElementById('login-message');
const loginMsgText = document.querySelector('#login-message .alert-message');

document.body.addEventListener("loginSuccess", function(evt){
  console.log("👉 `loginSuccess` was triggered!");
  console.log('🔔 evt.detail.redirect_url = ', evt.detail.redirect_url );

  loginMsgContainer.classList.add(evt.detail.css);
  loginMsgText.innerHTML = evt.detail.message;
  setTimeout( () => {
    window.location.href = evt.detail.redirect_url;
  }, 1750);
})

document.body.addEventListener("loginFail", function(evt){
  console.log("`loginFail` was triggered!");
  console.log('🔔 evt.detail = ', evt.detail );

  loginMsgContainer.classList.add(evt.detail.css);
  loginMsgText.innerHTML = evt.detail.message;
})

document.body.addEventListener("submit", function(evt){
  loginMsgContainer.classList.remove("alert-success","alert-warning");
})
</script>