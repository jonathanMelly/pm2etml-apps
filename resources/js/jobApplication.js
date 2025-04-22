document.addEventListener("DOMContentLoaded", function () {
    // Add a click event listener to table items which have something in it
    const elements = document.querySelectorAll("[data-application_id]");
    elements.forEach(function (element) {
        element.addEventListener("click", function (e) {
            spnJobTitle.innerText = e.target.dataset.application_job;
            spnJobApplicant.innerText = e.target.dataset.application_applicant;
            inpApplicationId.value = e.target.dataset.application_id;
            overlay.style.display = "block";
            popup.style.display = "block";
        });
        element.style.cursor = "pointer";
    });
    overlay.addEventListener("click", () => {
        overlay.style.display = "none";
        popup.style.display = "none";
    });
});
