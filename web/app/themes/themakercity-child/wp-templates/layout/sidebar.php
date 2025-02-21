<?php
use function TheMakerCity\users\check_maker_profile_id;

$current_user = wp_get_current_user();
$avatar = get_avatar_url( $current_user->user_email, ['size' => 96] );
$maker_profile_id = get_user_meta( $current_user->ID, 'maker_profile_id', true );
if( empty( $maker_profile_id ) )
  $maker_profile_id = check_maker_profile_id();
$business_name = get_the_title( $maker_profile_id );
?>
<nav id="sidebar" class="sidebar js-sidebar">
  <div class="sidebar-content js-simplebar">
    <a class="sidebar-brand" href="<?= home_url() ?>">
      <span class="align-middle"><?= get_bloginfo( 'title' ) ?></span>
    </a>

        <div class="sidebar-user">
          <div class="d-flex justify-content-center">
            <div class="flex-shrink-0">
              <img src="<?= $avatar ?>" class="avatar img-fluid rounded me-1" alt="<?= $current_user->user_firstname ?> <?= $current_user->user_lastname ?>" /> 
            </div>
            <div class="flex-grow-1 ps-2">
              <span class="sidebar-user-title"><?= $current_user->user_firstname ?> <?= $current_user->user_lastname ?></span>
              <div class="sidebar-user-subtitle" style="font-size: .75em;"><?= $business_name ?></div>
            </div>
          </div>
        </div>    

    <ul class="sidebar-nav">
      <?php
      if( $maker_profile_id ){
        $profile_permalink = get_permalink( $maker_profile_id );
      ?>
      <li class="sidebar-item<?php if( strpos( $_SERVER['REQUEST_URI'], 'profile' ) ){ echo ' active'; } ?>">
        <a class="sidebar-link" href="<?= home_url( 'profile-editor' ) ?>">
          <i class="align-middle fas fa-user"></i> <span class="align-middle">Your Profile</span>
        </a>
      </li>
      <!--<li class="sidebar-item">
        <a class="sidebar-link" href="<?= $profile_permalink ?>" target="_blank">
          <i class="align-middle fas fa-arrow-up-right-from-square"></i> <span class="align-middle">View Your Profile</span>
        </a>
      </li>-->
      <li class="sidebar-item<?php if( strpos( $_SERVER['REQUEST_URI'], 'account' ) ){ echo ' active'; } ?>">
        <a class="sidebar-link" href="<?= home_url( 'account' ) ?>">
          <i class="align-middle fas fa-sliders"></i> <span class="align-middle">Your Account</span>
        </a>
      </li>      
      <?php } ?>
      <li class="sidebar-item">
        <hr/>
      </li>
      <li class="sidebar-item">
        <a class="sidebar-link" href="<?= home_url() ?>">
          <i class="align-middle fas fa-rotate-left"></i> <span class="align-middle">Return to Site</span>
        </a>
      </li>
      <?php if( current_user_can( 'activate_plugins' ) ){ ?>
      <li class="sidebar-item">
        <a class="sidebar-link" href="<?= site_url( '/wp-admin/' ) ?>" target="_blank">
          <i class="fas fa-arrow-up-right-from-square"></i> <span class="align-middle">WP Admin</span>
        </a>        
      </li>
      <?php } ?>      
      <li class="sidebar-item">
        <a class="sidebar-link" href="<?= wp_logout_url( home_url() ) ?>">
          <i class="fas fa-right-from-bracket"></i> <span class="align-middle">Log out</span>
        </a>
      </li>
    </ul>
  </div>
</nav>