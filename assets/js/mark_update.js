document.addEventListener("DOMContentLoaded", () => {
    const marksField = Array.from(document.getElementsByClassName("marks"));
    const totalField = document.getElementsByClassName("total")[0];

    function calculateTotal(){
        let total = 0;
        marksField.forEach(mark => {
            const value = mark.value.trim();
            const marks = value !== '' ? parseInt(value, 10) : 0;
            console.info("Parsed mark:", marks);
            if (!isNaN(marks)){
                total += marks;
                console.log("Total: ", total);
            } else {
                console.info("This is NaN:", marks);
            }
        });
        totalField.value = total;
    }

    marksField.forEach(mark => {
        mark.addEventListener("input", calculateTotal);    
    });
})

document.addEventListener("DOMContentLoaded", () => {
    const rows = document.querySelectorAll("tbody tr");
    const students = [];

    rows.forEach((row, index) => {
        const cells = row.querySelectorAll("td");
        const subjectMarks = [
            parseInt(cells[2].textContent.trim()), // English
            parseInt(cells[3].textContent.trim()), // Tamil
            parseInt(cells[4].textContent.trim()), // Maths
            parseInt(cells[5].textContent.trim()), // Science
            parseInt(cells[6].textContent.trim())  // Social
        ];

        let allAGrades = true;

        subjectMarks.forEach((mark, i) => {
            const gradeSpan = cells[i + 2].querySelector(".grade-subject");
            if (mark >= 40) {
                gradeSpan.textContent = " (A)";
                gradeSpan.style.color = "green";
            } else {
                gradeSpan.textContent = " (B)";
                gradeSpan.style.color = "red";
                allAGrades = false;
            }
        });
    });
});


document.getElementById('markForm').addEventListener("submit", function(e){
    e.preventDefault();

    let formData = new FormData(this);
    for (let [key, value] of formData.entries()) {
        console.log(`${key}: ${value}`);
    }
    

    fetch('/Suswin/api/marklist.php', {
        method: 'post',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
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
})