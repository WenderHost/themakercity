<?php
$current_user = wp_get_current_user();
$avatar = get_avatar_url( $current_user->user_email, ['size' => 96] );
?>
      <nav class="navbar navbar-expand navbar-light navbar-bg">

        <div class="navbar-collapse collapse">
          <ul class="navbar-nav navbar-align">
            <li class="nav-item dropdown">
              <a class="nav-icon dropdown-toggle d-inline-block d-sm-none" href="#" data-bs-toggle="dropdown">
                <i class="align-middle" data-feather="settings"></i>
              </a>

              <a class="nav-link d-none d-sm-inline-block" href="#" data-bs-toggle="dropdown">
                <img src="<?= $avatar ?>" class="avatar img-fluid rounded me-1" alt="<?= $current_user->user_firstname ?> <?= $current_user->user_lastname ?>" /> <span class="text-dark"><?= $current_user->user_firstname ?> <?= $current_user->user_lastname ?></span>
              </a>
            </li>
          </ul>
        </div>
      </nav>