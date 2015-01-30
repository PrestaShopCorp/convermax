var cm_params = {
    facets: {},
    sliders: {},
    trees: {},
    facets_display: {},
    page: '',
    size: '',
    orderby: 'position',
    orderway: 'desc',
    SetFacet: function(checked, fieldname, value, displayname) {
        if(checked) {
            if(!this.facets[fieldname]) {
                this.facets[fieldname] = [];
            }
            this.facets[fieldname].push(value);
            this.facets_display[fieldname] = displayname;
        } else {
            for (var i = 0; i < this.facets[fieldname].length; i++) {
                if(this.facets[fieldname][i] == value) {
                    this.facets[fieldname].splice(i,1);
                }
            }
            if (this.facets[fieldname].length == 0) {
                delete this.facets[fieldname];
                delete this.facets_display[fieldname];
            }
        }
    },
    Get: function() {
        var data = '';
        for (var i = 0; i < this.facets.length; i++) {
            data += 'fld' + i + '=' + this.facets[i]['key'] + '&val' + i + '=' + this.facets[i]['val'] + (i == (this.facets.length - 1) ? '' : '&');
        }
        return data;
    },
    GetFacets: function(format) {
        var data = '';
        var keys = Object.keys(this.facets);
        for(var i = 0; i < keys.length; i++ ) {
            if (format == 'url') {
            }
            for(var j = 0; j < this.facets[keys[i]].length; j++ ) {
                if (j in this.facets[keys[i]]) {
                    if (format == 'url') {
                        data += (i == 0 ? '' : '&') + 'cm_select[' + encodeURIComponent(keys[i]) + '][]=' + encodeURIComponent(this.facets[keys[i]][j]) + (j == (this.facets[keys[i]].length - 1) ? '' : '&');
                    }
                    if (format == 'list') {
                        data += '<li><span onclick="cm_params.SetFacet(false, \'' + keys[i] + '\', \'' + this.facets[keys[i]][j] + '\');cm_reload();">[x]</span>' + this.facets_display[keys[i]] + ' - ' + this.facets[keys[i]][j] + '</li>';
                    }
                }
            }

        }
        //get sliders
        keys = Object.keys(this.sliders);
        for(var i = 0; i < keys.length; i++ ) {
                if (format == 'url') {
                    data += /*(i == 0 ? '' : '&') + */'&cm_select[' + encodeURIComponent(keys[i]) + '][]=' + encodeURIComponent(this.sliders[keys[i]][0]);
                }
                if (format == 'list') {
                    if (this.sliders[keys[i]][0] != this.sliders[keys[i]][1]) {
                    data += '<li><span onclick="cm_params.ResetSlider(\'' + keys[i] + '\', \'' + this.sliders[keys[i]][1] + '\');cm_reload();">[x]</span>' + keys[i] + ' - ' + this.sliders[keys[i]][0] + '</li>';
                    }
                }
        }

        return data;
    },
    ResetSlider: function(fieldname, value) {

        this.sliders[fieldname][0] = value;
    }
};
var ajaxLoaderOn = 0;



