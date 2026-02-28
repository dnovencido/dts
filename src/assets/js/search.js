const search_document = document.getElementById("search_document");

if(search_document) {
    search_document.addEventListener("submit", function(e) {
        const input = document.getElementById("search-input");
        const value = input.value.trim();

        // Prevent empty search
        if (value === "") {
            e.preventDefault();
            input.focus();
            return;
        }
    });
}