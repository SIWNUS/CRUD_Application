document.addEventListener("DOMContentLoaded", () => {
    deleteButtons = document.querySelectorAll(".delete");

    deleteButtons.forEach(function(button){
        button.addEventListener("click", function(){
            const id = button.getAttribute("data-id");
            console.info("id: ", id);

            if (confirm("Do you want to delete this user information? ")){
                fetch(`/Suswin/api/delete_marks.php?id=${encodeURIComponent(id)}`,{
                    method: "GET",
                    headers: {
                        "Content-Type": "x-www-form-urlencoded",
                    }
                })
                .then(response => response.json())
                .then(data => {
                    console.log(data);
                    if (data.success){
                        alert(data.success);
                        window.location.reload();
                    } else {
                        alert(data.error);
                    }
                })
                .catch(error => {
                    console.error("Error: ", error);
                })
            }
        });
    });
})