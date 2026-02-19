var view_modal_buttons = document.querySelectorAll('.view-modal');
for (var i = 0; i < view_modal_buttons.length; i++) {
  view_modal_buttons[i].addEventListener('click', function (e) {
    e.preventDefault();
    const url = this.dataset.url;

    // Show loading
    document.getElementById('modalContent').innerHTML = 'Loading...';

    fetch(url)
      .then(response => response.text())
      .then(data => {

        document.getElementById('modalContent').innerHTML = data;

        const pageTitle = document.getElementById('page-title');

        if (pageTitle) {
            document.getElementById('modal-title').textContent =
            pageTitle.textContent;
        }

        const modal = new bootstrap.Modal(
          document.getElementById('viewModal')
        );

        modal.show();

      })
      .catch(error => {
        console.error(error);
        document.getElementById('modalContent').innerHTML = 'Failed to load.';
      });
  });

}


