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

  function debounce(fn, delay = 120) {
    let timer = null;
    return function (...args) {
      clearTimeout(timer);
      timer = setTimeout(() => fn.apply(this, args), delay);
    };
  }

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

  function createMarkerContentElement(maker) {
    const wrapper = document.createElement("div");
    wrapper.className = "maker-marker";

    const imageUrl = maker.primary_image || "";
    const firstLetter = (maker.title || "").trim().charAt(0).toUpperCase() || "?";

    wrapper.innerHTML = `
      <div class="maker-marker-inner">
        ${
          imageUrl
            ? `<img src="${imageUrl}" alt="${maker.title}" />`
            : `<div class="maker-marker-fallback"><span>${firstLetter}</span></div>`
        }
      </div>
    `;

    return wrapper;
  }

  /**
   * Only recenter the map if the marker falls inside the right-side
   * danger zone (covered by the sidepanel).
   */
  function recenterMapForSidepanel(map, position) {
    const projection = map.getProjection();
    if (!projection) return;

    const div = map.getDiv();
    const rect = div.getBoundingClientRect();
    const width = rect.width;

    // Rightmost 30% of the map = danger zone
    const dangerZoneStartX = width * 0.60;

    // Convert lat/lng → world point → pixel point
    const scale = Math.pow(2, map.getZoom());
    const worldPoint = projection.fromLatLngToPoint(position);

    const pixelPoint = {
      x: worldPoint.x * scale,
      y: worldPoint.y * scale
    };

    // Where is the map's *top-left* corner in world pixels?
    const topLeftWorld = projection.fromLatLngToPoint(map.getBounds().getNorthEast());
    const topLeftPixel = {
      x: topLeftWorld.x * scale - width,
      y: topLeftWorld.y * scale
    };

    // Convert world pixel → container coordinates
    const markerX = pixelPoint.x - topLeftPixel.x;

    // If marker is NOT in the danger zone → don't shift
    if (markerX < dangerZoneStartX) {
      return;
    }

    // Marker *is* behind the sidepanel ⇒ apply recentering
    const offsetPx = +180;   // positive moves map left (desired)
    const worldOffsetX = offsetPx / scale;

    const newWorldPoint = new google.maps.Point(
      worldPoint.x + worldOffsetX,
      worldPoint.y
    );

    const newLatLng = projection.fromPointToLatLng(newWorldPoint);

    map.panTo(newLatLng);
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

      // NEW: Click to open sidepanel (w/ debouncing)
      marker.addListener(
        "click",
        debounce(() => {

          // ---- ANIMATION: pulse the marker ----
          const el = markerContent; // <div class="maker-marker">
          el.classList.remove("clicked");
          void el.offsetWidth; // force reflow so animation restarts every time
          el.classList.add("clicked");

          // ---- Open panel ----
          sidepanel.innerHTML = buildSidepanelHTML(maker);
          sidepanel.classList.add("open");
          enablePanelCloseButton();

          // ---- Recenter smartly ----
          const position = new google.maps.LatLng(maker.lat, maker.lng);

          requestAnimationFrame(() => {
            recenterMapForSidepanel(map, position);
          });

        }, 120)
      );  

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
