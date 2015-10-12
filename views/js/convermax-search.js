/*
 * 2015 CONVERMAX CORP
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@convermax.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author CONVERMAX CORP <info@convermax.com>
 *  @copyright  2015 CONVERMAX CORP
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of CONVERMAX CORP
 */
var cm_params = {
	facets: {},
	sliders: {},
    sliders_display: {},
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
			for(var j = 0; j < this.facets[keys[i]].length; j++ ) {
				if (j in this.facets[keys[i]]) {
					if (format == 'url') {
						data += (i == 0 ? '' : '&') + 'cm_select[' + encodeURIComponent(keys[i]) + '][]=' + encodeURIComponent(this.facets[keys[i]][j]) + (j == (this.facets[keys[i]].length - 1) ? '' : '&');
					}
					if (format == 'list') {
						data += '<li><span onclick="cm_params.SetFacet(false, \'' + keys[i] + '\', \'' + this.facets[keys[i]][j].replace(/'/g, "\\'") + '\');cm_reload();">[x]</span>' + this.facets_display[keys[i]] + ' - ' + this.facets[keys[i]][j] + '</li>';
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
					data += '<li><span onclick="cm_params.ResetSlider(\'' + keys[i] + '\', \'' + this.sliders[keys[i]][1] + '\');cm_reload();">[x]</span>' + this.sliders_display[keys[i]] + ' - ' + this.sliders[keys[i]][0] + '</li>';
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

	getUserId();
	getSessionId();

	$('#cm_facets').on('change', 'input[type=checkbox]', function()
	{
		cm_params.SetFacet(this.checked, this.dataset.fieldname, this.value, this.dataset.displayname);
		cm_params.page = 1;
		var params = {};
		params.searchfeatures = 'FacetSelected';
		cm_reload(params);
	});

    cm_initSliders();
    cm_initTrees();

    if (typeof cm_category == 'undefined') {

        $(document).off('change', 'select[name="n"]');
        $(document).on('change', 'select[name=n]', function (e) {
            $('select[name=n]').val($(this).val());
            cm_params.size = $(this).val();
            cm_params.page = 1;
            cm_reload();
        });

        $('#cm_related a').click(function (e) {
            e.preventDefault();
            cm_params.page = 1;
            cm_query = $(this).text();
            cm_reload();
        });


        $(document).off('change', '.selectProductSort');
        $(document).on('change', '.selectProductSort', function (e) {
            var splitData = $(this).val().split(':');

            cm_params.orderby = splitData[0];
            cm_params.orderway = splitData[1];
            cm_params.page = 1;

            cm_reload();
        });
        cm_paginationButton();
        cm_displayCurrentSearchBlock();
    }
	cm_init();

    $(window).resize(cm_resize);
});

function cm_initSliders() {
	$('.cm_slider').each(function() {
		cm_params.sliders[this.dataset.fieldname][1] = this.dataset.range;
		cm_params.sliders_display[this.dataset.fieldname] = this.dataset.displayname;
		var dollar = (this.dataset.fieldname == 'price') ? '$' : '';
        var r = /\[(.*) TO (.*)\]/i;
		var v = r.exec(this.dataset.range);
		var s = r.exec(cm_params.sliders[this.dataset.fieldname][0]);
		var min = parseFloat(v[1]);
		var max = parseFloat(v[2]);
		var selectionmin = parseFloat(s[1]);
		var selectionmax = parseFloat(s[2]);
		var sliderinfo = $("<div>").addClass("cm_sliderinfo").html('<p>Selected: '+ dollar + selectionmin + ' to ' + dollar + selectionmax + '</p>').insertBefore($(this));
		$(this).slider({
			range: true,
			min: min,
			max: max,
			values: [selectionmin, selectionmax],
			slide: function (e, ui) {
				sliderinfo.html('<p>Selected: ' + dollar + ui.values[0] + ' to ' + dollar + ui.values[1] + '</p>');
			},
			change: function (e, ui) {
				var newselection = '[' + ui.values[0] + ' TO ' + ui.values[1] +']';
				cm_params.sliders[this.dataset.fieldname][0] = newselection;
				cm_params.page = 1;
				var params = {};
				params.searchfeatures = 'FacetSelected';
				cm_reload(params);
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
		var params = {};
		params.searchfeatures = 'FacetSelected';
		cm_reload(params);
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
		$('#cm_selected_facets').replaceWith('<div id="cm_selected_facets" class="list-block" style="display: none;"> </div>');
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
	if (typeof params != 'undefined' && typeof params.searchfeatures != 'undefined') {
		request += '&searchfeatures=' + params.searchfeatures;
	}

	var loc = cm_search_url + ((cm_search_url.indexOf('?') < 0) ? '?' : '&') + request;

    if (typeof cm_category != 'undefined' && cm_category == true) {
        window.location = loc;
        return;
    }

	cm_ajaxQuery = $.ajax(
		{
			type: 'GET',
			url: cm_search_url,
			data: request + '&ajax=true',
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
				ajaxLoaderOn = 0;


				if (typeof display != 'undefined' && display instanceof Function) {
					var view = $.totalStorage('display');
					display(view);
				}
				cm_init();

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

		$(this).children().on('click', function(e) {
			e.preventDefault();
			if (nbPage == 0)
				p = parseInt($(this).children().html()) + parseInt(nbPage);
			else
				p = nbPage;
			cm_params.page = p;
			cm_reload();
			nbPage = 0;
			return false;
		});
	});
}


function cm_init() {
	$("#cm_facets input[type='checkbox'], select.form-control").uniform();
    cm_resize();
    if (typeof cm_category == 'undefined') {
        $('div.pagination form').on('submit', function (e) {
            e.preventDefault();
            var size = cm_params.size;
            cm_params.size = $(this).find('input[name=n]').val();
            cm_params.page = 1;
            cm_reload();
            cm_params.size = size;
        });
    }
}

function toggleList(item) {
    if ($(item).parent().find('.cm_more_results').css('display') == 'none') {
        $(item).parent().find('.cm_more_results').css('display', 'block');
        $(item).text('Show less');
    } else {
        $(item).parent().find('.cm_more_results').css('display', 'none');
        $(item).text('Show more');
    }
}

function toggleFacet(item, state) {
    switch (state) {
        case 'show':
            $(item).parent().find('.cm_facetbody').css('display', 'block');
            break;
        case 'hide':
            $(item).parent().find('.cm_facetbody').css('display', 'none');
            break;
        default:
            if ($(item).parent().find('.cm_facetbody').css('display') == 'none') {
                $(item).parent().find('.cm_facetbody').css('display', 'block');
            } else {
                $(item).parent().find('.cm_facetbody').css('display', 'none');
            }
    }
}

function toggleBlock() {
    //
}

function cm_resize() {
    var state = 'show';
    if ($(window).width() < 767) {
        state = 'hide';
    }
    $('.cm_facet_title').each(function() {
        toggleFacet(this, state);
    });
    $('#facets_block div.checker').each(function() {
        $(this).css('display', 'inline');
    });
    $('.cm_more_results').each(function() {
        $(this).css('display', 'none');
        $(this).parent().find('.cm_more_link').text('Show more');
    });
}