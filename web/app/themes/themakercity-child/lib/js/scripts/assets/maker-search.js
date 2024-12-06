// /scripts/maker-search.js
function activateSecondTab() {
  const tabs = document.querySelectorAll('.elementor-tab-title');
  if (tabs.length > 1) {
    tabs[1].click();
  }
}

function setupTabs() {
  const tabTitles = document.querySelectorAll('.elementor-tab-title');
  const filterByTab = document.getElementById('elementor-tab-title-1631'); // Assuming this is the "Filter by:" tab
  filterByTab.textContent = 'Initializing...';
  filterByTab.classList.add('button');
  const categoryTab = document.getElementById('elementor-tab-title-1632');

  tabTitles.forEach(tab => {
    tab.addEventListener('click', () => {
      /**
       * If we click on a tab that IS NOT the first tab, this means
       * we have "Opened" the filters. So we set the text of the
       * first tab to be "Close Filter Menu":
       */
      if (tab.id !== 'elementor-tab-title-1631') {
        if (filterByTab.textContent === 'Initializing...' || filterByTab.textContent === 'Filter by:') {
          filterByTab.textContent = 'Close Filter Menu';
          filterByTab.classList.add('button');
        }
      } else {
        /**
         * When we click directly on the first tab, we have "Closed"
         * all the filters. So, we set the text of this tab back to
         * "Filter by:":
         */
        filterByTab.textContent = 'Filter by:';
        filterByTab.classList.remove('button');
      }
    });
  });
}

export function initializeMakerSearch() {
  let inite = 0;
  document.addEventListener('DOMContentLoaded', async function() {
    const filterByTab = document.getElementById('elementor-tab-title-1631');
    if( filterByTab ){
      await setupTabs();
      setInterval(function(){
        if (document.readyState == "complete" && inite == 0 )  {
          activateSecondTab();
          inite++;
        }
      }, 500);      
    } else {
      console.log('👋 I did not find #elementor-tab-title-1631, so no need to initialize MakerSearch.');
    }
  });
}
