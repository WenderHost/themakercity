// assets/hide-empty-elements.js
export function hideEmptyElements() {
  document.addEventListener('DOMContentLoaded', function() {
    const shortcodes = document.querySelectorAll('.elementor-shortcode');
    console.log('🔔 shortcodes == ', shortcodes );
    if( 0 < shortcodes.length ){
      shortcodes.forEach(function(shortcode) {
        // Check if the element's text content is empty (ignoring whitespace) and it has no visible child nodes
        if (shortcode.textContent.trim() === '' && !hasVisibleContent(shortcode)) {
          const element = shortcode.closest('.elementor-element');
          if (element) {
            element.style.display = 'none';
          }
        }
      });
    } else {
      console.info('👉 No shortcodes found.');
    }
  });

  // Helper function to determine if an element has visible content
  function hasVisibleContent(element) {
    const allChildren = element.getElementsByTagName('*');
    for (const child of allChildren) {
      if (isVisible(child)) {
        return true;
      }
    }
    return false;
  }

  // Helper function to check if a given element is visible
  function isVisible(elem) {
    return !!(elem.offsetWidth || elem.offsetHeight || elem.getClientRects().length);
  }
}
