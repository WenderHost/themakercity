<nav id="sidebar" class="sidebar js-sidebar">
  <div class="sidebar-content js-simplebar">
    <a class="sidebar-brand" href="<?= home_url() ?>">
      <span class="align-middle"><?= get_bloginfo( 'title' ) ?></span>
    </a>

    <ul class="sidebar-nav">
      <li class="sidebar-header">
        Pages
      </li>

      <li class="sidebar-item<?php if( is_front_page() ){ echo ' active'; } ?>">
        <a class="sidebar-link" href="<?= home_url( 'profile' ) ?>">
          <i class="align-middle" data-feather="user"></i> <span class="align-middle">Your Profile</span>
        </a>
      </li>

      <li class="sidebar-item<?php if( is_post_type_archive( 'property' ) || 'property' == get_post_type() ){ echo ' active'; } ?>">
        <a class="sidebar-link" href="<?= home_url( 'account' ) ?>">
          <i class="align-middle" data-feather="sliders"></i> <span class="align-middle">Your Account</span>
        </a>
      </li>
      <li class="sidebar-item">
        <a class="sidebar-link" href="<?= home_url() ?>">
          <i class="align-middle" data-feather="corner-down-left"></i> <span class="align-middle">Return to Site</span>
        </a>
      </li>
      <!--
      <li class="sidebar-header">
        Tools & Components
      </li>

      <li class="sidebar-item">
        <a class="sidebar-link" href="ui-buttons.html">
          <i class="align-middle" data-feather="square"></i> <span class="align-middle">Buttons</span>
        </a>
      </li>

      <li class="sidebar-item">
        <a class="sidebar-link" href="ui-forms.html">
          <i class="align-middle" data-feather="check-square"></i> <span class="align-middle">Forms</span>
        </a>
      </li>

      <li class="sidebar-item">
        <a class="sidebar-link" href="ui-cards.html">
          <i class="align-middle" data-feather="grid"></i> <span class="align-middle">Cards</span>
        </a>
      </li>

      <li class="sidebar-item">
        <a class="sidebar-link" href="ui-typography.html">
          <i class="align-middle" data-feather="align-left"></i> <span class="align-middle">Typography</span>
        </a>
      </li>

      <li class="sidebar-item">
        <a class="sidebar-link" href="icons-feather.html">
          <i class="align-middle" data-feather="coffee"></i> <span class="align-middle">Icons</span>
        </a>
      </li>

      <li class="sidebar-header">
        Plugins & Addons
      </li>

      <li class="sidebar-item">
        <a class="sidebar-link" href="charts-chartjs.html">
          <i class="align-middle" data-feather="bar-chart-2"></i> <span class="align-middle">Charts</span>
        </a>
      </li>

      <li class="sidebar-item">
        <a class="sidebar-link" href="maps-google.html">
          <i class="align-middle" data-feather="map"></i> <span class="align-middle">Maps</span>
        </a>
      </li>
    -->
    </ul>
  </div>
</nav>