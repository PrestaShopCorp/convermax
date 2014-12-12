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
    GetFacets: function(format) {
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
    }
};
//cm_params.facets.push({key: fieldname, val: value});



$(document).ready(function()
{
    //$('#cm_selected_facets').replaceWith('<div id="cm_selected_facets">asd<ul>' + cm_params.GetFacets('list') + '</ul></div>');

    cm_params.size = cm_size;
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
    $('select[name=n]').off('change').on('change', function(event) {
        alert('name=n');
        $('select[name=n]').val($(this).val());
        cm_params.size = $(this).val();
        cm_params.page = 1;
        cm_reload();
    });


    //$('.productsSortForm').off('change').on('change', '.selectProductSort', function(e){
    //$('.selectProductSort').off('change').on('change', function(event) {
    $('.selectProductSort').unbind('change').bind('change', function(event) {
        alert('selectProductSort');
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

        cm_reload();
    });

    cm_paginationButton();
    //cm_init();
});

function cm_getCurrentFacetsBlock() {
    if(!Object.keys(cm_params.facets).length) {
        return '';
    }
    var data;
    data = '<b>Current Search</b>';
    data += '<ul>';
    data += cm_params.GetFacets('list');
    data += '<li><span onclick="cm_params.facets = {};cm_reload()">clear all</span></li>'
    data += '</ul>';

    return data;
}

function cm_reload(params) {
    console.log(cm_params.facets);
    /*var facets = params.facets || cm_params.Getfacets();
    var page = params.page || cm_page;
    var size = params.size || cm_size;*/

    var request = 'facets=' + encodeURIComponent(cm_params.GetFacets('url')) + '&p=' + cm_params.page + '&n=' + cm_params.size + '&search_query=' + cm_query;
    if (cm_params.orderby) {
        request += '&orderby=' + encodeURIComponent(cm_params.orderby) + '&orderway=' + cm_params.orderway;

        /*if (cm_params.sort_desc) {
            request += '&sort.0.descending=true';
        }*/
    }

    var loc = search_url + ((search_url.indexOf('?') < 0) ? '?' : '&') + request;


    cm_stopAjaxQuery();
    cm_ajaxQuery = $.ajax(
        {
            type: 'GET',
            url: baseDir + 'modules/convermax/convermax-ajax.php',
            //data: 'facets=' + JSON.stringify(cm_params.facets) + '&cm_query=' + cm_query,
            data: request,
            dataType: 'json',
            success: function(result)
            {


                //qw = result;
                //console.log(result.facets);
                //alert(result.facets);
                $('#cm_selected_facets').replaceWith('<div id="cm_selected_facets">' + cm_getCurrentFacetsBlock() + '</div>');
                $('#facets_block').replaceWith('<div id="facets_block">' + result.facets + '</div>');

                //update checkboxes style
                //$('#facets_block').find('input:checkbox').uniform();


                $('#center_column').attr('id', 'old_center_column');
                $('#old_center_column').replaceWith('<div id="center_column" class="' + $('#old_center_column').attr('class') + '">'+result.productList+'</div>');
                $('#old_center_column').hide();


                $('#center_column').attr('id', 'old_center_column');
                $('#old_center_column').replaceWith('<div id="center_column" class="' + $('#old_center_column').attr('class') + '">'+result.productList+'</div>');
                $('#old_center_column').hide();


                /*if (result.pagination.search(/[^\s]/) >= 0) {
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
                }*/






                // Currente page url
                //if (typeof(cm_params_url) == 'undefined')
                    //cm_params_url = '#';


                //cm_params_url += (facets ? facets : cm_params.Get());


                //window.location = cm_params_url;

                cm_paginationButton();
                initUniform();


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
                    cm_reload();
                });

                if (display instanceof Function) {
                    var view = $.totalStorage('display');

                    //if (view && view != 'grid')
                        display(view);
                }

                //history.pushState(null, '', loc);
                //console.log(loc);
                alert(loc);


            }
        });
    cm_ajaxQueries.push(cm_ajaxQuery);
}

function cm_init()
{
    //initSliders();
    cm_initLocationChange();
    //updateProductUrl();
    if (window.location.href.split('#').length == 2 && window.location.href.split('#')[1] != '')
    {
        var params = window.location.href.split('#')[1];
        cm_reload('&selected_filters='+params);
    }
}

function cm_initLocationChange(func, time)
{
    if(!time) time = 500;
    var current_friendly_url = cm_getUrlParams();
    setInterval(function()
    {
        if(cm_getUrlParams() != current_friendly_url && !lockLocationChecking)
        {
            // Don't reload page if current_friendly_url and real url match
            if (current_friendly_url.replace(/^#(\/)?/, '') == cm_getUrlParams().replace(/^#(\/)?/, ''))
                return;

            lockLocationChecking = true;
            reloadContent('&selected_filters='+cm_getUrlParams().replace(/^#/, ''));
        }
        else {
            lockLocationChecking = false;
            current_friendly_url = cm_getUrlParams();
        }
    }, time);
}

function cm_getUrlParams()
{
    var params = current_friendly_url;
    if(window.location.href.split('#').length == 2 && window.location.href.split('#')[1] != '')
        params = '#'+window.location.href.split('#')[1];
    return params;
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