$(document).ready(function()
{

    if (typeof redirect_url != 'undefined' && redirect_url) {
        window.location = redirect_url;
    }

    $('#cm_facets').on('change', 'input[type=checkbox]', function()
    {
        cm_params.SetFacet(this.checked, this.dataset.fieldname, this.value, this.dataset.displayname);
        cm_params.page = 1;
        cm_reload();
    });

    $(document).off('change', 'select[name="n"]');
    $(document).on('change', 'select[name=n]', function(e) {
        $('select[name=n]').val($(this).val());
        cm_params.size = $(this).val();
        cm_params.page = 1;
        cm_reload();
    });

    $('#cm_related a').click(function(e)
    {
        e.preventDefault();
        cm_params.page = 1;
        cm_query = $(this).text();
        cm_reload();
    });



    $(document).off('change', '.selectProductSort');
    $(document).on('change', '.selectProductSort', function(e) {
        var splitData = $(this).val().split(':');

        cm_params.orderby = splitData[0];
        cm_params.orderway = splitData[1];
        cm_params.page = 1;

        console.log(cm_params.orderby);
        console.log(cm_params.orderway);

        cm_reload();
    });


    cm_initSliders();
    cm_initTrees();
    cm_paginationButton();
    cm_displayCurrentSearchBlock();

    //autocomplete part
    $("#search_query_" + blocksearch_type).unautocomplete();

    var width_ac_results = 	$("#search_query_" + blocksearch_type).parent('form').width();
    if (typeof ajaxsearch != 'undefined' && ajaxsearch && typeof blocksearch_type !== 'undefined' && blocksearch_type)
        $("#search_query_" + blocksearch_type).autocomplete(
            search_url,
            {
                minChars: 3,
                max: 10,
                width: (width_ac_results > 0 ? width_ac_results : 500),
                selectFirst: false,
                scroll: false,
                dataType: "json",
                formatItem: function(data, i, max, value, term) {
                    if (value == 'freetext') {
                        return '<div class="autocomplete-item">' + data.Text + '</div>';
                    }
                    if (value == 'product') {
                        return '<div class="autocomplete-item"><img src="' + data.img_link + '"><div class="autocomplete-desc">' + data.description_short + '</div></div>';
                    }
                    if (value == 'category') {
                        return '<div class="autocomplete-item">' + data.FacetValue + '</div>';
                    }
                    if (value == 'group') {
                        return '<div class="autocomplete-group">' + data + '</div>';
                    }
                },
                parse: function(data) {
                    var mytab = new Array();
                    var displayproduct = true;
                    var displaycat = true;
                    for (var i = 0; i < data.length; i++) {
                        if (data[i].Type == 'product' && displayproduct) {
                        mytab[mytab.length] = { data: 'Product Search:', value: 'group' };
                        displayproduct = false;
                    }
                    if (data[i].Type == 'category' && displaycat) {
                        mytab[mytab.length] = { data: 'Category Search:', value: 'group' };
                        displaycat = false;
                    }
                    mytab[mytab.length] = {data: data[i], value: data[i].Type};
                }
                    return mytab;
                },
                extraParams: {
                    ajaxSearch: 1,
                    id_lang: id_lang
                }
            }
        )
            .result(function(event, data, formatted) {
                if (data.Type == 'freetext') {
                    document.location.href = search_url + ((search_url.indexOf('?') < 0) ? '?' : '&') + 'search_query=' + data.Text;
                }
                if (data.Type == 'product') {
                    document.location.href = data.link;
                }
                if (data.Type == 'category') {
                    cm_params.SetFacet(true, data.FieldName, data.FacetValue);
                    document.location.href = search_url + ((search_url.indexOf('?') < 0) ? '?' : '&') + cm_params.GetFacets('url');
                }
            });


});

function cm_initSliders() {
    $('.cm_slider').each(function() {
        cm_params.sliders[this.dataset.fieldname][1] = this.dataset.range;
        var r = /\[(.*) TO (.*)\]/i;
        var v = r.exec(this.dataset.range);
        var s = r.exec(cm_params.sliders[this.dataset.fieldname][0]);
        var min = parseFloat(v[1]);
        var max = parseFloat(v[2]);
        var selectionmin = parseFloat(s[1]);
        var selectionmax = parseFloat(s[2]);
        var sliderinfo = $("<div>").addClass("cm_sliderinfo").html('<p>Selected: ' + selectionmin + ' to ' + selectionmax + '</p>').insertBefore($(this));
        $(this).slider({
            range: true,
            min: min,
            max: max,
            values: [selectionmin, selectionmax],
            slide: function (e, ui) {
                sliderinfo.html('<p>Selected: ' + ui.values[0] + ' to ' + ui.values[1] + '</p>');
            },
            change: function (e, ui) {
                var newselection = '[' + ui.values[0] + ' TO ' + ui.values[1] +']';
                cm_params.sliders[this.dataset.fieldname][0] = newselection;
                cm_params.page = 1;
                cm_reload();
            }
        });
    });

}

function cm_initTrees()
{
    $('.cm_tree_item').click(function(e)
    {
        e.preventDefault();
        cm_params.page = 1;
        cm_params.SetFacet(true, this.dataset.fieldname, this.dataset.value, this.dataset.displayname);
        cm_reload();
    });
}

function cm_displayCurrentSearchBlock() {
    var facets = cm_params.GetFacets('list');
    if (facets) {
        var data;
        data = '<b>Current Search</b>';
        data += '<ul>';
        if (cm_query.length > 0)
            data += '<li><span onclick="cm_clearQuery()">[x]</span>Query - ' + cm_query + '</li>';
        data += facets;
        data += '</ul>';
        data += '<div class="clear-all" onclick="cm_clearCurrentSearchBlock()">Clear all</div>';

        $('#cm_selected_facets').replaceWith('<div id="cm_selected_facets" class="list-block">' + data + '</div>');
    } else {
        $('#cm_selected_facets').replaceWith('<div id="cm_selected_facets" class="list-block"> </div>');
    }
}

