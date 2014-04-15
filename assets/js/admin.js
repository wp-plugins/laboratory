/**
 * Laboratory Admin JavaScript
 *
 * All JavaScript logic for the Laboratory admin interface.
 * @since 1.0.0
 *
 */

(function($){

	LaboratoryAdmin = 
{

/**
 * module_image_wrap()
 *
 * Wrap module image with div, this is for achieving box-shadow
 * effect on image.
 * 
 * @param images obj
 * @since 1.0.0
 */

	module_image_wrap: function( $images ) {
		$images.each(function(){
			var $el			= $(this),
					wrapper = $el.wrap('<div class="image-inner-wrapper"></div>');
			$el.css('opacity', 0).parent()
         .css('background', 'url('+ $el.attr('src') +') no-repeat');
		});
	}, // End module_image_wrap()


/**
 * create_scrollbar()
 *
 * Change default scroll bar with nice scrollbar
 * 
 * @since 1.0.0
 */
 	create_scrollbar: function() {
		$('#laboratory-addon, .laboratory-menu, .module-settings-loader .settings-inner').niceScroll({
			cursoropacitymax: 0.8,
			scrollspeed: 30,
			mousescrollstep: 30,
			railpadding: {
				right: 4
			}
		});
 	}, // End create_scrollbar()


/**
 * init_module_display()
 *
 * Initiate module display function
 * 
 * @since 1.0.0
 */
	init_module_display: function() {

		/* Minimize wordpress sidebar menu */
		$('body').addClass('folded');

		this.create_scrollbar();
		this.module_image_wrap( $('.module-image img') );

		// If hash exist, load module according to hash
		var hash = window.location.hash;
		if( $(hash).length > 0 ) {
			this.show_module( hash );
		} else {
			$('.laboratory-menu li:first a').trigger('click');
		}
		this.hash_change();

	}, // End init_module_display()


/**
 * show_module()
 *
 * Show module specified by id in parameter passed
 *
 * @param {str} id module id
 * @since 1.0.0
 */
 	show_module: function( id ) {
 		var	$menu 	= $('.laboratory-menu a'),
				$module	= $('.module-list .module-item');

		$menu.removeClass('active');
		$('a[href="'+ id +'"]').addClass('active');

		if( id !== '#all' ) {
			$module.fadeOut(200);
			$(id).delay(200).fadeIn(200);
		}

		// Set hash
		window.location.hash = id;
 	}, // End show_module()


/**
 * module_active_state()
 *
 * Sidebar click event
 * 
 * @param images obj
 * @since 1.0.0
 */
	sidebar_active_state: function() {
		var $menu 	= $('.laboratory-menu a'),
				$module	= $('.module-list .module-item'),
				laboratory = this;

		$menu.click(function(e){
			e.preventDefault();
			var $el 		= $(this),
					target	= $el.attr('href');

			laboratory.hidePlaceholder();
			laboratory.show_module( target );
			laboratory._lastHash = target;
		});
	}, // End sidebar_active_state()


_lastHash: '',
/**
 * hash_change()
 *
 * On Hash change event
 *
 * @since 1.0.0
 */
 	hash_change: function() {
 		var laboratory = this;
 		window.onhashchange = function() { 			
 			if( laboratory._lastHash !== window.location.hash ) {
				laboratory.show_module( window.location.hash );
			}
 		}
 	}, // End hash_change()


/**
 * init_sidebar()
 * 
 * @since 1.0.0
 */
	init_sidebar: function() {
		this.sidebar_active_state();
	}, // End init_sidebar()


/**
 * ajax_settings_loader()
 *
 * Load settings page
 *
 * @since 1.0.0
 */
 	ajax_settings_loader: function() {
 		var laboratory	 	= this,
 				$settingsWrap	= $('.module-settings-loader'),
 				$placeholder 	= $settingsWrap.find('.settings-scroller'),
 				$settings_btn	= $('.settings-link .btn'),
 				$ajaxLoader		= $settingsWrap.find('.ajax-loader'),
 				$closeBtn			= $settingsWrap.find('.btn-close'),
 				showPlaceholder, hidePlaceholder;

 		// Show placeholder
 		// ----------------
 		this.showPlaceholder = function() {
 			$placeholder.empty();

 			// Check if support CSS transition
 			if( laboratory.check_support('transition') ) {
 				$settingsWrap.css('margin-left', 0);
 			} else {
 				$settingsWrap.animate({
					'margin-left': 0
				}, 300);
 			}
 			$placeholder.css('opacity', 1);
			$ajaxLoader.delay(300).fadeIn();
 		};

 		// Hide Placeholder
 		// ----------------
 		this.hidePlaceholder = function() {
 			// Check if support CSS transition
 			if( laboratory.check_support('transition') ) {
 				$settingsWrap.css('margin-left', '100%');
 			} else {
 				$settingsWrap.animate({
					'margin-left': '100%'
				}, 300);
 			}
			$ajaxLoader.fadeOut();
 		};

 		// Settings button onclick event
 		// -----------------------------
 		$settings_btn.click(function(e){
 			e.preventDefault();

 			var $el						= $(this),
 					settings_url	= $el.attr('href'),
 					module_title	= $el.parents('.module-innercontainer').find('.title').text();

 			// Load Settings page
			$.ajax({
				url: settings_url,
				type: 'GET',
				dataType: 'html',
				beforeSend: function(){
					laboratory.showPlaceholder();
					$('.settings-header-fixed h3').text( module_title );
				},
				success: function( res ) {
					$($(res).find('#laboratory').html()).appendTo( $placeholder );
					$ajaxLoader.fadeOut();
					// Reload the scrollbar
					laboratory.nicescroll_resize('.module-settings-loader .settings-inner');
					laboratory.ajax_callback();
				},
				error: function( res ) {
					laboratory.hidePlaceholder();
				}
			});

 		});


 		// Close Settings
 		// ------------------------
 		$closeBtn.click(function(e){
 			e.preventDefault();

 			laboratory.hidePlaceholder();
 			$placeholder.empty();
 		});
 		
 	}, // End ajax_settings_loader()


/**
 * nicescroll_resize()
 *
 * Recalculate nicescroll scroller
 *
 * @since 1.0.0
 */
 	nicescroll_resize: function( target ) {
 		$(target).getNiceScroll()[0].resize();
 	}, // End nicescroll_resize()


/**
 * tabs_navigation()
 *
 * active tabs navigation menu for settings page
 *
 * @since 1.0.0
 */
 	tabs_navigation: function() {
 		// Make sure each heading has a unique ID.
		$('ul#settings-sections.subsubsub').find( 'a' ).each( function ( i ) {
			var id_value = $( this ).attr( 'href' ).replace( '#', '' );
			$( 'h3:contains("' + $( this ).text() + '")' ).attr( 'id', id_value ).addClass( 'section-heading' );
		});

		$( '#laboratory-container .subsubsub a.tab' ).click( function ( e ) {
			e.preventDefault();
			// Move the "current" CSS class.
			$( this ).parents( '.subsubsub' ).find( '.current' ).removeClass( 'current' );
			$( this ).addClass( 'current' );
		
			// If "All" is clicked, show all.
			if ( $( this ).hasClass( 'all' ) ) {
				$( '#laboratory-container h3, #laboratory-container form p, #laboratory-container table.form-table, p.submit' ).show();
				return false;
			}
			
			// If the link is a tab, show only the specified tab.
			var toShow = $( this ).attr( 'href' );
			$( '#laboratory-container h3.section-heading, #laboratory-container form > p:not(".submit"), #laboratory-container table' ).hide();
			$( 'h3' + toShow ).show().nextUntil( 'h3.section-heading', 'p, table, table p' ).show();
			
			// Resize nicescroll
			//laboratory.nicescroll_resize('.module-settings-loader .settings-inner');

			return false;
		});
 	}, // End tabs_navigation()


/**
 * ajax_callback()
 *
 * This will run when settings successfuly loaded
 *
 * @since 1.0.0
 */
 	ajax_callback: function() {
 		// Custom editor
 		if( $('#custom-css-code').length > 0 ) {
 			this.custom_editor('custom-css-code', 'text/css');
 		}
 		if( $('#custom-html-code-head').length > 0 ) {
 			this.custom_editor('custom-html-code-head', 'text/html');
 		}
 		if( $('#custom-html-code-footer').length > 0 ) {
 			this.custom_editor('custom-html-code-footer', 'text/html');
 		}
 		if( $('ul#settings-sections.subsubsub').length > 0 ) {
 			this.tabs_navigation();
 		}
 	}, // End ajax_callback()


/**
 * ajax_save_settings()
 *
 * Save settings
 *
 * @since 1.0.0
 */
 	ajax_save_settings: function() {
 		var $settings = $('.settings-inner'),
 				$scroller	= $settings.find('.settings-scroller'),
 				$loader		= $('.ajax-loader');

 		$settings.on('submit click', '#submit', function(e){
 			var saveUrl		= $settings.find('form').attr('action'),
 					settings	= $settings.find('form').serialize();

 			e.preventDefault();
 			
 			$.ajax({
 				url: saveUrl,
 				type: 'POST',
 				data: settings,
 				beforeSend: function() {
 					$loader.fadeIn(200);
 					$scroller.fadeTo(200, 0.3);
 				},
 				error: function() {
 					$loader.fadeOut(200);
 					$scroller.fadeTo(200, 1);
 				},
 				success: function(res) {
 					var $notice = $(res).find('.updated');
					$('.settings-error.alert').remove();
 					$notice.addClass('alert').insertBefore('.settings-container:first');
 					$loader.fadeOut(200);
 					$scroller.fadeTo(200, 1);
 					$('.settings-inner').getNiceScroll()[0].doScrollTo(0);
 				}
 			});
 		});
 	}, //End ajax_save_settings()


/**
 * resize_panel()
 *
 * Resize panel when resize event triggered
 *
 * @since 1.0.0
 */
 	resize_panel: function() {
 		$(window).bind('resize load', function(){
 			setTimeout(function(){
 				var $window 			= $(window),
 						windowHeight	= $window.height(),
 						$wrapper			= $('.outer-wrapper'),
 						$wrapperPos 	= $wrapper.offset().top,
 						footerHeight	= $('#footer').outerHeight(),
 						barHeight			= $('#wpadminbar').outerHeight();

 				$wrapper.height( windowHeight - ( $wrapperPos + footerHeight + barHeight ) );
 			}, 500);
 		});
 	}, // End resize_panel()


/**
 * ajax_component_toggle()
 *
 * Toggle activate/deactivate button on module item
 *
 * @since 1.0.0
 */
 	ajax_component_toggle: function () {
 		$( 'input.button-primary.component-control-save:not(.download):not(.purchase)' ).click( function ( e ) {
 		var thisObj = $( this );
	 	var ajaxLoaderIcon = jQuery( this ).parent().find( '.ajax-loading' );
	 	ajaxLoaderIcon.css( 'visibility', 'visible' ).fadeTo( 'slow', 1, function () {
 		var type = thisObj.parent().find( 'input[name="component-type"]' ).val();
 		
 		// Determine whether or not to activate/deactivate the component.
 		var taskObj = thisObj.parent().find( 'input[name="deactivate-component"]' );
 		
 		if ( ! taskObj.length ) {
 			var taskObj = thisObj.parent().find( 'input[name="activate-component"]' );
 		}
 		
 		var taskType = taskObj.attr( 'name' );
 		var componentToken = taskObj.val();
 		
 		var customStrings = {};

 		// Perform the AJAX call.	
		jQuery.post(
			ajaxurl, 
			{ 
				action : 'laboratory_component_toggle', 
				laboratory_component_toggle_nonce : laboratory_localized_data.laboratory_component_toggle_nonce, 
				type: type, 
				task: taskType, 
				component: componentToken
			},
			function( response ) {
				ajaxLoaderIcon.fadeTo( 'slow', 0, function () {
					jQuery( this ).css( 'visibility', 'hidden' );

					customStrings = LaboratoryAdmin.deep_copy( laboratory_localized_data ); // Make a true copy of the object, rather than by reference.
					
					// Do string replacement to include the component name in the message.
					var titleText = thisObj.parents( '.module-item' ).find( '.module-title .title' ).text();
					for ( i in customStrings ) {
						customStrings[i] = customStrings[i].replace( '%s ', titleText + ' ' );
						customStrings[i] = customStrings[i].replace( ' %s', ' ' + titleText );
					}
					
					if ( response == true ) {
						thisObj.toggleClass( 'enable' ).toggleClass( 'disable' );
						// Apply changes for deactivation (deactivation -> activation).
						if ( taskType == 'deactivate-component' ) {
							thisObj.attr( 'value', customStrings.enable );
							thisObj.parents( 'div.module-item' ).removeClass( 'enabled' ).addClass( 'disabled' ).find( 'span.status-label' ).removeClass('label-active').text( customStrings.disabled );
							thisObj.parents( 'div.module-item' ).find( 'input[name="deactivate-component"]' ).attr( 'name', 'activate-component' );
							
							var noticeMessage = $( '<div />' ).addClass( 'alert' ).text( customStrings.disabled_successfully );
						} else {
						// Apply changes for activation (activation -> deactivation).
							thisObj.attr( 'value', laboratory_localized_data.disable );
							thisObj.parents( 'div.module-item' ).removeClass( 'disabled' ).addClass( 'enabled' ).find( 'span.status-label' ).addClass('label-active').text( customStrings.enabled );
							thisObj.parents( 'div.module-item' ).find( 'input[name="activate-component"]' ).attr( 'name', 'deactivate-component' );
							
							var noticeMessage = $( '<div />' ).addClass( 'alert' ).text( customStrings.enabled_successfully );
						}

						// Toggle the settings link, if it exists.
						if ( thisObj.siblings( '.settings-link' ).length ) {
							thisObj.siblings( '.settings-link' ).toggleClass( 'hidden' );
							if ( taskType == 'deactivate-component' ) {
								var settingsURL = thisObj.siblings( '.settings-link' ).find( 'a' ).attr( 'href' );
								var urlBits = settingsURL.split( '?' );
								if ( jQuery( '#adminmenu a[href*="' + urlBits[1] + '"]' ).length ) {
									jQuery( '#adminmenu a[href*="' + urlBits[1] + '"]' ).parent( 'li' ).remove();
								}
							}
						}
					} else {
						// There was an error. Notify the user.
						if ( taskType == 'deactivate-component' ) {
							var noticeMessage = $( '<div />' ).addClass( 'alert alert-error' ).text( customStrings.diabled_error );
						} else {
							var noticeMessage = $( '<div />' ).addClass( 'alert alert-error' ).text( customStrings.enabled_error );
						}
					}

					// Display the notice message after the button.
					thisObj.parents( '.module-inside' ).prepend( noticeMessage );
					noticeMessage.delay( 2000 ).fadeTo( 'slow', 0, function () {
						noticeMessage.remove();
					});
				});
			}	
		);
 	});
 	
 	return false;
 });
}, // End ajax_component_toggle()


/**
 * custom_editor function.
 *
 * @description Turn textarea into custom editor with CodeMirror
 */
 	custom_editor: function( target, mode ) {
 		var cssEditor = CodeMirror.fromTextArea(document.getElementById(target), {
 			mode: mode,
 			lineWrapping: true,
 			tabSize: 2,
 			onCursorActivity: function() {
 				cssEditor.setLineClass(h1Line, null, null);
 				h1Line = cssEditor.setLineClass( cssEditor.getCursor().line, null, "activeline" );
 			},
 			onChange: function() {
 				$('#' + target).val( cssEditor.getValue() );
 			}
 		});
 		var h1Line = cssEditor.setLineClass(0, "activeline");
 	},


/**
 * deep_copy function.
 *
 * @description Build a deep copy of an opject, rather than passing it by reference.
 * @source http://stackoverflow.com/questions/3284285/make-object-not-pass-by-reference
 * @access public
 * @param object obj
 * @return object retVal
 */
	deep_copy: function ( obj ) {
	  if (typeof obj !== "object") return obj;
	  if (obj.constructor === RegExp) return obj;

	  var retVal = new obj.constructor();
	  for (var key in obj) {
	      if (!obj.hasOwnProperty(key)) continue;
	      retVal[key] = LaboratoryAdmin.deep_copy(obj[key]);
	  }
	  return retVal;
	}, // End deep_copy()


/**
 * check_support()
 *
 * Check support for CSS3 property
 *
 * @since 1.0.0
 */
 	check_support: function( prop ) {
    var div = document.createElement('div'),
      vendors = 'Khtml Ms O Moz Webkit'.split(' '),
      len = vendors.length;
      
      if ( prop in div.style ) return true;

      prop = prop.replace(/^[a-z]/, function(val) {
        return val.toUpperCase();
      });

      while(len--) {
        if ( vendors[len] + prop in div.style ) {
          // browser supports box-shadow. Do what you need.
          // Or use a bang (!) to test if the browser doesn't.
          return true;
        }
      }
      return false;
  } // End check_support()


}; // End LaboratoryAdmin Object


/**
 * Execute the above methods in the LaboratoryAdmin object.
 *
 * @since 1.0.0
 */
 $(document).ready(function () {

 		LaboratoryAdmin.init_sidebar();
		LaboratoryAdmin.ajax_settings_loader();
		LaboratoryAdmin.init_module_display();
		LaboratoryAdmin.ajax_component_toggle();
		LaboratoryAdmin.ajax_save_settings();
		LaboratoryAdmin.resize_panel();
		
		/* Twitter Stream ticker
		----------------------------------------------------------------- */
		var $t_stream = $('.laboratory_twitter_stream'),
				$t_stream_list = $t_stream.find('ul');

		// Only run this script when twitter feed fetched
		if( $t_stream_list.length > 0 ) {
			var $item = $t_stream_list.find('li'),
					item_length = $item.length,
					current_visible = $item.filter(':visible').index();

			// Hide all list except the first one
			$t_stream_list.find('li:not(:first)').hide();
			setInterval(function(){
				var next_visible = current_visible + 1;
				if( next_visible > item_length - 1 ) {
					next_visible = 0;
				}
				current_visible = next_visible;
				$item.hide();
				$item.eq(next_visible).fadeTo(250, 1);
			}, 5000);
		}

	});


})(jQuery);