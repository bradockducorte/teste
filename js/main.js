/*
    Template Name: Compras - Business & Consulting html landing page
    Author: webthemes
    Author Uri: https://www.templatemonster.com/authors/webthemes/
    Version: 1.2
*/

(function ($) {
    "use strict";

    $(window).on('load', function(){
        // Prealoder
        $('.preloader').fadeOut();

        //. Isotope Initialize
        function isotopeInit() {
            $('.project_items').isotope({
                itemSelector: '.item',
                masonry: {
                    columnWidth: '.item'
                }
            });
            $('.project_filter_menu ul li').on('click', function () {
                $('.project_filter_menu ul li').removeClass('active');
                $(this).addClass('active');
                var selector = $(this).attr('data-filter');
                $('.project_items').isotope({
                    filter: selector
                });
                return false;
            });
        }
        isotopeInit();
    });


    $(document).ready(function () {
        // Hamburger Menu
        $('.hamburger-menu, #menu li a').on('click', function () {
            $('.hamburger-menu .line-top, #menu, .menu_wrapper').toggleClass('current');
            $('.hamburger-menu .line-center').toggleClass('current');
            $('.hamburger-menu .line-bottom').toggleClass('current');
        });
        
        
        // Show or hide the sticky footer button
        $(window).on('scroll', function () {
            if ($(this).scrollTop() > 600) {
                $('.back-to-top').fadeIn(200);
            } else {
                $('.back-to-top').fadeOut(200);
            }
        });

        //Animate the scroll to yop
        $('.back-to-top').on('click', function (event) {
            event.preventDefault();

            $('html, body').animate({
                scrollTop: 0,
            }, 600);
        });

        //05. sticky header
        function sticky_header(){
            $(window).on('scroll', function() {
                if (!$('#menu').hasClass('current')) {
                    if ($(window).scrollTop() > 70) {
                        $('header').addClass('fixed_header');
                    } else {
                        $('header').removeClass('fixed_header');
                    }
                }else{
                    $('header').addClass('fixed_header');
                }
            });
        }
        sticky_header();

         // Smoot Scroll Effect
        $('#menu li a').bind('click', function(event) {
            var $anchor = $(this);
            var headerH = '70';
            $('html, body').stop().animate({
                scrollTop: $($anchor.attr('href')).offset().top - headerH + "px"
            }, 600, 'easeInSine');
            event.preventDefault();
        });

        //06. magnific Popup Initialize
        function magnificPopupInit() {
            $('.snake').magnificPopup({
                type: 'image',
                gallery: {
                    enabled: true
                }
            });
        }
        magnificPopupInit();

         //Hero Slider Activation
        $(".hero_slider .owl-carousel").owlCarousel({
            loop: true,
            margin: 0,
            nav: true,
            items: 1,
            dots: false,
            animateOut: 'fadeOut',
            animateIn: 'fadeIn',
            navText: ["<i class='fal fa-angle-left'></i>", "<i class='fal fa-angle-right'></i>"],
            smartSpeed: 1200,
            autoHeight: false,
            autoplay: true,
            mouseDrag: false,
            touchDrag: false,
            pullDrag: false,
            freeDrag: false,
        });

        //07. testimonial Slider Initialize
        function testi_carouselInit() {
            $('.owl-carousel.testi_carousel').owlCarousel({
                dots: false,
                loop: true,
                margin: 30,
                stagePadding: 2,
                autoplay: true,
                smartSpeed: 1500,
                autoplayTimeout: 4500,
                autoplayHoverPause: true,
                responsive: {
                    0: {
                        items: 1
                    },
                    768: {
                        items: 2,
                    },
                    992: {
                        items: 2
                    }
                }
            });
        }
        testi_carouselInit();

        //Counter Number
        $('.about_wrapper').on('inview', function(event, visible) {
            if (visible) {
                $(this).find('.count').each(function () {
                    var $this = $(this);
                    $({ Counter: 0 }).animate({ Counter: $this.text() }, {
                        duration: 1500,
                        easing: 'swing',
                        step: function () {
                            $this.text(Math.ceil(this.Counter));
                        }
                    });
                });
                $(this).unbind('inview');
            }
        });


        //Aos Activation
        setTimeout(function(){
            AOS.init({
                duration: 700,
            });
        }, 600);

        //Hover overlay Activation
        $(".snake").snakeify({
            speed: 200
        });

    });

})(jQuery);
