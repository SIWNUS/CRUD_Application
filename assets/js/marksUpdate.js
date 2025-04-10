document.getElementById("markUpdateForm").addEventListener("submit", function(e){
    e.preventDefault();

    let formData = new FormData(this);

    fetch('/Suswin/api/update_marks.php', {
        method: 'post',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.info("response: ", data);
        if (data.success){
            alert(data.success);
            window.location.assign("/Suswin/pages/marksheet.php");
        } else {
            alert(data.error);
        }
    })
    .catch(error => {
        console.error("Error: ", error);
    })
})