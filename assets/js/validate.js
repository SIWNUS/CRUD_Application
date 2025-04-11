document.addEventListener("DOMContentLoaded", () => {
    // Data mappings for districts and cities
    const districtData = {
      "Tamil Nadu": ["Chennai", "Coimbatore", "Erode"],
      "Karnataka": ["Bengaluru", "Mysore", "Hosur"]
    };
    const cityData = {
      "Chennai": ["T. Nagar", "Velachery", "Anna Nagar", "Guindy"],
      "Coimbatore": ["RS Puram", "Gandhipuram", "Peelamedu", "Saibaba Colony"],
      "Erode": ["Perundurai", "Bhavani", "Gobichettipalayam", "Chennimalai"],
      "Bengaluru": ["Whitefield", "Koramangala", "Indiranagar", "Jayanagar"],
      "Mysore": ["Vijayanagar", "Saraswathipuram", "Nazarbad", "Jayalakshmipuram"],
      "Hosur": ["Mathigiri", "Bagalur", "Zuzuwadi", "Kelamangalam"]
    };
  
    // Populate the state dropdown
    const states = ["Tamil Nadu", "Karnataka"];
    const stateDrop = document.getElementById("state");
    states.forEach(s => {
      const opt = document.createElement("option");
      opt.value = s;
      opt.textContent = s;
      stateDrop.appendChild(opt);
    });
  
    // Get pre-selected values if in update/edit mode
    const selectedState = stateDrop.dataset.selected;
    const districtDrop = document.getElementById("districts");
    const selectedDistrict = districtDrop.dataset.selected;
    const cityDrop = document.getElementById("cities");
    const selectedCity = cityDrop.dataset.selected;
  
    // Populate district & city if a state is already selected (edit mode)
    if (selectedState) {
      stateDrop.value = selectedState;
  
      // Populate districts for the selected state
      const districts = districtData[selectedState] || [];
      districtDrop.innerHTML = '<option value="" disabled>Select your district</option>';
      districts.forEach(d => {
        const opt = document.createElement("option");
        opt.value = d;
        opt.textContent = d;
        if (d === selectedDistrict) {
          opt.selected = true;
        }
        districtDrop.appendChild(opt);
      });
  
      // Populate cities for the selected district
      const cities = cityData[selectedDistrict] || [];
      cityDrop.innerHTML = '<option value="" disabled>Select your city</option>';
      cities.forEach(c => {
        const opt = document.createElement("option");
        opt.value = c;
        opt.textContent = c;
        if (c === selectedCity) {
          opt.selected = true;
        }
        cityDrop.appendChild(opt);
      });
    }
  
    // When state changes, update district dropdown and reset city dropdown
    stateDrop.addEventListener("change", () => {
      const selected = stateDrop.value;
      const districts = districtData[selected] || [];
      districtDrop.innerHTML = '<option value="" disabled selected>Select your district</option>';
      districts.forEach(d => {
        const opt = document.createElement("option");
        opt.value = d;
        opt.textContent = d;
        districtDrop.appendChild(opt);
      });
      cityDrop.innerHTML = '<option value="" disabled selected>Select your city</option>';
    });
  
    // When district changes, update city dropdown
    districtDrop.addEventListener("change", () => {
      const selected = districtDrop.value;
      const cities = cityData[selected] || [];
      cityDrop.innerHTML = '<option value="" disabled selected>Select your city</option>';
      cities.forEach(c => {
        const opt = document.createElement("option");
        opt.value = c;
        opt.textContent = c;
        cityDrop.appendChild(opt);
      });
    });
  
    // Handle form submission regardless of whether it is an update or a new registration
    const form = document.getElementById("update_form") || document.getElementById("reg_form");
    if (form) {
      form.addEventListener("submit", function(e) {
        e.preventDefault();
        const formData = new FormData(form);
        
        // Change the API endpoint as needed. For updates, you might send to update_api.php,
        // and for new registrations, to register_api.php.
        // Below example uses update_api.php.
        fetch("/Suswin/api/validate.php", {
          method: "POST",
          body: formData
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
    }
  });
  