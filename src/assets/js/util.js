const btnDelete = document.getElementsByClassName("btn-delete");

for (let i=0; i<btnDelete.length; i++) {
    btnDelete[i].addEventListener("click", function(e) {
        e.preventDefault();
        var result = confirm("Do you want to delete this item?");
        if(result) {
            var btn = this;
            var id = this.getAttribute("data-id");
            var category = this.getAttribute("data-category");
            url = this.dataset.url;
            fetch(`${url}/${id}`, {
                method:'DELETE',
                headers: {
                    "Content-Type": "application/json",
                },
            }).then(function(response) {
                return response.json(); 
            }).then(function(data) {
                if(data.deleted) {
                    const row = btn.closest('tr');
                    if (row) {
                        row.remove();
                    } else {
                        window.location.href = `/delete_from_preview?category=${category}`;
                    }
                }
            }).catch(function(e) { 
                console.log(e)
            })        
        }
    });
}