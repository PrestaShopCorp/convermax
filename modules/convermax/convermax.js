//alert('asd');

var cm_facet_vals = {
    values: [],
    Change: function(checked, value) {
        value = value.split('|');
        //key = value[0];
        if(checked) {
            this.values.push({key: value[0], val: value[1]});
            //this.values[].({key: value[0], val: value[1]});
        } else {
            //delete this.values[value[0]];
            for (var i = 0; i < this.values.length; i++) {
                if(this.values[i]['key'] == value[0] && this.values[i]['val'] == value[1]) {
                    //delete this.values[i];
                    this.values.splice(i,1);
                }
            }
        }
    }
};

$(document).ready(function()
{
    $('#facets_block input[type=checkbox]').on('change', function()
    {
        //alert(this.checked);
        //reloadContent();
        //console.log(cm_facet_vals.values);

        cm_facet_vals.Change(this.checked, this.value);
        console.debug(cm_facet_vals.values);
    });
});

function cm_reload() {
    cm_stopAjaxQuery();
    cm_ajaxQuery = $.ajax(
        {
            type: 'GET',
            url: baseDir + 'modules/convermax/convermax-ajax.php',
            data: data+params_plus+n,
            dataType: 'json',
            success: function(result)
            {
                $('#layered_block_left').replaceWith(utf8_decode(result.filtersBlock));

                $('.category-product-count').html(result.categoryCount);

                if (result.productList)
                    $('#product_list').replaceWith(utf8_decode(result.productList));
                else
                    $('#product_list').html('');

                $('#product_list').css('opacity', '1');
                if ($.browser.msie) // Fix bug with IE8 and aliasing
                    $('#product_list').css('filter', '');

                if (result.pagination.search(/[^\s]/) >= 0) {
                    if ($(result.pagination).find('ul.pagination').length)
                    {
                        $('div#pagination').show();
                        $('ul.pagination').each(function () {
                            $(this).replaceWith($(result.pagination).find('ul.pagination'));
                        });
                    }
                    else if (!$('ul.pagination').length)
                    {
                        $('div#pagination').show();
                        $('div#pagination').each(function () {
                            $(this).html($(result.pagination));
                        });
                    }
                    else
                    {
                        $('ul.pagination').html('');
                        $('div#pagination').hide();
                    }
                }
                else
                {
                    $('ul.pagination').html('');
                    $('div#pagination').hide();
                }

                paginationButton();
                ajaxLoaderOn = 0;

                // On submiting nb items form, relaod with the good nb of items
                $('#pagination form').submit(function() {
                    val = $('#pagination #nb_item').val();
                    $('#pagination #nb_item').children().each(function(it, option) {
                        if (option.value == val)
                            $(option).attr('selected', true);
                        else
                            $(option).removeAttr('selected');
                    });
                    // Reload products and pagination
                    reloadContent();
                    return false;
                });
                if (typeof(ajaxCart) != "undefined")
                    ajaxCart.overrideButtonsInThePage();

                if (typeof(reloadProductComparison) == 'function')
                    reloadProductComparison();
                initSliders();

                // Currente page url
                if (typeof(current_friendly_url) == 'undefined')
                    current_friendly_url = '#';

                // Get all sliders value
                $(['price', 'weight']).each(function(it, sliderType)
                {
                    if ($('#layered_'+sliderType+'_slider').length)
                    {
                        // Check if slider is enable & if slider is used
                        if(typeof($('#layered_'+sliderType+'_slider').slider('values', 0)) != 'object')
                        {
                            if ($('#layered_'+sliderType+'_slider').slider('values', 0) != $('#layered_'+sliderType+'_slider').slider('option' , 'min')
                                || $('#layered_'+sliderType+'_slider').slider('values', 1) != $('#layered_'+sliderType+'_slider').slider('option' , 'max'))
                                current_friendly_url += '/'+sliderType+'-'+$('#layered_'+sliderType+'_slider').slider('values', 0)+'-'+$('#layered_'+sliderType+'_slider').slider('values', 1)
                        }
                    }
                    else if ($('#layered_'+sliderType+'_range_min').length)
                    {
                        current_friendly_url += '/'+sliderType+'-'+$('#layered_'+sliderType+'_range_min').val()+'-'+$('#layered_'+sliderType+'_range_max').val();
                    }
                });
                if (current_friendly_url == '#')
                    current_friendly_url = '#/';
                window.location = current_friendly_url;
                lockLocationChecking = true;

                if(slideUp)
                    $.scrollTo('#product_list', 400);
                updateProductUrl();

                $('.hide-action').each(function() {
                    hideFilterValueAction(this);
                });
            }
        });
    cm_ajaxQueries.push(cm_ajaxQuery);
}

function cm_stopAjaxQuery() {
    if (typeof(cm_ajaxQueries) == 'undefined')
        cm_ajaxQueries = new Array();
    for(i = 0; i < cm_ajaxQueries.length; i++) {
        if (typeof cm_ajaxQueries[i] != 'undefined')
            cm_ajaxQueries[i].abort();
    }
    cm_ajaxQueries = new Array();
}

function convermaxSearch(field_name, field_value, query) {
        stopInstantSearchQueries();
        instantSearchQuery = $.ajax({
            url: search_url + '?rand=' + new Date().getTime(),
            data: {
                instantSearch: 1,
                id_lang: id_lang,
                q: query,
                facets0field: field_name,
                facets0selection: field_value
            },
            dataType: 'html',
            type: 'POST',
            headers: { "cache-control": "no-cache" },
            async: true,
            cache: false,
            success: function(data){
                if($("#search_query_" + blocksearch_type).val().length > 0)
                {
                    tryToCloseInstantSearch();
                    $('#center_column').attr('id', 'old_center_column');
                    $('#old_center_column').after('<div id="center_column" class="' + $('#old_center_column').attr('class') + '">'+data+'</div>');
                    $('#old_center_column').hide();
                    // Button override
                    ajaxCart.overrideButtonsInThePage();
                    $("#instant_search_results a.close").click(function() {
                        $("#search_query_" + blocksearch_type).val('');
                        return tryToCloseInstantSearch();
                    });
                    return false;
                }
                else
                    tryToCloseInstantSearch();
            }
        });
        instantSearchQueries.push(instantSearchQuery);
}


function utf8_decode (utfstr) {
    var res = '';
    for (var i = 0; i < utfstr.length;) {
        var c = utfstr.charCodeAt(i);

        if (c < 128)
        {
            res += String.fromCharCode(c);
            i++;
        }
        else if((c > 191) && (c < 224))
        {
            var c1 = utfstr.charCodeAt(i+1);
            res += String.fromCharCode(((c & 31) << 6) | (c1 & 63));
            i += 2;
        }
        else
        {
            var c1 = utfstr.charCodeAt(i+1);
            var c2 = utfstr.charCodeAt(i+2);
            res += String.fromCharCode(((c & 15) << 12) | ((c1 & 63) << 6) | (c2 & 63));
            i += 3;
        }
    }
    return res;
}