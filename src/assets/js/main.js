console.log('hello world');
$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    setTimeout(function() {
        $('.loader-wrapper').addClass('loaded');
        $('html').addClass('loaded');
    }, 1000);

    $('.dropdown-trigger').click(function() {
        $(this).parent().toggleClass('show');
    })
    setTimeout(()=>{
        $('.session-popup').addClass('animate');
        setTimeout(()=>{
            $('.session-popup').fadeOut();
        },4000);
    },1500);
    

    $('.custom-popup,.close-popup').on('click', (e) => {
        e.stopPropagation();
        $('.custom-popup').fadeOut();
    })

    $('.custom-popup .white-card').on('click', (e) => {
        e.stopPropagation();
    })
    $('.filter-wrapper').on('click', () => {
        $('.filter-popup').fadeIn();
    })


    $('.admin-menu-item.with-children').on('click', function () {
        $(this).parent().find('.children').slideToggle();
    });

    // Function to slugify text
    function slugify(text) {
        return text
            .toString() // Ensure it's a string
            .toLowerCase() // Convert to lowercase
            .trim() // Remove leading/trailing spaces
            .replace(/[^a-z0-9\s-]/g, '') // Remove non-alphanumeric characters
            .replace(/\s+/g, '-') // Replace spaces with hyphens
            .replace(/-+/g, '-'); // Replace multiple hyphens with a single hyphen
    }

    // Event listener to update the slug
    $('input[name="slug"]').on('input', function () {
        const titleText = $(this).val(); // Get the input value
        const slug = slugify(titleText); // Generate the slug
        $(this).val(slug); // Set the slug in the slug input
    });

    $('[data-slug-origin]').each(function () {
        const rootThis = $(this);
        const origin_input_name = rootThis.data('slug-origin');
        const origin_input = $('input[name="' + origin_input_name + '"]');
        origin_input.on('keyup', function () {
            rootThis.val(slugify(origin_input.val()));
        });
    });

    $('.check-all-checkboxes input').on('click', function () {
        let isChecked = $(this).is(':checked');
        $('.checkbox-delete-container input')
            .prop('checked', false) // Ensure all are unchecked first
            .prop('checked', isChecked) // Set to checked if "Check All" is checked
            .trigger('change'); // Trigger change event
    });

    $('.sortable .sortable-row').each(function (i) {
        $(this).find('[name*="pos"]').val(i + 1);
    });

    $('.sortable').sortable({
        update: function (event, ui) {
            $('.sortable .sortable-row').each(function (i) {
                $(this).find('[name*="pos"]').val(i + 1);
            });
        },
    });
    function toggleElementVisibility(button, targetSelector) {
        let input = button.find('input');
        let isHidden = input.val() === 'true';

        input.val(isHidden ? '' : 'true');
        button.find('.btn').text(isHidden ? 'Remove' : 'Undo');
        button.closest('.form-input-container').find(targetSelector).slideToggle();
    }

    $('.remove-current-image, .remove-current-file').on('click', function () {
        let target = $(this).hasClass('remove-current-image') ? '.img-container' : '.file-input-container';
        toggleElementVisibility($(this), target);
    });

    $('.select-multiple-custom').on('change', function () {
        var select = $(this);
        var data = select.select2('data');
        if (data.length) {
            for (let i = 0; i < data.length; i++) {
                var value = data[i].id;
                if (value && !select.closest('.select-multiple-custom-container').find('.selected-options input[type="hidden"][value="' + value + '"]').length) {
                    var optionHtml = '<div class="selected-option">';
                    optionHtml += '<input type="hidden" name="' + select.data('name') + '[]" value="' + value + '" class="selected-option-id">';
                    optionHtml += '<input type="hidden" name="pos[' + select.data('name') + '][' + value + ']" value="">';
                    optionHtml += '</div>';
                    select.closest('.select-multiple-custom-container').find('.selected-options').append(optionHtml);
                }
            }
        }
    });

    $('.time-picker .change-time-container span').on('click', function () {
        let container = $(this).closest('.change-time-container');
        let timeFormContainer = container.closest('.time-picker').find('.time-form-container');
        let hourInput = timeFormContainer.find('input:nth-child(1)');
        let minInput = timeFormContainer.find('input:nth-child(2)');
        let periodInput = timeFormContainer.find('input:nth-child(3)');
        const index = $(this).index();
        if (index === 0) {
            updateTime(hourInput, container.hasClass('upper'), 1, 12);
        } else if (index === 1) {
            updateTime(minInput, container.hasClass('upper'), 0, 59);
        } else {
            periodInput.val(periodInput.val() === 'AM' ? 'PM' : 'AM');
        }
        timeFormContainer.find('input[type="hidden"]').val(`${hourInput.val()}:${minInput.val()} ${periodInput.val()}`);
    });

    function updateTime(input, increase, min, max) {
        let value = +input.val();
        value = increase ? value + 1 : value - 1;
        if (value > max) value = min;
        if (value < min) value = max;
        input.val(value.toString().padStart(2, '0'));
    }
    let timeout, interval;
    $('.time-picker .change-time-container span')
        .on('mousedown', function () {
            timeout = setTimeout(() => {
                interval = setInterval(() => $(this).trigger('click'), 50);
            }, 500);
        })
        .on('mouseup mouseleave', function () {
            clearTimeout(timeout);
            clearInterval(interval);
        });

    $('.date-picker').datepicker({
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        changeYear: true,
    });

    $('.select-multiple-custom').on("select2:unselecting", function (e) {
        let wrapper = $(this).closest('.select-multiple-custom-container');
        wrapper.find(`.selected-options .selected-option-id[value="${e.params.args.data.id}"]`)
            .closest('.selected-option')
            .remove();
    });

    $(document).on('click', '.selected-option .fa-remove', function () {
        let selectedOptionDisplay = $(this).closest('.selected-option');
        let selectedOptionId = selectedOptionDisplay.find('.selected-option-id').val();
        let select = $(this).closest('.select-multiple-custom-container').find('select');
        let updatedValues = (select.val() || []).filter(value => value !== selectedOptionId);
        select.val(updatedValues).change();
        selectedOptionDisplay.remove();
    });

    $('.form-buttons-container').each(function () {
        $('.btn-draft').on('click', function (event) {
            event.preventDefault();
            $('#isPublished').val('0');
            $('#post-type-form').submit();
        });
    });

    let idsToDelArr = [];
    $(document).on('change', '.checkbox-delete-container input', function () {
        let value = $(this).val();
        if ($(this).is(':checked')) {
            if (!idsToDelArr.includes(value)) {
                idsToDelArr.push(value);
            }
        } else {
            idsToDelArr = idsToDelArr.filter(id => id !== value);
        }
    });

    $('form.bulk-delete').on('submit', function () {
        if (idsToDelArr.length > 0) {
            let ids = idsToDelArr.join(',');
            $(this).attr('action', `${$(this).attr('action')}/${ids}`);
        }
        return true;
    });

    $(document).on('change', '.custom-file-wrapper input', function (e) {
        let fileWrapper = $(this).closest('.custom-file-wrapper');
        let fileNames = Array.from(e.target.files).map(file => file.name).join(', ');

        if (fileNames) {
            fileWrapper.attr('data-text', fileNames).removeClass('placeholder');
        } else {
            fileWrapper.attr('data-text', fileWrapper.attr('data-placeholder')).addClass('placeholder');
        }
    });

    $('[id^="ckeditor_"]').each(function () {
        CKEDITOR.replace(this.id, {
            versionCheck: false,
            height: 400,
            extraPlugins: 'format,embed,autoembed,maximize,blockquote,justify,bidi',
            embed_provider: '//ckeditor.iframe.ly/api/oembed?url={url}&callback={callback}',
            format_tags: 'p;h1;h2;h3;h4;h5;h6',
            removeButtons: 'Cut,Copy,Paste,PasteText,PasteFromWord,Styles',

        });
    });

    $('select.w-auto').select2();
    $('select.w-100').select2({ width: '100%' });
    $('.custom-select').on('click', function () {
        $(this).find('.items').slideToggle();
    });

    $('.custom-select li').on('click', function () {
        $(this).closest('.custom-select').find('.display').val($(this).text());
        $(this).closest('.custom-select').find('.value').val($(this).attr('id'));
        $(this).parent('.items').slideToggle();
    });

    $('.dropdown-trigger').on('click', function () {
        $(this).parent().find('.custom-dropdown-wrapper-items').slideToggle();
    });

    $('.datatable-table').each(function () {
        const table = $(this);
        let params = new URLSearchParams(window.location.search);
        let value = params.get('per_page');
        var options = {
            pageLength: value ? value : 25,
        };
        if (!table.hasClass('no-export')) {
            options.buttons = [
                'csv',
                "pdfHtml5"
            ];
        }
        table.DataTable(options);
    });

    $('.server-side-showing-nbr select').on('change', function () {
        $(this).closest('form').submit();
    });

    $('.multiple-images-container').on('click', '.delete-btn', function () {
        const container = $(this).closest('.multiple-images-container');
        const toDeleteImage = $(this).data('image');
        let currentImages = JSON.parse(container.find('.current-multiple-images-value').val() || '[]');
        
        // Filter out the image to delete
        const filteredImages = currentImages.filter(image => image !== toDeleteImage);
        container.find('.current-multiple-images-value').val(JSON.stringify(filteredImages));
        
        // Hide the parent element of the delete button
        $(this).parent().hide();
    });
    
    $('.images-sortable').sortable({
        update: function () {
            const updatedArr = $(this).find('.single-multiple-image').map(function () {
                return $(this).data('image');
            }).get();
            
            $(this).closest('.multiple-images-container').find('.current-multiple-images-value').val(JSON.stringify(updatedArr));
        }
    });


});