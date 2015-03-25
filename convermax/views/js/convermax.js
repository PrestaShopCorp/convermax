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
$(document).ready(function()
{

    getUserId();
    getSessionId();

    var input = $("<input>")
        .attr("type", "hidden")
        .attr("name", "searchfeatures").val("QueryTyped");
    $("#search_query_" + blocksearch_type).parent('form').append($(input));

    //autocomplete part
    $("#search_query_" + blocksearch_type).unautocomplete();

    var width_ac_results = 	$("#search_query_" + blocksearch_type).parent('form').width();
    if (typeof ajaxsearch != 'undefined' && ajaxsearch && typeof blocksearch_type !== 'undefined' && blocksearch_type)

        $("#search_query_" + blocksearch_type).autocomplete(

            cm_url + '/autocomplete/json',
            {
                minChars: 3,
                max: 12,
                width: (width_ac_results > 0 ? width_ac_results : 500),
                selectFirst: false,
                scroll: false,
                dataType: "json",
                formatItem: function(data, i, max, value, term) {
                    var params = {};
                    if (value == 'Freetext') {
                        return '<div class="autocomplete-item">' + data.Text + '</div>';
                    }
                    if (value == 'Product') {
                        return '<div class="autocomplete-item"><img src="' + data.img_link + '"><div class="autocomplete-desc">' + data.description_short + '</div></div>';
                    }
                    if (value == 'Category') {
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
                    var term = $("#search_query_" + blocksearch_type).val();
                    for (var i = 0; i < data.length; i++) {
                        if (data[i].Type == 'Product' && displayproduct) {
                            mytab[mytab.length] = { data: 'Product Search:', value: 'group' };
                            displayproduct = false;
                        }
                        if (data[i].Type == 'Category' && displaycat) {
                            mytab[mytab.length] = { data: 'Category Search:', value: 'group' };
                            displaycat = false;
                        }
                        data[i].position = i + 1;
                        data[i].term = term;
                        mytab[mytab.length] = {data: data[i], value: data[i].Type};
                    }
                    return mytab;
                },
                extraParams: {
                    query: function(){return $("#search_query_" + blocksearch_type).val()}
                }
            }
        )
            .result(function(event, data, formatted) {
                if (data.Type == 'Freetext') {
                    trackAutocomplete(data);
                    document.location.href = search_url + ((search_url.indexOf('?') < 0) ? '?' : '&') + 'search_query=' + data.Text + '&searchfeatures=QueryTyped';
                }
                if (data.Type == 'Product') {
                    trackAutocomplete(data);
                    document.location.href = data.link;
                }
                if (data.Type == 'Category') {
                    trackAutocomplete(data);
                    document.location.href = search_url + ((search_url.indexOf('?') < 0) ? '?' : '&') + 'cm_select[' + data.FieldName + '][]=' + data.FacetValue + '&searchfeatures=FacetSelected';
                }
            });
});

//tracking functions
function generateId(length) {
    var len = length !== undefined ? length : 16;
    var result = "";
    while (result.length < len) {
        var randNum = Math.floor(Math.random() * 16);
        if (randNum < 10) {
            result += randNum;
        } else {
            result += randNum === 10 ? "a" : randNum === 11 ? "b" : randNum === 12 ? "c" : randNum === 13 ? "d" : randNum === 14 ? "e" : "f";
        }
    }
    return result;
}

function makeIdFromCookie(fieldName, expire, renew) {
    var storedValue = $.cookie(fieldName);
    if (storedValue !== undefined && (typeof storedValue == "string" || storedValue instanceof String)) {
        if (renew) {
            $.cookie(fieldName, storedValue, { expires: expire });
        }
        return storedValue;
    }
    var newId = generateId();
    $.cookie(fieldName, newId, { expires: expire });
    return newId;
}

function getUserId() {
    return makeIdFromCookie("cmuid", 3650);
}

function getSessionId() {
    var date = new Date();
    var minutes = 60;
    date.setTime(date.getTime() + (minutes * 60 * 1000));
    return makeIdFromCookie("cmsid", date, true);
}



function trackAutocomplete(item) {
    var eventParams = {
        SuggestionType: item.Type,
        UserInput: item.term,
        Position: item.position
    };
    switch(item.Type){
        case 'Freetext':
            eventParams.Value = item.Text;
            break;
        case 'Product':
            eventParams.ProductId = item.id_product;
            break;
        case 'Category':
            eventParams.Field = item.FieldName;
            eventParams.Value = item.FacetValue;
            break;
    }
    trackEvent('SuggestionSelection', eventParams);
}

function trackEvent(eventType, eventParams) {
    var event = {};
    event.UserAgent = window.navigator.userAgent;
    event.UserID = getUserId();
    event.SessionID = getSessionId();
    event.EventType = eventType;
    event.EventParams = eventParams;

    $.ajax({
        type: 'POST',
        url: cm_url + '/track',
        data: JSON.stringify(event),
        dataType: 'json',
        contentType: 'application/json'
    });
}