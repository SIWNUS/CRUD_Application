const states = ["Tamil Nadu", "Karnataka"];

const state_drop = document.getElementById("state");

states.forEach(state => {
    let option = document.createElement("option");
    option.setAttribute("value", state);
    
    let optionText = document.createTextNode(state);
    option.appendChild(optionText);

    state_drop.appendChild(option);
});

const district_data = {
    "Tamil Nadu": ["Chennai", "Coimbatore", "Erode"],
    "Karnataka": ["Bengaluru", "Mysore", "Hosur"]
};

const district_drop = document.getElementById("districts");

state_drop.addEventListener("change", function(){
    const state_value = this.value;
    const districts = district_data[state_value] || [];

    district_drop.innerHTML = '<option value="" disabled selected>Select your district</option>';

    districts.forEach(district => {
        let option = document.createElement("option");
        option.setAttribute("value", district);
        
        let optionText = document.createTextNode(district);
        option.appendChild(optionText);
    
        district_drop.appendChild(option);
    });
})

const city_data = {
    "Chennai": ["T. Nagar", "Velachery", "Anna Nagar", "Guindy"],
    "Coimbatore": ["RS Puram", "Gandhipuram", "Peelamedu", "Saibaba Colony"],
    "Erode": ["Perundurai", "Bhavani", "Gobichettipalayam", "Chennimalai"],
    "Bengaluru": ["Whitefield", "Koramangala", "Indiranagar", "Jayanagar"],
    "Mysore": ["Vijayanagar", "Saraswathipuram", "Nazarbad", "Jayalakshmipuram"],
    "Hosur": ["Mathigiri", "Bagalur", "Zuzuwadi", "Kelamangalam"]
};

const city_drop = document.getElementById("cities");

district_drop.addEventListener("change", function(){
    const district_value = this.value;
    const cities = city_data[district_value];

    console.info(cities);

    city_drop.innerHTML = '<option value="" disabled selected>Select your city</option>';

    cities.forEach(city => {
        console.info(city);
        let option = document.createElement("option");
        option.setAttribute("value", city);

        let optionText = document.createTextNode(city);
        option.appendChild(optionText);

        city_drop.appendChild(option);
    });
});



document.getElementById("reg_form").addEventListener("submit", function(e) {
    e.preventDefault();

    let formData = new FormData(this);

    for (let [key, value] of formData.entries()) {
        console.log(key + ": " + value);
    }    

    fetch("/Suswin/api/validate.php", {
        method: 'post',
        body: formData
    })
    .then(response=>response.json())
    .then(data => {
        console.log(data);
        if(data.success){
            alert(data.success);
            window.location.reload();
        } else {
            alert(data.error);
        }
    })
    .catch(error => {
        console.error("ERROR: ", error);
    })
});