document.addEventListener("DOMContentLoaded", function () {
  const { endpoint, mapId } = makerMapData;
  const mapElement = document.getElementById(mapId);
  if (!mapElement) return;

  // Map config
  const MAX_SINGLE_MARKER_ZOOM = 14;
  const FILTER_MODE = "AND"; // "AND" or "OR"

  const mapOptions = {
    zoom: 12,
    center: { lat: 35.9606, lng: -83.9207 },
    mapId: "6bc30825e5dd2d0987897fd0",
    zoomControl: true,
    mapTypeControl: true,
    streetViewControl: true,
    fullscreenControl: true,
    gestureHandling: "auto",
  };

  const map = new google.maps.Map(mapElement, mapOptions);
  let clusterer = null;
  let allMakers = []; // store all makers for client-side filtering
  let markers = [];

  /**
   * Helper: build the SVG string for a marker.
   * Uses maker.primary_image if available, otherwise a letter badge.
   *
   * @param {Object} maker
   * @returns {string}
   */
  function buildMarkerSvgString(maker) {
    const imageUrl = maker.primary_image || "";
    const hasImage = !!imageUrl;
    const firstLetter =
      (maker.title || "").trim().charAt(0).toUpperCase() || "?";

    // SVG is 64x72 with a circular "photo" area at 32,32 radius 29
    return `
<svg version="1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 72">
  <style>
    .fill { fill: #f4f4f4; }
    .outline { fill: #d1d1d1; }
  </style>

  <!-- Outer Marker Housing -->
  <path class="fill" d="M27.2 63.1H27C11.6 60.6.5 47.6.5 32 .5 14.6 14.6.5 32 .5S63.5 14.6 63.5 32c0 15.6-11.1 28.6-26.5 31.1h-.2L32 71l-4.8-7.9z"/>
  <path class="outline" d="M32 1c17.1 0 31 13.9 31 31 0 7.4-2.7 14.6-7.5 20.2s-11.4 9.2-18.6 10.4l-.5.1-.2.4-4.2 7-4.2-7-.2-.4-.5-.1c-7.2-1.1-13.8-4.8-18.6-10.4C3.7 46.6 1 39.4 1 32 1 14.9 14.9 1 32 1m0-1C14.3 0 0 14.3 0 32c0 15.9 11.7 29.2 26.9 31.6L32 72l5.1-8.4C52.3 61.2 64 47.9 64 32 64 14.3 49.7 0 32 0z"/>

  <defs>
    <clipPath id="primary-image-clip">
      <circle cx="32" cy="32" r="29" />
    </clipPath>
  </defs>

  ${
    hasImage
      ? `
  <!-- Maker primary image -->
  <image
    href="${imageUrl}"
    x="3"
    y="3"
    width="58"
    height="58"
    clip-path="url(#primary-image-clip)"
    preserveAspectRatio="xMidYMid slice"
  />
  `
      : `
  <!-- Letter badge fallback -->
  <circle cx="32" cy="32" r="29" fill="#7ac5e5" />
  <text
    x="32"
    y="39"
    text-anchor="middle"
    font-size="24"
    font-family="system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif"
    fill="#ffffff"
  >${firstLetter}</text>
  `
  }
</svg>`;
  }

  /**
   * Helper: convert SVG string to a DOM node wrapped in a div
   * so we can apply hover scaling via CSS (.maker-marker).
   *
   * @param {Object} maker
   * @returns {HTMLElement}
   */
  function createMarkerContentElement(maker) {
    const wrapper = document.createElement("div");
    wrapper.className = "maker-marker";
    wrapper.innerHTML = buildMarkerSvgString(maker).trim();
    return wrapper;
  }

  /**
   * Renders markers on the map based on filtered data.
   * @param {Array} makers Array of maker objects to render.
   */
  function renderMarkers(makers) {
    const bounds = new google.maps.LatLngBounds();

    // Clear previous markers & clusters
    if (clusterer) {
      clusterer.clearMarkers();
    }
    markers.forEach((m) => {
      m.map = null; // AdvancedMarkerElement uses the 'map' property
    });
    markers = [];

    makers.forEach((maker) => {
      const position = { lat: maker.lat, lng: maker.lng };

      const markerContent = createMarkerContentElement(maker);

      const marker = new google.maps.marker.AdvancedMarkerElement({
        position,
        content: markerContent,
        title: maker.title,
        map: map,
      });

      const infowindow = new google.maps.InfoWindow({
        content: `
          <div class="maker-infowindow">
            <h3><a href="${maker.link}" target="_blank" rel="noopener noreferrer">${maker.title}</a></h3>
            <p>${maker.address}</p>
          </div>
        `,
      });

      marker.addListener("click", () => {
        infowindow.open({
          anchor: marker,
          map,
        });
      });

      markers.push(marker);
      bounds.extend(position);
    });

    if (makers.length > 0) {
      map.fitBounds(bounds);

      // Cap zoom on single marker
      if (makers.length === 1) {
        google.maps.event.addListenerOnce(map, "bounds_changed", function () {
          if (map.getZoom() > MAX_SINGLE_MARKER_ZOOM) {
            map.setZoom(MAX_SINGLE_MARKER_ZOOM);
          }
        });
      }

      clusterer = new markerClusterer.MarkerClusterer({
        map,
        markers,
      });
    }
  }

  /**
   * Builds the filter UI using maker-space-types.
   * @param {Array} types List of taxonomy term objects
   */
  function buildFilterUI(types) {
    let wrapper = mapElement.closest(".maker-map-wrapper");
    if (!wrapper) {
      wrapper = document.createElement("div");
      wrapper.classList.add("maker-map-wrapper");
      mapElement.parentNode.insertBefore(wrapper, mapElement);
      wrapper.appendChild(mapElement);
    }

    const filterContainer = document.createElement("div");
    filterContainer.classList.add("maker-map-filters");
    filterContainer.innerHTML = `<strong>Filter by Type:</strong>`;

    // Show All option
    const allLabel = document.createElement("label");
    allLabel.classList.add("maker-filter-item");

    const allCheckbox = document.createElement("input");
    allCheckbox.type = "checkbox";
    allCheckbox.value = "all";
    allCheckbox.checked = true;

    allLabel.appendChild(allCheckbox);
    allLabel.append(" All Maker Spaces");
    filterContainer.appendChild(allLabel);

    // Individual type checkboxes
    types.forEach((type) => {
      const label = document.createElement("label");
      label.classList.add("maker-filter-item");

      const checkbox = document.createElement("input");
      checkbox.type = "checkbox";
      checkbox.value = type.slug;
      checkbox.checked = false;

      label.appendChild(checkbox);

      // Show "Name (count)" using the count from the REST response
      const labelText =
        typeof type.count === "number"
          ? `${type.name} (${type.count})`
          : type.name;

      label.append(` ${labelText}`);
      filterContainer.appendChild(label);
    });

    wrapper.insertBefore(filterContainer, mapElement);

    /**
     * Filter change handler
     */
    filterContainer.addEventListener("change", (e) => {
      const checkboxes = filterContainer.querySelectorAll(
        'input[type="checkbox"]'
      );
      const selected = Array.from(checkboxes)
        .filter((cb) => cb.checked && cb.value !== "all")
        .map((cb) => cb.value);

      // Show All checked
      if (e.target.value === "all" && e.target.checked) {
        checkboxes.forEach((cb) => {
          if (cb.value !== "all") cb.checked = false;
        });
        renderMarkers(allMakers);
        return;
      }

      // If specific filters are selected
      if (selected.length > 0) {
        allCheckbox.checked = false;

        const has = (maker, slug) =>
          Array.isArray(maker.space_types) &&
          maker.space_types.includes(slug);

        const filtered = allMakers.filter((maker) =>
          FILTER_MODE === "AND"
            ? selected.every((slug) => has(maker, slug))
            : selected.some((slug) => has(maker, slug))
        );

        renderMarkers(filtered);
      } else {
        // No filters → revert to all
        allCheckbox.checked = true;
        renderMarkers(allMakers);
      }
    });
  }

  /**
   * Loads makers + maker-space-type terms.
   */
  function loadData() {
    fetch(endpoint)
      .then((res) => res.json())
      .then((data) => {
        if (!data || !data.makers) {
          console.warn("Invalid response:", data);
          return;
        }

        allMakers = data.makers;

        // Build filter UI using maker-space-types
        if (
          data["maker-space-types"] &&
          data["maker-space-types"].length > 0
        ) {
          buildFilterUI(data["maker-space-types"]);
        }

        // Initial map render
        renderMarkers(allMakers);
      })
      .catch((err) => console.error("Error loading maker locations:", err));
  }

  // Initial load
  loadData();
});
