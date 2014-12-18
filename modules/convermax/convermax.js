//alert('asd');
var cm_params = {
    facets: {},
    page: '',//1,//cm_page,
    size: '',//cm_size,
    //sort: '',
    //sort_desc: false,
    orderby: 'position',
    orderway: 'desc',
    //query: cm_query,
    Change: function(checked, fieldname, value) {
        //value = value.split('|');
        //key = value[0];
        if(checked) {
            //working code:
            //this.facets.push({key: fieldname, val: value});
            if(!this.facets[fieldname]) {
                //this.facets.push(fieldname);
                this.facets[fieldname] = [];
            }
            this.facets[fieldname].push(value);
            //this.facets[].({key: value[0], val: value[1]});
        } else {
            //working code:
            /*for (var i = 0; i < this.facets.length; i++) {
                if(this.facets[i]['key'] == fieldname && this.facets[i]['val'] == value) {
                    //delete this.facets[i];
                    this.facets.splice(i,1);
                }
            }*/
            for (var i = 0; i < this.facets[fieldname].length; i++) {
                if(this.facets[fieldname][i] == value) {
                    this.facets[fieldname].splice(i,1);
                }
            }
            if (this.facets[fieldname].length == 0) {
                //this.facets.splice(this.facets.indexOf(fieldname), 1);
                delete this.facets[fieldname];
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
    GetFacets_old_working: function(format) {
        var data = '';
        var keys = Object.keys(this.facets);
        for(var i = 0; i < keys.length; i++ ) {
            //console.log(this.facets[keys[i]]);
            if (format == 'url') {
                data += '&facet.' + i + '.field=' + encodeURIComponent(keys[i]);
            }
            for(var j = 0; j < this.facets[keys[i]].length; j++ ) {
                if (format == 'url') {
                    data += '&facet.' + i + '.selection=' + encodeURIComponent(this.facets[keys[i]][j]);
                }
                if (format == 'list') {
                    data += '<li>' + keys[i] + ' - ' + this.facets[keys[i]][j] + '<span onclick="cm_params.Change(false, \'' + keys[i] + '\', \'' + this.facets[keys[i]][j] + '\');cm_reload()">[x]</span></li>';
                }
            }

        }
        return data;
    },
    GetFacets: function(format) {
        var data = '';
        var keys = Object.keys(this.facets);
        for(var i = 0; i < keys.length; i++ ) {
            //console.log(this.facets[keys[i]]);
            if (format == 'url') {
                //data += '&' + encodeURIComponent(keys[i]) + '[]=';
            }
            for(var j = 0; j < this.facets[keys[i]].length; j++ ) {
                if (format == 'url') {
                    //data += '&facet.' + i + '.selection=' + encodeURIComponent(this.facets[keys[i]][j]);
                    data += (i == 0 ? '' : '&') + 'cm_select[' + encodeURIComponent(keys[i]) + '][]=' + encodeURIComponent(this.facets[keys[i]][j]);
                }
                if (format == 'list') {
                    data += '<li>' + keys[i] + ' - ' + this.facets[keys[i]][j] + '<span onclick="cm_params.Change(false, \'' + keys[i] + '\', \'' + this.facets[keys[i]][j] + '\');cm_reload();">[x]</span></li>';
                }
            }

        }
        return data;
    }
};
var ajaxLoaderOn = 0;
//cm_params.facets.push({key: fieldname, val: value});



$(document).ready(function()
{
    //$('#cm_selected_facets').replaceWith('<div id="cm_selected_facets">asd<ul>' + cm_params.GetFacets('list') + '</ul></div>');

    //cm_params.size = cm_size;
    //alert(cm_size);

    $('#cm_facets').on('change', 'input[type=checkbox]', function()
    {
        //alert(this.checked);
        //reloadContent();
        //console.log(cm_params.facets);

        cm_params.Change(this.checked, this.dataset.fieldname, this.value);
        //console.log(cm_params.facets);
        cm_params.page = 1;
        cm_reload();
    });
    /*if (window.location.href.split('#').length == 2 && window.location.href.split('#')[1] != '')
    {
        var params = window.location.href.split('#')[1];
        //alert(params);
        cm_reload();
    }*/

    //$('select[name=n]').off('change')
    //$('.nbrItemPage').off('change').on('change', 'select[name=n]', function(e)
    //$('select[name=n]').on('change', 'select[name=n]', function(e)
    //$('select[name=n]').off('change').on('change', function(event) {
    //$(document).off('change').on('change', 'select[name=n]', function(e) {
    //$('select[name=n]').off('change');
    $(document).off('change', 'select[name="n"]');
    //$('select[name=n]').on('change', function(e) {
    $(document).on('change', 'select[name=n]', function(e) {
        //alert('name=n');
        $('select[name=n]').val($(this).val());
        cm_params.size = $(this).val();
        cm_params.page = 1;
        cm_reload();
    });


    //$('.productsSortForm').off('change').on('change', '.selectProductSort', function(e){
    //$('.selectProductSort').off('change').on('change', function(event) {
    //$('.selectProductSort').off('change');
    //$(document).off('change', '.selectProductSort');
    //$('.selectProductSort').unbind('change').bind('change', function(event) {
    //$(document).off('change').on('change', '.selectProductSort', function(e) {
    $(document).off('change', '.selectProductSort');
    $(document).on('change', '.selectProductSort', function(e) {
        //alert('selectProductSort');
        /*
        if (typeof request != 'undefined' && request)
            var requestSortProducts = request;
        var splitData = $(this).val().split(':');
        if (typeof requestSortProducts != 'undefined' && requestSortProducts)
            document.location.href = requestSortProducts + ((requestSortProducts.indexOf('?') < 0) ? '?' : '&') + 'orderby=' + splitData[0] + '&orderway=' + splitData[1];
        */
        var splitData = $(this).val().split(':');
        /*
        if (splitData[0] != 'position') {
            cm_params.orderby = splitData[0];
            if (splitData[1] = 'desc') {
                cm_params.sort_desc = true;
            }
        }*/

        cm_params.orderby = splitData[0];
        cm_params.orderway = splitData[1];
        cm_params.page = 1;

        console.log(cm_params.orderby);
        console.log(cm_params.orderway);

        cm_reload();
    });


    cm_paginationButton();
    //cm_init();
    cm_displayCurrentSearchBlock();

    //autocomplete part

    $("#search_query_" + blocksearch_type).unautocomplete();

    if (typeof ajaxsearch != 'undefined' && ajaxsearch && typeof blocksearch_type !== 'undefined' && blocksearch_type)
        $("#search_query_" + blocksearch_type).autocomplete(
            cm_autocomplete_url,
            {
                minChars: 3,
                max: 10,
                width: (width_ac_results > 0 ? width_ac_results : 500),
                selectFirst: false,
                scroll: false,
                dataType: "json",
                formatItem: function(data, i, max, value, term) {
                    return value;
                },
                parse: function(data) {
                    var mytab = new Array();
                    for (var i = 0; i < data.length; i++)
                        mytab[mytab.length] = { data: data[i], value: data[i].cname + ' > ' + data[i].pname };
                    return mytab;
                },
                extraParams: {
                    ajaxSearch: 1,
                    id_lang: id_lang
                }
            }
        )
            .result(function(event, data, formatted) {
                $('#search_query_' + blocksearch_type).val(data.pname);
                document.location.href = data.product_link;
            });
});

function cm_displayCurrentSearchBlock() {
    if(!Object.keys(cm_params.facets).length) {
        $('#cm_selected_facets').replaceWith('<div id="cm_selected_facets"> </div>');
    } else {
        var data;
        data = '<b>Current Search</b>';
        data += '<ul>';
        data += cm_params.GetFacets('list');
        data += '<li><span onclick="cm_params.facets = {};cm_reload();">clear all</span></li>';
        data += '</ul>';

        //return data;
        $('#cm_selected_facets').replaceWith('<div id="cm_selected_facets">' + data + '</div>');
    }
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
    console.log(cm_params.facets);
    if (!ajaxLoaderOn)
    {
        $('#center_column').prepend($('#cm_ajax_container').html());
        //$('.cm_ajax_message').center();
        /*$('.cm_ajax_message').css("position","fixed");
        $('.cm_ajax_message').css('z-index', '9');
        $('.cm_ajax_message').css('top', '50%');
        $('.cm_ajax_message').css('left', '50%');*/
        //$('#cm_ajax_loader').css('margin', '0 auto');
        //$('.cm_ajax_message').css({top:'50%',left:'50%',margin:'-'+($('.cm_ajax_message').height() / 2)+'px 0 0 -'+($('.cm_ajax_message').width() / 2)+'px'});
        $('#center_column').css('opacity', '0.7');
        ajaxLoaderOn = 1;
    }
    /*var facets = params.facets || cm_params.Getfacets();
    var page = params.page || cm_page;
    var size = params.size || cm_size;*/

    //var request = encodeURIComponent(cm_params.GetFacets('url')) + '&p=' + cm_params.page + '&n=' + cm_params.size + '&search_query=' + cm_query;
    var request = cm_params.GetFacets('url') + '&p=' + cm_params.page + '&n=' + cm_params.size + '&search_query=' + cm_query;
    if (cm_params.orderby) {
        request += '&orderby=' + encodeURIComponent(cm_params.orderby) + '&orderway=' + cm_params.orderway;

        /*if (cm_params.sort_desc) {
            request += '&sort.0.descending=true';
        }*/
    }

    var loc = search_url + ((search_url.indexOf('?') < 0) ? '?' : '&') + request;


    //cm_stopAjaxQuery();
    console.log(request);
    cm_ajaxQuery = $.ajax(
        {
            type: 'GET',
            url: baseDir + 'modules/convermax/convermax-ajax.php',
            //data: 'facets=' + JSON.stringify(cm_params.facets) + '&cm_query=' + cm_query,
            data: request,
            dataType: 'json',
            success: function(result)
            {


                console.log('cm_reload ajax success');
                //qw = result;
                //console.log(result.facets);
                //alert(result.facets);
                //$('#cm_selected_facets').replaceWith('<div id="cm_selected_facets">' + cm_displayCurrentSearchBlock() + '</div>');
                cm_displayCurrentSearchBlock();
                $('#facets_block').replaceWith('<div id="facets_block">' + result.facets + '</div>');

                //update checkboxes style
                //$('#facets_block').find('input:checkbox').uniform();


                $('#center_column').attr('id', 'old_center_column');
                $('#old_center_column').replaceWith('<div id="center_column" class="' + $('#old_center_column').attr('class') + '">'+result.productList+'</div>');
                $('#old_center_column').hide();


                $('#center_column').attr('id', 'old_center_column');
                $('#old_center_column').replaceWith('<div id="center_column" class="' + $('#old_center_column').attr('class') + '">'+result.productList+'</div>');
                $('#old_center_column').hide();




                cm_paginationButton();
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

                    // Reload products and pagination
                    alert('div.pagination form');
                    cm_reload();
                });

                if (display instanceof Function) {
                    var view = $.totalStorage('display');

                    //if (view && view != 'grid')
                        display(view);
                }

                history.pushState(null, '', loc);
                //console.log(loc);
                //alert(loc);


            },
            error: function (r) {
                /*var r = jQuery.parseJSON(response.responseText);
                alert("Message: " + r.Message);
                alert("StackTrace: " + r.StackTrace);
                alert("ExceptionType: " + r.ExceptionType);*/
                alert(r.responseText);
            }
        });
    //cm_ajaxQueries.push(cm_ajaxQuery);
    console.log('cm_reload end');
}

function cm_init()
{

}



function cm_paginationButton() {
    var current_friendly_url = '#';
    $('.pagination a').not(':hidden').each(function () {
        if ($(this).attr('href').search(/[&|\?]p=/) == -1)
            var page = 1;
        else
            var page = $(this).attr('href').replace(/^.*[&|\?]p=(\d+).*$/, '$1');

        //var location = window.location.href.replace(/#.*$/, '');
        //$(this).attr('href', location+current_friendly_url.replace(/\/page-(\d+)/, '')+'/page-'+page);
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
            //p = '&p='+ p;
            cm_params.page = p;
            //cm_reload({page: p});
            cm_reload();
            nbPage = 0;
            return false;
        });
    });
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