function cm_clearCurrentSearchBlock() {
    cm_params.facets = {};
    var keys = Object.keys(cm_params.sliders);
    for(var i = 0; i < keys.length; i++ ) {
    cm_params.ResetSlider(keys[i], cm_params.sliders[keys[i]][1]);
    }
    cm_reload();
}

function cm_clearQuery()
{
    cm_query='';
    $('#search_query_top').val('');
    cm_reload();
}


jQuery.fn.center = function () {
    this.css("position","fixed");
    this.css("top", Math.max(0, ((this.parent().height() - $(this).outerHeight()) / 2) +
    this.parent().scrollTop()) + "px");
    this.css("left", Math.max(0, (($(this.parent()).width() - $(this).outerWidth()) / 2) +
    this.parent().scrollLeft()) + "px");
    return this;
}

function cm_reload(params) {
    if (!ajaxLoaderOn)
    {
        $('#center_column').prepend($('#cm_ajax_container').html());
        $('#center_column').css('opacity', '0.7');
        ajaxLoaderOn = 1;
    }
    var request = cm_params.GetFacets('url') + '&p=' + cm_params.page + '&n=' + cm_params.size + '&search_query=' + cm_query;
    if (cm_params.orderby) {
        request += '&orderby=' + encodeURIComponent(cm_params.orderby) + '&orderway=' + cm_params.orderway;
    }

    var loc = search_url + ((search_url.indexOf('?') < 0) ? '?' : '&') + request;

    cm_ajaxQuery = $.ajax(
        {
            type: 'GET',
            url: baseDir + 'modules/convermax/convermax-ajax.php',
            data: request,
            dataType: 'json',
            success: function(result)
            {
                if (typeof result.redirect_url != 'undefined' && result.redirect_url) {
                    window.location = result.redirect_url;
                    return;
                }
                cm_displayCurrentSearchBlock();
                $('#facets_block').replaceWith('<div id="facets_block">' + result.facets + '</div>');
                $('#center_column').attr('id', 'old_center_column');
                $('#old_center_column').replaceWith('<div id="center_column" class="' + $('#old_center_column').attr('class') + '">'+result.productList+'</div>');
                $('#old_center_column').hide();


                $('#center_column').attr('id', 'old_center_column');
                $('#old_center_column').replaceWith('<div id="center_column" class="' + $('#old_center_column').attr('class') + '">'+result.productList+'</div>');
                $('#old_center_column').hide();

                cm_paginationButton();
                cm_initSliders();
                cm_initTrees();
                initUniform();
                ajaxLoaderOn = 0;

                $('div.pagination form').on('submit', function(e)
                {
                    e.preventDefault();
                    val = $('div.pagination select[name=n]').val();

                    $('div.pagination select[name=n]').children().each(function(it, option) {
                        if (option.value == val)
                            $(option).attr('selected', true);
                        else
                            $(option).removeAttr('selected');
                    });
                    cm_reload();
                });

                if (display instanceof Function) {
                    var view = $.totalStorage('display');
                        display(view);
                }

                history.pushState(null, '', loc);
            },
            error: function (r) {
                //alert(r.responseText);
            }
        });
}


function cm_paginationButton() {
    $('.pagination a').not(':hidden').each(function () {
        if ($(this).attr('href').search(/[&|\?]p=/) == -1)
            var page = 1;
        else
            var page = $(this).attr('href').replace(/^.*[&|\?]p=(\d+).*$/, '$1');
    });
    $('.pagination li').not('.current, .disabled').each(function () {
        var nbPage = 0;
        if ($(this).attr('class') == 'pagination_next')
            nbPage = parseInt($('.pagination li.current').children().children().html())+ 1;
        else if ($(this).attr('class') == 'pagination_previous')
            nbPage = parseInt($('.pagination li.current').children().children().html())- 1;

        $(this).children().children().on('click', function(e) {
            e.preventDefault();
            if (nbPage == 0)
                p = parseInt($(this).html()) + parseInt(nbPage);
            else
                p = nbPage;
            cm_params.page = p;
            cm_reload();
            nbPage = 0;
            return false;
        });
    });
}


function initUniform()
{
    $("#cm_facets input[type='checkbox'], select.form-control").uniform();
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