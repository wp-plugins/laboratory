(function(a){a.fn.colabs_mc_widget=function(b){var e,c,d;e={url:"/",cookie_id:false,cookie_value:""};d=jQuery.extend(e,b);c=a(this);c.submit(function(){var f;f=jQuery("<div></div>");f.css({"background-image":"url("+d.loader_graphic+")","background-position":"center center","background-repeat":"no-repeat",height:"100%",left:"0",position:"absolute",top:"0",width:"100%","z-index":"100"});c.css({height:"100%",position:"relative",width:"100%"});c.children().hide();c.append(f);a.getJSON(d.url,c.serialize(),function(h,k){var j,g,i;if("success"===k){if(true===h.success){i=jQuery("<p>"+h.success_message+"</p>");i.hide();c.fadeTo(400,0,function(){c.html(i);i.show();c.fadeTo(400,1)});if(false!==d.cookie_id){j=new Date();j.setTime(j.getTime()+"3153600000");document.cookie=d.cookie_id+"="+d.cookie_value+"; expires="+j.toGMTString()+";"}}else{g=jQuery(".error",c);if(0===g.length){f.remove();c.children().show();g=jQuery('<div class="error"></div>');g.prependTo(c)}else{f.remove();c.children().show()}g.html(h.error)}}return false});return false})}}(jQuery));