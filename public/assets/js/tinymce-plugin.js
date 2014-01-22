(function () {
    "use strict";

    tinymce.create('tinymce.plugins.StudioWPGoogleMaps', {
        init: function (editor, url) {
            editor.addButton('add-map', {
                title : 'Add map',
                image : url + '/../img/add-map.png',
                onclick : function() {
                    var address = prompt("Please specify the address for the map");

                    if(address) {
                        editor.execCommand('mceInsertContent', false, '[google-map address="' + address + '"]');
                    }
                }
            });
        },

		getInfo : function() {
			return {
				longname : 'StudioWP Google Map TinyMCE Plugin',
				author : 'Federico Ramírez',
				authorurl : 'http://studiowp.net',
				infourl : '',
				version : '1.0'
			};
		}
    });

    tinymce.PluginManager.add('studiowpgooglemaps', tinymce.plugins.StudioWPGoogleMaps);

}());
