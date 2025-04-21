function toggleDetails(button) {
    const details = button.parentElement.nextElementSibling;
    if (details.style.display === "none" || details.style.display === "") {
        details.style.display = "block";
        button.textContent = "Hide Details";
    } else {
        details.style.display = "none";
        button.textContent = "View Details";
    }
}
