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
            <!--
            <li class="nav-item dropdown">
              <a class="nav-icon dropdown-toggle" href="#" id="alertsDropdown" data-bs-toggle="dropdown">
                <div class="position-relative">
                  <i class="align-middle" data-feather="bell"></i>
                  <span class="indicator">4</span>
                </div>
              </a>
              <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end py-0" aria-labelledby="alertsDropdown">
                <div class="dropdown-menu-header">
                  4 New Notifications
                </div>
                <div class="list-group">
                  <a href="#" class="list-group-item">
                    <div class="row g-0 align-items-center">
                      <div class="col-2">
                        <i class="text-danger" data-feather="alert-circle"></i>
                      </div>
                      <div class="col-10">
                        <div class="text-dark">Update completed</div>
                        <div class="text-muted small mt-1">Restart server 12 to complete the update.</div>
                        <div class="text-muted small mt-1">30m ago</div>
                      </div>
                    </div>
                  </a>
                  <a href="#" class="list-group-item">
                    <div class="row g-0 align-items-center">
                      <div class="col-2">
                        <i class="text-warning" data-feather="bell"></i>
                      </div>
                      <div class="col-10">
                        <div class="text-dark">Lorem ipsum</div>
                        <div class="text-muted small mt-1">Aliquam ex eros, imperdiet vulputate hendrerit et.</div>
                        <div class="text-muted small mt-1">2h ago</div>
                      </div>
                    </div>
                  </a>
                  <a href="#" class="list-group-item">
                    <div class="row g-0 align-items-center">
                      <div class="col-2">
                        <i class="text-primary" data-feather="home"></i>
                      </div>
                      <div class="col-10">
                        <div class="text-dark">Login from 192.186.1.8</div>
                        <div class="text-muted small mt-1">5h ago</div>
                      </div>
                    </div>
                  </a>
                  <a href="#" class="list-group-item">
                    <div class="row g-0 align-items-center">
                      <div class="col-2">
                        <i class="text-success" data-feather="user-plus"></i>
                      </div>
                      <div class="col-10">
                        <div class="text-dark">New connection</div>
                        <div class="text-muted small mt-1">Christina accepted your request.</div>
                        <div class="text-muted small mt-1">14h ago</div>
                      </div>
                    </div>
                  </a>
                </div>
                <div class="dropdown-menu-footer">
                  <a href="#" class="text-muted">Show all notifications</a>
                </div>
              </div>
            </li>
            -->
            <!--
            <li class="nav-item dropdown">
              <a class="nav-icon dropdown-toggle" href="#" id="messagesDropdown" data-bs-toggle="dropdown">
                <div class="position-relative">
                  <i class="align-middle" data-feather="message-square"></i>
                </div>
              </a>
              <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end py-0" aria-labelledby="messagesDropdown">
                <div class="dropdown-menu-header">
                  <div class="position-relative">
                    4 New Messages
                  </div>
                </div>
                <div class="list-group">
                  <a href="#" class="list-group-item">
                    <div class="row g-0 align-items-center">
                      <div class="col-2">
                        <img src="<?= MAKR_STYLESHEET_DIR_URI ?>adminkit-pro/static/img/avatars/avatar-5.jpg" class="avatar img-fluid rounded-circle" alt="Vanessa Tucker">
                      </div>
                      <div class="col-10 ps-2">
                        <div class="text-dark">Vanessa Tucker</div>
                        <div class="text-muted small mt-1">Nam pretium turpis et arcu. Duis arcu tortor.</div>
                        <div class="text-muted small mt-1">15m ago</div>
                      </div>
                    </div>
                  </a>
                  <a href="#" class="list-group-item">
                    <div class="row g-0 align-items-center">
                      <div class="col-2">
                        <img src="<?= MAKR_STYLESHEET_DIR_URI ?>adminkit-pro/static/img/avatars/avatar-2.jpg" class="avatar img-fluid rounded-circle" alt="William Harris">
                      </div>
                      <div class="col-10 ps-2">
                        <div class="text-dark">William Harris</div>
                        <div class="text-muted small mt-1">Curabitur ligula sapien euismod vitae.</div>
                        <div class="text-muted small mt-1">2h ago</div>
                      </div>
                    </div>
                  </a>
                  <a href="#" class="list-group-item">
                    <div class="row g-0 align-items-center">
                      <div class="col-2">
                        <img src="<?= MAKR_STYLESHEET_DIR_URI ?>adminkit-pro/static/img/avatars/avatar-4.jpg" class="avatar img-fluid rounded-circle" alt="Christina Mason">
                      </div>
                      <div class="col-10 ps-2">
                        <div class="text-dark">Christina Mason</div>
                        <div class="text-muted small mt-1">Pellentesque auctor neque nec urna.</div>
                        <div class="text-muted small mt-1">4h ago</div>
                      </div>
                    </div>
                  </a>
                  <a href="#" class="list-group-item">
                    <div class="row g-0 align-items-center">
                      <div class="col-2">
                        <img src="<?= MAKR_STYLESHEET_DIR_URI ?>adminkit-pro/static/img/avatars/avatar-3.jpg" class="avatar img-fluid rounded-circle" alt="Sharon Lessman">
                      </div>
                      <div class="col-10 ps-2">
                        <div class="text-dark">Sharon Lessman</div>
                        <div class="text-muted small mt-1">Aenean tellus metus, bibendum sed, posuere ac, mattis non.</div>
                        <div class="text-muted small mt-1">5h ago</div>
                      </div>
                    </div>
                  </a>
                </div>
                <div class="dropdown-menu-footer">
                  <a href="#" class="text-muted">Show all messages</a>
                </div>
              </div>
            </li>
            -->
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