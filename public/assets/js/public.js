/*
 * StudioWP Google Map Plugin. 
 *
 * Author: Federico Ramírez <federico@studiowp.net>
 */
(function ($) {
	"use strict";

    // Find all maps
    $('div.studiowp-google-map').each(function (idx, e) {
        var el = $(e),
            map,
            marker,
            lat = el.attr('map-lat'),
            lng = el.attr('map-lng'),
            zoom = parseInt(el.attr('map-zoom'), 10) || 15,
            latlng = new google.maps.LatLng(lat, lng);

        el.width(el.attr('map-width'));
        el.height(el.attr('map-height'));

        if(el.attr('map-center') === 'true') {
            el.css('margin', 'auto');
        }

        map = new google.maps.Map(e, {
            center: latlng,
            zoom: zoom,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        });

        marker = new google.maps.Marker({
            position: latlng,
            map: map
        });
    });
}(jQuery));
