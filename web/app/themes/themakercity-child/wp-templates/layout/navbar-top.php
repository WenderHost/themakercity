<?php
$current_user = wp_get_current_user();
$avatar = get_avatar_url( $current_user->user_email, ['size' => 96] );
?>
      <nav class="navbar navbar-expand navbar-light navbar-bg">
        <a class="sidebar-toggle js-sidebar-toggle">
          <i class="hamburger align-self-center"></i>
        </a>

        <div class="navbar-collapse collapse">
          <ul class="navbar-nav navbar-align">
            <li class="nav-item">
              <a class="nav-icon js-fullscreen d-none d-lg-block" href="#">
                <div class="position-relative">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-maximize align-middle"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path></svg>
                </div>
              </a>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-icon dropdown-toggle d-inline-block d-sm-none" href="#" data-bs-toggle="dropdown">
                <i class="align-middle" data-feather="settings"></i>
              </a>

              <a class="nav-link dropdown-toggle d-none d-sm-inline-block" href="#" data-bs-toggle="dropdown">
                <img src="<?= $avatar ?>" class="avatar img-fluid rounded me-1" alt="<?= $current_user->user_firstname ?> <?= $current_user->user_lastname ?>" /> <span class="text-dark"><?= $current_user->user_firstname ?> <?= $current_user->user_lastname ?></span>
              </a>
              <div class="dropdown-menu dropdown-menu-end" style="z-index: 9999;">
                <a class="dropdown-item" href="<?= home_url('/profile') ?>"><i class="align-middle me-1" data-feather="user"></i> Profile</a>
                <?php if( current_user_can( 'activate_plugins' ) ){ ?>
                <a class="dropdown-item" href="<?= site_url( '/wp-admin/' ) ?>" target="_blank"><i class="fas fa-arrow-up-right-from-square"></i> WP Admin</a>
                <?php } ?>
                <!--
                <a class="dropdown-item" href="#"><i class="align-middle me-1" data-feather="pie-chart"></i> Analytics</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="index.html"><i class="align-middle me-1" data-feather="settings"></i> Settings & Privacy</a>
                <a class="dropdown-item" href="#"><i class="align-middle me-1" data-feather="help-circle"></i> Help Center</a>
                -->
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="<?= wp_logout_url( home_url() ) ?>">Log out</a>
              </div>
            </li>
          </ul>
        </div>
      </nav>