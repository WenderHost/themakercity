<!-- START Reset Form -->
<div class="alert alert-hide" id="reset-message" role="alert">
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
<!-- END Reset Form -->
<script>
  const resetMsgContainer = document.getElementById('reset-message');
  const resetMsgText = document.querySelector('#reset-message .alert-message');
  document.body.addEventListener('passwordReset', function(evt){
    resetMsgContainer.classList.remove('alert-success','alert-danger','alert-hide');
    resetMsgContainer.classList.add(evt.detail.css);
    resetMsgText.innerHTML = evt.detail.message;
  });

  const newAccountMsgContainer = document.getElementById('newAccount-message');
  const newAccountMsgText = document.querySelector('#newAccount-message .alert-message');
  document.body.addEventListener('newAccount', function(evt){
    newAccountMsgContainer.classList.remove('alert-success','alert-danger','alert-hide');
    newAccountMsgContainer.classList.add(evt.detail.css);
    newAccountMsgText.innerHTML = evt.detail.message;
    if( 'alert-success' == evt.detail.css ){
      setTimeout((evt) => {
        newAccountMsgContainer.classList.remove('alert-success','alert-danger','alert-hide');
        newAccountMsgText.innerHTML = '';
      }, 15000);
    }
  });

  const newAccountForm = document.getElementById('new-account');
  document.body.addEventListener('resetRegistrationForm', function(evt){
    newAccountForm.reset();
  });
</script>