// /scripts/maker-search.js

function runWhenElementorLoaded(callback) {
  if (window.elementorFrontend && window.elementorFrontend.hooks) {
    callback();
  } else {
    setTimeout(() => runWhenElementorLoaded(callback), 100);
  }
}

function activateSecondTab() {
  const tabs = document.querySelectorAll('.elementor-tab-title');
  if (tabs.length > 1) {
    tabs[1].click();
  }
}

function setupTabs() {
  const tabTitles = document.querySelectorAll('.elementor-tab-title');
  const filterByTab = document.getElementById('elementor-tab-title-1631'); // Assuming this is the "Filter by:" tab
  const categoryTab = document.getElementById('elementor-tab-title-1632');

  tabTitles.forEach(tab => {
    tab.addEventListener('click', () => {
      if (tab.id !== 'elementor-tab-title-1631') {
        if (filterByTab.textContent === 'Filter by:') {
          filterByTab.textContent = 'Close Filter Menu';
          filterByTab.classList.add('button');
        }
      } else {
        filterByTab.textContent = 'Filter by:';
        filterByTab.classList.remove('button');
      }
    });
  });
}

export function initializeMakerSearch() {
  document.addEventListener('DOMContentLoaded', function() {
    runWhenElementorLoaded(activateSecondTab);
    setupTabs();
  });
}
