/*
 * singleuploadimage - jQuery plugin for upload a image, simple and elegant.
 * 
 * Copyright (c) 2014 Langwan Luo
 *
 * Licensed under the MIT license
 *
 * http://www.opensource.org/licenses/mit-license.php
 *
 * Project home:
 *     https://github.com/langwan/jquery.singleuploadimage.js
 *
 * version: 1.0.3
 */

(function ( $ ) {

    $.fn.singleupload = function(options) {

        var $this = this;
        var inputfile = null;
        
        var settings = $.extend({
            action: '#',
            onSuccess: function(url, data) {},
            onError: function(code){},
            OnProgress: function(loaded, total) {
                var percent = Math.round(loaded * 100 / total);
                $this.html(percent + '%');
            },
            name: 'img'
        }, options);

        $('#' + settings.inputId).bind('change', function() {
            $this.css('backgroundImage', 'none');
            var fd = new FormData();
            fd.append($('#' + settings.inputId).attr('name'), $('#' + settings.inputId).get(0).files[0]);
			//appends form data with propertyID
			fd.append('propertyID', $("input[name='propertyID']").val());
            var xhr = new XMLHttpRequest();
            xhr.addEventListener("load", function(ev) {
                $this.html('');
                var res = eval("(" + ev.target.responseText + ")");

                if(res.code != 0) {
                    settings.onError(res.code);
                    return;
                }
                var review = ('<img id="logoUpload" src="'+serviceURL+res.url+'" style="width:120px;height:90px;"/>');
                $this.append(review);
				d = new Date();
				$("#logoUpload").attr("src", serviceURL+res.url+"?"+d.getTime());
                settings.onSuccess(res.url, res.data);

            },
            false);
            xhr.upload.addEventListener("progress", function(ev) {
                settings.OnProgress(ev.loaded, ev.total);
            }, false);
            
            xhr.open("POST", settings.action, true);
            xhr.send(fd);  

        });  
       
    	return this;
    }

 
}( jQuery ));