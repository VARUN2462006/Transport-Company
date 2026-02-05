document.addEventListener("DOMContentLoaded", () => {
    const toggleButton = document.querySelector(".toggle");
    const subMenu = document.querySelector(".sub-menu");

    // Safety Check: Only run if both elements actually exist
    if (toggleButton && subMenu) {
        
        // 1. Initially hide submenu
        // (Note: It is better to set display: none in your CSS file to prevent "flashing", but this works too)
        subMenu.style.display = "none";

        // 2. Toggle submenu on button click
        toggleButton.addEventListener("click", (e) => {
            e.stopPropagation(); // Prevents the document click listener from firing immediately
            
            if (subMenu.style.display === "flex") {
                subMenu.style.display = "none";
            } else {
                subMenu.style.display = "flex";
            }
        });

        // 3. Close submenu if clicking anywhere else on the document
        document.addEventListener("click", (e) => {
            // Check if the clicked target is NOT inside the submenu
            if (!subMenu.contains(e.target)) {
                subMenu.style.display = "none";
            }
        });
    } else {
        console.error("Error: Could not find '.toggle' or '.sub-menu' in the HTML.");
    }
});