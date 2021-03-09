
$(document).ready(function() {
	'use strict';
	/*-----------------------------------------------------------------------------------*/
    /*	STICKY HEADER
    /*-----------------------------------------------------------------------------------*/
	if ($(".navbar").length) {
    	var options = {
	        offset: 350,
	        offsetSide: 'top',
	        classes: {
	            clone: 'banner--clone fixed',
	            stick: 'banner--stick',
	            unstick: 'banner--unstick'
	        },
	        onStick: function() {
	            $($.SmartMenus.Bootstrap.init);
	        },
	        onUnstick: function() {
	            $('.navbar .btn-group').removeClass('open');
	        }
	    };
	    var banner = new Headhesive('.navbar', options);
	}
    /*-----------------------------------------------------------------------------------*/
    /*	HAMBURGER MENU ICON
    /*-----------------------------------------------------------------------------------*/
    $(".nav-bars").on( "click", function() {
        $(".nav-bars").toggleClass("is-active");
    });
    /*-----------------------------------------------------------------------------------*/
	/*	DROPDOWN MENU
	/*-----------------------------------------------------------------------------------*/
    $('.navbar .nav .btn-group .dropdown-menu').on('click', function(e) {
        e.stopPropagation();
    });	   
	/*-----------------------------------------------------------------------------------*/
	/*	SLICK
	/*-----------------------------------------------------------------------------------*/
	$('.slick-wrapper').each(function(idx, item) {
		var carouselId = "carousel" + idx;
		this.id = carouselId;
		$(this).find('.slick').slick({
			dots: true,
			infinite: true,
			adaptiveHeight: true,
			touchThreshold: 10,
			swipeToSlide: true,
			slide: "#" + carouselId + " .slick .item",
			appendArrows: "#" + carouselId + " .slick-nav",
			appendDots: "#" + carouselId + " .slick-nav",
			prevArrow: '<div class="slick-prev-wrapper"><span class="slick-prev"></span></div>',
			nextArrow: '<div class="slick-next-wrapper"><span class="slick-next"></span></div>',
			customPaging: function(slider, i) {
				return '';
			}
		});
	});
	/*-----------------------------------------------------------------------------------*/
    /*	ACCORDION / COLLAPSE
    /*-----------------------------------------------------------------------------------*/
    $('.panel-group').find('.panel:has(".in")').addClass('panel-active');
    $('.panel-group').on('shown.bs.collapse', function(e) {
        $(e.target).closest('.panel').addClass(' panel-active');
    }).on('hidden.bs.collapse', function(e) {
        $(e.target).closest('.panel').removeClass(' panel-active');
    });
    /*-----------------------------------------------------------------------------------*/
    /*	PATTERN WRAPPER
    /*-----------------------------------------------------------------------------------*/
    $(".pattern-wrapper").css('background-image', function () {
	    var bg = ('url(' + $(this).data("image-src") + ')');
	    return bg;
	});
    /*-----------------------------------------------------------------------------------*/
    /*	COUNTDOWN
	/*-----------------------------------------------------------------------------------*/
	$(".countdown").countdown();
    /*-----------------------------------------------------------------------------------*/
    /*	COUNTER
    /*-----------------------------------------------------------------------------------*/
    $('.counter .value').counterUp({
        delay: 50,
        time: 1000
    });
    /*-----------------------------------------------------------------------------------*/
    /*	PROGRESSBAR
	/*-----------------------------------------------------------------------------------*/
    var $pcircle = $('.progressbar.full-circle');
    var $psemi = $('.progressbar.semi-circle');
    var $pline = $('.progressbar.line');
    
    $pcircle.each(function(i) {
        var circle = new ProgressBar.Circle(this, {
            strokeWidth: 4,
            trailWidth: 4,
            duration: 2000,
            easing: 'easeInOut',
            step: function(state, circle, attachment) {
                circle.setText(Math.round(circle.value() * 100));
            }
        });
        
        var value = ($(this).attr('data-value') / 100);
        $pcircle.waypoint(function() {
            circle.animate(value);
        }, {
            offset: "100%"
        })
    });
    $psemi.each(function(i) {
        var semi = new ProgressBar.SemiCircle(this, {
            strokeWidth: 4,
            trailWidth: 4,
            duration: 2000,
            easing: 'easeInOut',
            step: function(state, circle, attachment) {
                circle.setText(Math.round(circle.value() * 100));
            }
        });
        
        var value = ($(this).attr('data-value') / 100);
        $psemi.waypoint(function() {
            semi.animate(value);
        }, {
            offset: "100%"
        })
    });
    $pline.each(function(i) {
        var line = new ProgressBar.Line(this, {
            strokeWidth: 3,
            trailWidth: 3,
            duration: 3000,
            easing: 'easeInOut',
            text: {
                style: {
                    color: 'inherit',
                    position: 'absolute',
                    right: '0',
                    top: '-30px',
                    padding: 0,
                    margin: 0,
                    transform: null
                },
                autoStyleContainer: false
            },
            step: function(state, line, attachment) {
                line.setText(Math.round(line.value() * 100) + ' %');
            }
        });
        var value = ($(this).attr('data-value') / 100);
        $pline.waypoint(function() {
            line.animate(value);
        }, {
            offset: "100%"
        })
    });
    /*-----------------------------------------------------------------------------------*/
    /*	CIRCLE INFO BOX
    /*-----------------------------------------------------------------------------------*/
    $("#dial1").s8CircleInfoBox({
	    autoSlide: false,
	    action: "click"
	});
	$("#dial2").s8CircleInfoBox({
	    autoSlide: false,
	    action: "click"
	});
	/*-----------------------------------------------------------------------------------*/
    /*	IMAGE ICON HOVER
    /*-----------------------------------------------------------------------------------*/
    $('.overlay').prepend('<span class="bg"></span>');
    /*-----------------------------------------------------------------------------------*/
    /*	TOOLTIP
    /*-----------------------------------------------------------------------------------*/
    $('.has-tooltip').tooltip();
    $('.has-popover').popover({
	    trigger: 'focus'
    });
    /*-----------------------------------------------------------------------------------*/
	/*	INCREMENT
	/*-----------------------------------------------------------------------------------*/
    $('.qtyplus').on('click', function(e){
        e.preventDefault();
        var fieldName = $(this).attr('field');
        var currentVal = parseInt($('input[name='+fieldName+']').val());
        if (!isNaN(currentVal)) {
            $('input[name='+fieldName+']').val(currentVal + 1);
        } else {
            $('input[name='+fieldName+']').val(0);
        }
    });
    $(".qtyminus").on('click', function(e) {
        e.preventDefault();
        var fieldName = $(this).attr('field');
        var currentVal = parseInt($('input[name='+fieldName+']').val());
        if (!isNaN(currentVal) && currentVal > 0) {
            $('input[name='+fieldName+']').val(currentVal - 1);
        } else {
            $('input[name='+fieldName+']').val(0);
        }
    });
    /*-----------------------------------------------------------------------------------*/
    /*	LAZY LOAD GOOGLE MAPS
    /*-----------------------------------------------------------------------------------*/
    (function($, window, document, undefined) {
        var $window = $(window),
            mapInstances = [],
            $pluginInstance = $('.google-map').lazyLoadGoogleMaps({
                key: false,
                callback: function(container, map) {
                    var $container = $(container),
                        center = new google.maps.LatLng($container.attr('data-lat'), $container.attr('data-lng'));

                    map.setOptions({
                        center: center,
                        zoom: 15,
                        zoomControl: true,
                        zoomControlOptions: {
                            style: google.maps.ZoomControlStyle.DEFAULT
                        },
                        disableDoubleClickZoom: false,
                        mapTypeControl: true,
                        mapTypeControlOptions: {
                            style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
                        },
                        scaleControl: true,
                        scrollwheel: false,
                        streetViewControl: true,
                        draggable: true,
                        overviewMapControl: false,
                        mapTypeId: google.maps.MapTypeId.ROADMAP
                    });



                    var mapicon = new google.maps.MarkerImage("style/images/marker@2x.png", null, null, null, new google.maps.Size(40, 40));

                    var marker = new google.maps.Marker({
                        position: center,
                        map: map,
                        icon: mapicon
                    });

                    var contentString = '<div class="map-info">' +
                        '<h5>Tortor Dolor</h5>' +
                        '<div class="map-info-body">' +
                        '<p>Integer posuere erat a ante venenatis dapibus posuere.</p>' +
                        '</div>' +
                        '</div>';

                    var infowindow = new google.maps.InfoWindow({
                        content: contentString,
                        maxWidth: 200
                    });
                    marker.addListener('click', function() {
                        infowindow.open(map, marker);
                    });

                    $.data(map, 'center', center);
                    mapInstances.push(map);

                    var updateCenter = function() {
                        $.data(map, 'center', map.getCenter());
                    };
                    google.maps.event.addListener(map, 'dragend', updateCenter);
                    google.maps.event.addListener(map, 'zoom_changed', updateCenter);
                    google.maps.event.addListenerOnce(map, 'idle', function() {
                        $container.addClass('is-loaded');
                    });
                }
            });

        $window.on('resize', $pluginInstance.debounce(1000, function() {
            $.each(mapInstances, function() {
                this.setCenter($.data(this, 'center'));
            });
        }));

    })(jQuery, window, document);
    /*-----------------------------------------------------------------------------------*/
    /*	PAGE LOADING
    /*-----------------------------------------------------------------------------------*/
	$('.page-loading').delay(350).fadeOut('slow');
    $('.page-loading .status').fadeOut('slow');    
    /*-----------------------------------------------------------------------------------*/
    /*	GO TO TOP
    /*-----------------------------------------------------------------------------------*/
    $.scrollUp({
        scrollName: 'scrollUp',
        // Element ID
        scrollDistance: 300,
        // Distance from top/bottom before showing element (px)
        scrollFrom: 'top',
        // 'top' or 'bottom'
        scrollSpeed: 300,
        // Speed back to top (ms)
        easingType: 'linear',
        // Scroll to top easing (see http://easings.net/)
        animation: 'fade',
        // Fade, slide, none
        animationInSpeed: 200,
        // Animation in speed (ms)
        animationOutSpeed: 200,
        // Animation out speed (ms)
        scrollText: '<span class="btn btn-square btn-rounded btn-icon"><i class="et-chevron-small-up"></i></span>',
        // Text for element, can contain HTML
        scrollTitle: false,
        // Set a custom <a> title if required. Defaults to scrollText
        scrollImg: false,
        // Set true to use image
        activeOverlay: false,
        // Set CSS color to display scrollUp active point, e.g '#00FFFF'
        zIndex: 1001 // Z-Index for the overlay
    });
    /*-----------------------------------------------------------------------------------*/
    /*	ONEPAGE HEADER OFFSET
    /*-----------------------------------------------------------------------------------*/	
    var header_height = $('.navbar:not(.banner--clone)').outerHeight();
    var shrinked_header_height = 64;
    var firstStyle = {
        'padding-top': '' + shrinked_header_height + 'px',
        'margin-top': '-' + shrinked_header_height + 'px'
    };
    $('.onepage section').css(firstStyle);
    var secondStyle = {
        'padding-top': '' + header_height + 'px',
        'margin-top': '-' + header_height + 'px'
    };
    $('.onepage section:first-of-type').css(secondStyle);
	/*-----------------------------------------------------------------------------------*/
    /*	ONEPAGE NAV LINKS
    /*-----------------------------------------------------------------------------------*/	
	var empty_a = $('.onepage .navbar ul.navbar-nav a[href="#"]');	
	empty_a.on('click', function(e) {
	    e.preventDefault();
	});
    $('.onepage .navbar .nav li a').on('click', function() {
        $('.navbar .navbar-collapse.in').collapse('hide');
        $('.nav-bars').removeClass('is-active');
    });
    /*-----------------------------------------------------------------------------------*/
	/*	ONEPAGE SMOOTH SCROLL
	/*-----------------------------------------------------------------------------------*/	
	$(function() {
	  setTimeout(function() {
	    if (location.hash) {
	      window.scrollTo(0, 0);
	      var target = location.hash.split('#');
	      smoothScrollTo($('#'+target[1]));
	    }
	  }, 1);  
	  $('a.scroll[href*=#]:not([href=#])').on('click', function() {
	    if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
	      smoothScrollTo($(this.hash));
	      return false;
	    }
	  });  
	  function smoothScrollTo(target) {
	    var target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
	    
	    if (target.length) {
	      $('html,body').animate({
	        scrollTop: target.offset().top
	      }, 1500, 'easeInOutExpo');
	    }
	  }
	});  
});