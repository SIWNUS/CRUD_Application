document.addEventListener("DOMContentLoaded", () => {
    const updateButtons = document.querySelectorAll(".update");

    updateButtons.forEach(function(button){
        button.addEventListener("click", function(){
            const id = button.getAttribute("data-id");
            console.info(id);
            window.location.assign(`marksheet.php?id=${encodeURIComponent(id)}`);
        })

    })
})