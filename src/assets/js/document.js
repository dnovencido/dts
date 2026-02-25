$(function() {

    $('#document_type').select2( {
        placeholder: "--Select Document Type--",
        width: '100%'
    });

    $('#concerned_division').select2( {
        placeholder: "--Select Concerned Division--",
        theme: "classic",
        width: '100%',
        tags: true
    });

    $('#signatories').select2( {
        placeholder: "--Select Signatories--",
        theme: "classic",
        tags: true,
        width: '100%'
    });

    const select = $('#signatories');

    if(selectedValues && selectedValues.length > 0) {
        selectedValues.forEach(val => {
            // If option does not exist, create it
            if (select.find("option[value='" + val + "']").length === 0) {
                const option = new Option(val, val, true, true);
                select.append(option);
            }
            // Refresh Select2
            select.val(selectedValues).trigger('change');
        });
    }

    $('#stakeholders').select2( {
        placeholder: "--Select Stakeholders--",
        theme: "classic",
        multiple: true,
        tags: true,
        width: '100%'
    });

    $('#receiving_office').select2( {
        placeholder: "--Select Receiving Office--",
        width: '100%'
    });

    
    $('#concerned_division').on('change', function () {
        const divisionIds = $(this).val(); 
        $('#signatories').empty().trigger('change');
        if (!divisionIds || divisionIds.length === 0) return;
        $.ajax({
            url: '/get_stakeholders.php',
            type: 'POST',
            data: { division_ids: divisionIds }, // send array
            dataType: 'json',

            success: function (data) {
                const seen = {};
                const stakeholders = data.divisions || [];
                stakeholders.forEach(function (stakeholder) {
                    if (seen[stakeholder.id]) return;
                    seen[stakeholder.id] = true;
                    const option = new Option(
                        stakeholder.head,
                        stakeholder.id,
                        true,  // auto-select
                        true
                    );
                    $('#signatories').append(option);
                });
                $('#signatories').trigger('change');
            },
            error: function () {
                alert('Failed to load stakeholders.');
            }
        });
    });

    $('#filing_location').select2( {
        placeholder: "--Select Filing Location--",
        width: '100%'
    });

    const uploadContainer = document.getElementById('uploadContainer');
    const fileInput = document.getElementById('fileInput');
    const previewContainer = document.getElementById('previewContainer');
    const uploadPlaceholder = document.getElementById('uploadPlaceholder');
    uploadContainer.addEventListener('click', () => fileInput.click());

    fileInput.addEventListener('change', function () {
        const file = this.files[0];

        if (!file) return;

        previewContainer.innerHTML = '';
        previewContainer.classList.remove('d-none');
        uploadPlaceholder.classList.add('d-none');

        const fileType = file.type;

        // Image Preview
        if (fileType.startsWith('image/')) {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.className = 'img-fluid rounded mt-2';
            img.style.maxHeight = '250px';
            previewContainer.appendChild(img);
        }

        // PDF Preview
        else if (fileType === 'application/pdf') {
            const embed = document.createElement('embed');
            embed.src = URL.createObjectURL(file);
            embed.type = 'application/pdf';
            embed.width = '100%';
            embed.height = '400px';
            embed.className = 'mt-2 rounded';
            previewContainer.appendChild(embed);
        }

        // Other Files
        else {
            previewContainer.innerHTML =
                `<div class="alert alert-info mt-2">
                    <i class="fa-solid fa-file"></i> ${file.name}
                </div>`;
        }
    });


});