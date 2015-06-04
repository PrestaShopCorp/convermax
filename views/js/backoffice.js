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
//google analytics
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

ga('create', 'UA-41340404-1', 'auto');
//ga('send', 'pageview');

//youtube
var tag = document.createElement('script');

tag.src = "https://www.youtube.com/iframe_api";
var firstScriptTag = document.getElementsByTagName('script')[0];
firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

var player;
function onYouTubeIframeAPIReady() {
    player = new YT.Player('player', {
        height: '315',
        width: '500',
        videoId: 'Sme5uRzhCdM',
        events: {
            'onReady': onPlayerReady,
            'onStateChange': onPlayerStateChange
        }
    });
}

var pauseFlag = false;
function onPlayerReady(e) {

}
function onPlayerStateChange(e) {
    e.data == YT.PlayerState.PLAYING && setTimeout(onPlayerPercent, 1000, e.target);
    if (e.data == YT.PlayerState.PLAYING) {
        ga('send', 'event', 'Presta', 'Play');
        pauseFlag = true;
    }
    if (e.data == YT.PlayerState.PAUSED && pauseFlag) {
        ga('send', 'event', 'Presta', 'Pause');
        pauseFlag = false;
    }
    if (e.data == YT.PlayerState.ENDED) {
        ga('send', 'event', 'Presta', 'Finished');
    }
}

function onPlayerPercent(e) {
    if (e.getPlayerState() == YT.PlayerState.PLAYING) {
        var t = e.getDuration() - e.getCurrentTime() <= 1.5 ? 1 : (Math.floor(e.getCurrentTime() / e.getDuration() * 4) / 4).toFixed(2);         if (!e.lastP || t > e.lastP &&(t != 0 && t != 1)) {
            e.lastP = t;
            ga('send', 'event', 'Presta', t * 100 + "%");
        }
    e.lastP != 1 && setTimeout(onPlayerPercent, 1000, e);
    }
}
////

setInterval(checkIndexStatus, 5000);

$(document).ready(function() {
    $("a.gallery").fancybox({
        beforeLoad: function () {
            var filename = this.href.replace(/^.*[\\\/]/, '');
            ga('send', 'event', 'Presta', filename);
        }
    });
    $("#startbutton").click(function(e){
        var trackers, linker;
        if(ga !== undefined && typeof ga.getAll === 'function') {
            trackers = ga.getAll();
            if(trackers.length) {
                linker = new window.gaplugins.Linker(trackers[0]);
                destinationUrl = linker.decorate('https://admin.convermax.com/v2/signup?returnUrl=' + encodeURIComponent(document.location.href));
            }
            ga('send', 'event', 'Presta', 'ClickOnConnectButton');
        }
        window.open(destinationUrl, '_blank');
    });
    $(".showconnectionform").click(function(e){
        e.preventDefault();
        $(".connectionbutton").css('display', 'none');
        $(".connectionform").css('display', 'block');
    });
    $(".hideconnectionform").click(function(e){
        e.preventDefault();
        $(".connectionform").css('display', 'none');
        $(".connectionbutton").css('display', 'block');
    });
    $("#reindex").click(function(e){
        //e.preventDefault();
        $.ajax({
            //method: "POST",
            url: $(this).attr('data-url'),
            //data: { action: value },
            success: function () {
                $(this).attr('disabled','disabled');
            }
        });
    })
});

function checkIndexStatus() {
    $.ajax({
        //method: "POST",
        url: $("#indexation").attr('data-url'),
        //data: { action: value },
        success: function (result) {
            //result = JSON.parse(result);
            if(result.InProgress == true) {
                $("#reindex").attr('disabled','disabled');
                $("#indexation").css('display', 'block');
                $("#total_items").text(result.TotalEntries);
                $("#current_item").text(result.CurrentEntry);
            } else {
                $("#reindex").removeAttr('disabled');
                $("#indexation").css('display', 'none');
            }
        }
    });
}