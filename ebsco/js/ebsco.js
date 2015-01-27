/*
 * The EBSCO module javascript
 **/
(function ($) {
    $(document).ready(function () {

    //
    var updatePublishDateSlider = function () {
	var dates = $('#DT1').val().split("-");
        var from = parseInt(dates[0]);
        var min = 1600;

        if (!from || from < min) {
            from = min;
        }

        // and keep the max at 1 years from now
        var max = (new Date()).getFullYear() + 1;
        var to = dates[1] != undefined && dates[1] != "" && dates[1] >= from ? dates[1] : max;

        // update the slider with the new min/max/values
        $('#ebsco-advanced-search-sliderDT1').slider('option', {
            min: min, max: max, values: [from, to]
        });
    };


    /*
     * Self executing function
     **/
    var onLoad = function () {
        // EBSCO/Search : Expand limiters
        // $('._more_limiters').live('click', function (event) {
        //     $("#moreLimiters").hide();
        //     $("#limitersHidden").removeClass("offscreen");
        // });
        //
        // // Search : Collapse limiters
        // $('._less_limiters').live('click', function (event) {
        //     $("#moreLimiters").show();
        //     $("#limitersHidden").addClass("offscreen");
        // });

        // EBSCO/Search : Collapse / expand facets
        // $('.expandable').live('click', function (event) {
        //     var span = $(this).find('dt span'),
        //         id = $(this).attr('id').replace('facet-','');
        //     if (span.length > 0) {
        //         if (span.hasClass('collapsed')) {
        //             $('#narrowGroupHidden_' + id).show();
        //             span.removeClass('collapsed');
        //             span.addClass('expanded');
        //         } else if (span.hasClass('expanded')) {
        //             $('#narrowGroupHidden_' + id).hide();
        //             span.removeClass('expanded');
        //             span.addClass('collapsed');
        //         }
        //     } else if ($(this).attr('href')) {
        //         var dl = $(this).parents('dl'),
        //             id = dl.attr('id').replace('narrowGroupHidden_', ''),
        //             span = $('#facet-' + id).find('dt span');
        //         dl.hide();
        //         span.removeClass('expanded');
        //         span.addClass('collapsed');
        //     }
        // });

        // EBSCO/Search : Less facets
        // $('._less_facets').live('click', function (event) {
        //     var id = $(this).attr('id').replace('less-facets-','');
        //     var dl = $('#facet-' + id);
        //     dl.trigger('click');
        // });

        // Search : Ajax request the Record action
        // $('._record_link').live('click', function (event) {
        //     var element = $(this);
        //     var position = element.position();
        //     event.preventDefault();
        //     $('#spinner').show();
        //     $("#spinner").offset({left:event.pageX - 18,top:event.pageY - 18});
        //
        //     $.get(element.attr('href'), function (data) {
        //         $('#main').html(data);
        //         $('#spinner').hide();
        //     });
        // });

        // Advanced Search : Add a new search term
        $('#ebsco-advanced-search-form').on('click', '._add_row', function (event) {
            event.preventDefault();
            var newSearch = $('#advanced-row-template').html();
            var rows = $('._advanced-row');
            if (rows) {
                // Find the index of the next row
                var index = rows.length - 1; // one row is the template itself, so don't count it
                // Replace NN string with the index number
                newSearch = newSearch.replace(/NN/g, index);
                lastSearch = $('#edit-add-row');
                lastSearch.before(newSearch);
            }
        });

        // Advanced Search : Delete an advanced search row
        $('#ebsco-advanced-search-form').on('click', '._delete_row', function (event) {
            event.preventDefault();
            $(this).parents('._advanced-row').remove();
        });

        // Advanced Search : Reset the form fields to default values
        $('#ebsco-advanced-search-form').on('click', '.ebsco-advanced input[name="reset"]', function (event) {
            event.preventDefault();
            $('#ebsco-advanced-search-form').find('input, select').each(function (index) {
                var type = this.type;
                switch(type) {
                    case 'text':
                        $(this).val('');
                        break;
                    case 'checkbox':
                        $(this).attr('checked', false);
                        break;
                    case 'select-multiple':
                        $(this).children('option').each(function (index) {
                            $(this).attr('selected', false);
                        });
                        break;
                    case 'select-one':
                        $(this).children('option').each(function (index) {
                            $(this).attr('selected', false);
                        });
                        // for IE
                        $(this).children('option:first').attr('selected', 'selected');
                        break;
                    case 'radio':
                        $(this).attr('checked', false);
			var name = $(this).attr('name');
                        $('input[type="radio"][name="'+name+'"]').first().attr('checked', 'checked');
                        break;
                }
            });
        });

        // Auto submit the seelct boxes with '_jump_menu' class

        $('#ebsco-sort-form').on('change', '._jump_menu', function (event) {
            var name = $(this).attr('id').replace('ebsco-', ''),
                value = $(this).attr('value'),
                url = $('#ebsco-sort-form').attr('action');
            url += "&" + name + "=" + value;
            window.location.href = url;
        });

        // Retain search filters checkbox functionality
        // $('#edit-remember').live('click', function (event) {
        //     $("#ebsco-basic-search-form :input[type='checkbox'][name^='filter[']").attr('checked', $(this).attr('checked'));
        // });

        // Advanced Search : handle 'Date Published from' limiter
        // Create the UI slider (if slider function is defined)
        if(typeof $("#ebsco-advanced-search-sliderDT1").slider == 'function') {

            $('#ebsco-advanced-search-sliderDT1').slider({
                range: true,
                min: 0, max: 9999, values: [0, 9999],
                slide: function (event, ui) {
                    if(ui.values[0] == $(event.target).slider('option','min') && ui.values[1] == $(event.target).slider('option','max')) {
                        $('#ebsco-advanced-search-limiterDT1').val('');
			$('#DT1').val('');
                    } else {
                        $('#ebsco-advanced-search-limiterDT1').val('addlimiter(DT1:' + ui.values[0] + '-1/' + ui.values[1] + '-1)');
			$('#DT1').val(ui.values[0]+ '-' +ui.values[1]);
                    }
                }
            });

            // initialize the slider with the original values
            // in the text boxes
            updatePublishDateSlider();

            // when user enters values into the boxes
            // the slider needs to be updated too
            $('#DT1').change(function(){
                updatePublishDateSlider();
            });
        }
    }();


});
})(jQuery);
