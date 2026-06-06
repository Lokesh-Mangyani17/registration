(function( $ ) {
	$(document).on('ajaxComplete', function(e) {
		$('body').find('.nubu_alternate_products select').select2({
			placeholder: 'Select an alternate product',
			allowClear: true,
		});
	});
	
	$('.nubu-color-field').wpColorPicker();

	let wrapper = $('#ingredients-wrapper');
    let index = wrapper.find('.ingredient-row').length;
    $(document).trigger('quant:recalc');

    function calculateTotalQuant() {
        let total = 0;

        wrapper.find('input[name$="[quant]"]').each(function () {
            let val = parseFloat($(this).val());
            if (!isNaN(val)) {
                total += val;
            }
        });

        return Math.round(total * 100) / 100;
    }

    function showQuantError(total) {
        let notice = $('#quant-error-notice');

        if (!notice.length) {
            notice = $(`
                <div id="quant-error-notice" class="notice notice-error inline">
                    <p><strong>Error:</strong> Please make sure the total of quantity values is 100. Total Now <span class="total-val"></span></p>
                </div>
            `);
            wrapper.before(notice);
        }

        notice.find('.total-val').text(total);
        notice.show();
    }

    function hideQuantError() {
        $('#quant-error-notice').hide();
    }

    const $toggle = $('input[name="show_terpene_chart"]');
    const $wrapper = $('#ingredients-wrapper');
    const $addBtn = $('#add-ingredient');

    $toggle.on('change', function () {
        if ($(this).is(':checked')) {
            $wrapper.slideDown();
            $addBtn.show();
        } else {
            $wrapper.slideUp();
            $addBtn.hide();
        }
    })

    $('#add-ingredient').on('click', function() {
        let row = `
            <div class="ingredient-row" style="margin-bottom:10px;">
                <label>Terpenes ${index + 1}:</label>
                <input type="text" name="terpenes[${index}][title]" placeholder="Title"
                       style="width:25%; margin-right:10px;" required>
                <input type="text" name="terpenes[${index}][description]" placeholder="Description"
                       style="width:25%; margin-right:10px;">
                <input type="color" name="terpenes[${index}][color]" style="width:7%; margin-right:10px;">
                <input type="number" step="0.1" name="terpenes[${index}][quant]" style="width:13%; margin-right:10px;" placeholder="Percentage" min="0.1" max="100" required>
                <button type="button" class="remove-ingredient button">Remove</button>
            </div>`;
        wrapper.find('#add-ingredient').before(row);
        index++;
    });

    $(document).on('click', '.remove-ingredient', function() {
        $(this).closest('.ingredient-row').remove();
        $(document).trigger('quant:recalc');
    });
    $(document).on('input', 'input[name$="[quant]"]', function () {
        $(document).trigger('quant:recalc');
    });
    $(document).on('quant:recalc', function () {
        let total = calculateTotalQuant();

        if ( Math.abs( total - 100 ) > 0.01 ) {
            showQuantError(total);
        } else {
            hideQuantError();
        }
    });
}( jQuery ));