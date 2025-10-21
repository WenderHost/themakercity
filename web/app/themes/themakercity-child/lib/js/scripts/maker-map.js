document.addEventListener("DOMContentLoaded", function () {
  const { endpoint, category, mapId } = makerMapData;
  const mapElement = document.getElementById(mapId);
  if (!mapElement) return;

  // Map options
  const mapOptions = {
    zoom: 12,
    center: { lat: 35.9606, lng: -83.9207 },
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
   * Renders markers on the map based on filtered data.
   * @param {Array} makers Array of maker objects to render.
   */
  function renderMarkers(makers) {
    const bounds = new google.maps.LatLngBounds();

    // Clear previous markers
    if (clusterer) clusterer.clearMarkers();
    markers.forEach((m) => m.setMap(null));
    markers = [];

    makers.forEach((maker) => {
      const position = { lat: maker.lat, lng: maker.lng };
      const marker = new google.maps.Marker({
        position,
        title: maker.title,
      });

      const infowindow = new google.maps.InfoWindow({
        content: `
          <div class="maker-infowindow">
            <h3><a href="${maker.link}" target="_blank">${maker.title}</a></h3>
            <p>${maker.address}</p>
          </div>
        `,
      });

      marker.addListener("click", () => infowindow.open(map, marker));
      markers.push(marker);
      bounds.extend(position);
    });

    if (makers.length > 0) {
      map.fitBounds(bounds);
      clusterer = new markerClusterer.MarkerClusterer({ map, markers });
    }
  }

  /**
   * Builds the filter UI dynamically from maker-filters.
   * @param {Array} filters
   */
  function buildFilterUI(filters) {
    // Find or create container above map
    let wrapper = mapElement.closest(".maker-map-wrapper");
    if (!wrapper) {
      wrapper = document.createElement("div");
      wrapper.classList.add("maker-map-wrapper");
      mapElement.parentNode.insertBefore(wrapper, mapElement);
      wrapper.appendChild(mapElement);
    }

    // Create dropdown
    const select = document.createElement("select");
    select.classList.add("maker-map-filter");
    select.innerHTML = `<option value="all">All Maker Spaces</option>`;

    filters.forEach((filter) => {
      const option = document.createElement("option");
      option.value = filter.slug;
      option.textContent = filter.name;
      select.appendChild(option);
    });

    wrapper.insertBefore(select, mapElement);

    // Handle change event
    select.addEventListener("change", (e) => {
      const selected = e.target.value;
      if (selected === "all") {
        renderMarkers(allMakers);
      } else {
        const filtered = allMakers.filter((maker) =>
          maker.categories.includes(selected)
        );
        renderMarkers(filtered);
      }
    });
  }

  /**
   * Load makers + filters from REST API.
   */
  function loadData(categorySlug) {
    fetch(`${endpoint}?maker-category=${encodeURIComponent(categorySlug)}`)
      .then((res) => res.json())
      .then((data) => {
        if (!data || !data.makers) {
          console.warn("No makers found or invalid response:", data);
          return;
        }

        // Store makers globally for filtering
        allMakers = data.makers;

        // Build filter UI
        if (data["maker-filters"] && data["maker-filters"].length > 0) {
          buildFilterUI(data["maker-filters"]);
        }

        // Initial render
        renderMarkers(allMakers);
      })
      .catch((err) => console.error("Error loading maker locations:", err));
  }

  // Initial load
  loadData(category);
});
