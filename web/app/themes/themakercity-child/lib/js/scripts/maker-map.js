document.addEventListener("DOMContentLoaded", function () {
  const { endpoint, mapId } = makerMapData;
  const mapElement = document.getElementById(mapId);
  if (!mapElement) return;

  /**
   * Create sidepanel inside the wrapper (NOT inside mapElement),
   * so it visually floats over the map but is not under Google Maps layers.
   */
  function createSidepanel() {
    const wrapper = mapElement.closest(".maker-map-wrapper");

    const sidepanel = document.createElement("div");
    sidepanel.id = "maker-sidepanel";
    sidepanel.className = "maker-sidepanel";

    wrapper.appendChild(sidepanel);
    return sidepanel;
  }

  const sidepanel = createSidepanel();

  // Close panel
  function closeSidepanel() {
    sidepanel.classList.remove("open");
    sidepanel.innerHTML = "";
  }

  // Clicking X closes panel
  function enablePanelCloseButton() {
    const closeBtn = sidepanel.querySelector(".maker-panel-close");
    if (closeBtn) {
      closeBtn.addEventListener("click", () => {
        closeSidepanel();
      });
    }
  }

  // Clicking the map closes panel
  function attachMapClickClose(map) {
    map.addListener("click", () => {
      closeSidepanel();
    });
  }

  // Map configuration
  const MAX_SINGLE_MARKER_ZOOM = 14;
  const FILTER_MODE = "AND";

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
  attachMapClickClose(map);

  let clusterer = null;
  let allMakers = [];
  let markers = [];

  /**
   * Sidepanel HTML Template – customize freely
   */
  function buildSidepanelHTML(maker) {
    return `
      <div class="maker-panel-header">
        <h2>${maker.title}</h2>
        <button class="maker-panel-close">&times;</button>
      </div>

      <div class="maker-panel-body">
        ${
          maker.primary_image
            ? `<img class="maker-panel-photo" src="${maker.primary_image}" alt="${maker.title}" />`
            : ""
        }
        <p class="maker-panel-address">${maker.address}</p>

        <a class="maker-panel-link" href="${maker.link}" target="_blank" rel="noopener noreferrer">
          View Maker Profile →
        </a>
      </div>
    `;
  }

  /**
   * Build SVG marker content
   */
  function buildMarkerSvgString(maker) {
    const imageUrl = maker.primary_image || "";
    const hasImage = !!imageUrl;
    const firstLetter = (maker.title || "").trim().charAt(0).toUpperCase() || "?";

    return `
<svg version="1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 72">
  <style>
    .fill { fill: #f4f4f4; }
    .outline { fill: #d1d1d1; }
  </style>

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
        <image
          href="${imageUrl}"
          x="3"
          y="3"
          width="58"
          height="58"
          clip-path="url(#primary-image-clip)"
          preserveAspectRatio="xMidYMid slice"
        />`
      : `
        <circle cx="32" cy="32" r="29" fill="#7ac5e5" />
        <text
          x="32"
          y="39"
          text-anchor="middle"
          font-size="24"
          font-family="system-ui"
          fill="#ffffff"
        >${firstLetter}</text>`
  }
</svg>`;
  }

  function createMarkerContentElement(maker) {
    const wrapper = document.createElement("div");
    wrapper.className = "maker-marker";
    wrapper.innerHTML = buildMarkerSvgString(maker).trim();
    return wrapper;
  }

  /**
   * Render markers with AdvancedMarkerElement
   */
  function renderMarkers(makers) {
    const bounds = new google.maps.LatLngBounds();

    if (clusterer) clusterer.clearMarkers();
    markers.forEach((m) => (m.map = null));
    markers = [];

    makers.forEach((maker) => {
      const position = { lat: maker.lat, lng: maker.lng };
      const markerContent = createMarkerContentElement(maker);

      const marker = new google.maps.marker.AdvancedMarkerElement({
        position,
        content: markerContent,
        title: maker.title,
        map,
      });

      // Click → open sidepanel
      marker.addListener("click", () => {
        sidepanel.innerHTML = buildSidepanelHTML(maker);
        sidepanel.classList.add("open");
        enablePanelCloseButton();
      });

      markers.push(marker);
      bounds.extend(position);
    });

    if (makers.length > 0) {
      map.fitBounds(bounds);

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
   * Build filter UI
   */
  function buildFilterUI(types) {
    let wrapper = mapElement.closest(".maker-map-wrapper");

    const filterContainer = document.createElement("div");
    filterContainer.classList.add("maker-map-filters");
    filterContainer.innerHTML = `<strong>Filter by Type:</strong>`;

    // "All Maker Spaces"
    const allLabel = document.createElement("label");
    allLabel.classList.add("maker-filter-item");

    const allCheckbox = document.createElement("input");
    allCheckbox.type = "checkbox";
    allCheckbox.value = "all";
    allCheckbox.checked = true;

    allLabel.appendChild(allCheckbox);
    allLabel.append(" All Maker Spaces");
    filterContainer.appendChild(allLabel);

    // Individual taxonomy filters
    types.forEach((type) => {
      const label = document.createElement("label");
      label.classList.add("maker-filter-item");

      const checkbox = document.createElement("input");
      checkbox.type = "checkbox";
      checkbox.value = type.slug;

      label.appendChild(checkbox);
      label.append(` ${type.name} (${type.count})`);

      filterContainer.appendChild(label);
    });

    wrapper.insertBefore(filterContainer, mapElement);

    // Filtering logic
    filterContainer.addEventListener("change", (e) => {
      const checkboxes = filterContainer.querySelectorAll("input[type=checkbox]");
      const selected = Array.from(checkboxes)
        .filter((cb) => cb.checked && cb.value !== "all")
        .map((cb) => cb.value);

      if (e.target.value === "all" && e.target.checked) {
        checkboxes.forEach((cb) => {
          if (cb.value !== "all") cb.checked = false;
        });
        renderMarkers(allMakers);
        return;
      }

      if (selected.length > 0) {
        allCheckbox.checked = false;

        const has = (maker, slug) =>
          Array.isArray(maker.space_types) && maker.space_types.includes(slug);

        const filtered = allMakers.filter((maker) =>
          FILTER_MODE === "AND"
            ? selected.every((slug) => has(maker, slug))
            : selected.some((slug) => has(maker, slug))
        );

        renderMarkers(filtered);
      } else {
        allCheckbox.checked = true;
        renderMarkers(allMakers);
      }
    });
  }

  /**
   * Load data + initialize
   */
  function loadData() {
    fetch(endpoint)
      .then((res) => res.json())
      .then((data) => {
        if (!data || !data.makers) return;

        allMakers = data.makers;

        if (data["maker-space-types"]?.length > 0) {
          buildFilterUI(data["maker-space-types"]);
        }

        renderMarkers(allMakers);
      })
      .catch((err) => console.error("Error loading maker locations:", err));
  }

  loadData();
});
