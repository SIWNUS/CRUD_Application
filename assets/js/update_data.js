document.addEventListener("DOMContentLoaded", () => {
    // Data mappings
    const district_data = {
      "Tamil Nadu": ["Chennai", "Coimbatore", "Erode"],
      "Karnataka": ["Bengaluru", "Mysore", "Hosur"]
    };
    const city_data = {
      "Chennai": ["T. Nagar", "Velachery", "Anna Nagar", "Guindy"],
      "Coimbatore": ["RS Puram", "Gandhipuram", "Peelamedu", "Saibaba Colony"],
      "Erode": ["Perundurai", "Bhavani", "Gobichettipalayam", "Chennimalai"],
      "Bengaluru": ["Whitefield", "Koramangala", "Indiranagar", "Jayanagar"],
      "Mysore": ["Vijayanagar", "Saraswathipuram", "Nazarbad", "Jayalakshmipuram"],
      "Hosur": ["Mathigiri", "Bagalur", "Zuzuwadi", "Kelamangalam"]
    };
  
    // Populate states
    const states = ["Tamil Nadu", "Karnataka"];
    const state_drop = document.getElementById("state");
    states.forEach(s => {
      const opt = document.createElement("option");
      opt.value = s;
      opt.textContent = s;
      state_drop.appendChild(opt);
    });
  
    // Pre‑select and populate district & city if editing
    const selectedState    = state_drop.dataset.selected;
    const district_drop    = document.getElementById("districts");
    const selectedDistrict = district_drop.dataset.selected;
    const city_drop        = document.getElementById("cities");
    const selectedCity     = city_drop.dataset.selected;
  
    if (selectedState) {
      state_drop.value = selectedState;
  
      // Populate districts for that state
      const districts = district_data[selectedState] || [];
      district_drop.innerHTML = '<option value="" disabled>Select your district</option>';
      districts.forEach(d => {
        const opt = document.createElement("option");
        opt.value = d;
        opt.textContent = d;
        if (d === selectedDistrict) opt.selected = true;
        district_drop.appendChild(opt);
      });
  
      // Populate cities for that district
      const cities = city_data[selectedDistrict] || [];
      city_drop.innerHTML = '<option value="" disabled>Select your city</option>';
      cities.forEach(c => {
        const opt = document.createElement("option");
        opt.value = c;
        opt.textContent = c;
        if (c === selectedCity) opt.selected = true;
        city_drop.appendChild(opt);
      });
    }
  
    // When state changes, reload districts & reset city
    state_drop.addEventListener("change", () => {
      const s = state_drop.value;
      const districts = district_data[s] || [];
      district_drop.innerHTML = '<option value="" disabled selected>Select your district</option>';
      districts.forEach(d => {
        const opt = document.createElement("option");
        opt.value = d;
        opt.textContent = d;
        district_drop.appendChild(opt);
      });
      city_drop.innerHTML = '<option value="" disabled selected>Select your city</option>';
    });
  
    // When district changes, reload cities
    district_drop.addEventListener("change", () => {
      const d = district_drop.value;
      const cities = city_data[d] || [];
      city_drop.innerHTML = '<option value="" disabled selected>Select your city</option>';
      cities.forEach(c => {
        const opt = document.createElement("option");
        opt.value = c;
        opt.textContent = c;
        city_drop.appendChild(opt);
      });
    });
  
    // Handle form submission
    document.getElementById("update_form").addEventListener("submit", function(e) {
      e.preventDefault();
      const formdata = new FormData(this);
      fetch("/Suswin/api/update_api.php", {
        method: "POST",
        body: formdata
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          alert(data.success);
          window.location.assign("/Suswin");
        } else {
          alert(data.error);
        }
      })
      .catch(err => console.error("Error:", err));
    });
  });
  