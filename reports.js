let map;
let geocoder;

function initMap() {
  const defaultLocation = { lat: -26.2041, lng: 28.0473 }; // Johannesburg
  map = new google.maps.Map(document.getElementById("map"), {
    center: defaultLocation,
    zoom: 12,
  });

  geocoder = new google.maps.Geocoder();

  // Search location manually
  document.getElementById("searchBtn").addEventListener("click", () => {
    const address = document.getElementById("location").value;
    if (address.trim() !== "") {
      geocodeAddress(address);
    } else {
      alert("Please enter a location.");
    }
  });

  // Use current location
  document.getElementById("geoBtn").addEventListener("click", () => {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(showPosition, () => {
        alert("Could not get your location.");
      });
    } else {
      alert("Geolocation not supported by this browser.");
    }
  });

  // Load report markers from PHP
  fetch("http://localhost:8080/WebWizards/get-reports.php")
    .then(response => response.json())
    .then(data => {
      console.log("Reports loaded:", data);
      data.forEach(report => {
        if (report.Location) {
          geocoder.geocode({ address: report.Location }, (results, status) => {
            console.log("Geocode status for", report.Location, ":", status);
            if (status === "OK" && results[0]) {
              const marker = new google.maps.Marker({
                map: map,
                position: results[0].geometry.location,
                title: report.Description || "No Description"
              });
              const infoWindow = new google.maps.InfoWindow({
                content: `
                  <div style="color: black;">
                    <strong>Description:</strong> ${report.Description || "N/A"}<br>
                    <strong>Status:</strong> ${report.Status || "Unknown"}
                  </div>
                `
              });
              marker.addListener("click", () => {
                infoWindow.open(map, marker);
              });
            } else {
              console.error("Geocode failed for:", report.Location, status);
            }
          });
        }
      });
    })
    .catch(error => console.error("Error fetching reports:", error));
}

function geocodeAddress(address) {
  geocoder.geocode({ address: address }, (results, status) => {
    if (status === "OK") {
      map.setCenter(results[0].geometry.location);
      new google.maps.Marker({
        map: map,
        position: results[0].geometry.location,
      });
    } else {
      alert("Geocode failed: " + status);
    }
  });
}

function showPosition(position) {
  const userLoc = {
    lat: position.coords.latitude,
    lng: position.coords.longitude,
  };
  map.setCenter(userLoc);
  map.setZoom(14);
  new google.maps.Marker({
    map: map,
    position: userLoc,
    title: "You are here!",
  });
}

document.getElementById('BackBtn').addEventListener('click', () => {
  window.location.href = 'premium-dashboard.html';
});
