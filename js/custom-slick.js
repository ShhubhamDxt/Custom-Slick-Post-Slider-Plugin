jQuery(document).ready(function ($) {
    var $slider = $('.custom-slider');
    var autoplay = slickSliderOptions.autoplay == '1';
    $slider.slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        autoplay: slickSliderOptions.autoplay == '1',
        autoplaySpeed: 5000,
        arrows: slickSliderOptions.arrows == '1',
        dots: slickSliderOptions.dots == '1',
        prevArrow: slickSliderOptions.arrow_image
            ? `<button type="button" class="slick-prev"><img src="${slickSliderOptions.arrow_image}" alt="Prev" style="transform: scaleX(-1);"></button>`
            : '<button type="button" class="slick-prev">Prev</button>',
        nextArrow: slickSliderOptions.arrow_image
            ? `<button type="button" class="slick-next"><img src="${slickSliderOptions.arrow_image}" alt="Next"></button>`
            : '<button type="button" class="slick-next">Next</button>',
        customPaging: function (slider, i) {
            return '<span class="custom-dot"><i class="fa fa-circle"></i></span>';
        }
    });

    // Wrap slick dots in a container
    $('.slick-dots').wrap('<div class="slick-controls-wrapper"></div>');

    // Create Play/Pause Icon
    var playPauseIcon = $('<i id="slick-play-pause" class="fa fa-pause slick-control"></i>');

    // Set the correct active dot color from options
    // var activeDotColor = slickSliderOptions.active_dot || '#000';
    $('#slick-play-pause').css('color', slickSliderOptions.active_dot);

    // Append Play/Pause button inside wrapper but outside dots
    $('.slick-controls-wrapper').prepend(playPauseIcon);

    // Apply dot colors dynamically
    function updateDotColors() {
        $('.custom-dot i').css('color', slickSliderOptions.dot_color);
        $('.slick-dots li.slick-active .custom-dot i').css('color', slickSliderOptions.active_dot);
    }

    updateDotColors(); // Apply initially

    // Reapply active dot color on slide change
    $slider.on('afterChange', function () {
        updateDotColors();
    });

    // Play/Pause Toggle
    $('#slick-play-pause').on('click', function () {
        if ($(this).hasClass('fa-pause')) {
            $slider.slick('slickPause');
            $(this).removeClass('fa-pause').addClass('fa-play');
        } else {
            $slider.slick('slickPlay');
            $(this).removeClass('fa-play').addClass('fa-pause');
        }
    });

    //Slider(slide images) height
    $('.custom-slider .slide img').css('height', `${slickSliderOptions.image_height}px`);
    $('input[name="slick_image_height"]').on('change', function() {
        slickSliderOptions.image_height = $(this).val();
        initializeSlider();
    });

    // Apply title color
    $('.custom-slider .slide-overlay-text').css('color', slickSliderOptions.title_color);
    $('input[name="slick_title_color"]').on('change', function() {
        slickSliderOptions.title_color = $(this).val();
        $('.custom-slider .slide-overlay-text').css('color', slickSliderOptions.title_color);
    });

    // Style arrows
    $('.slick-prev, .slick-next').css({ 'position': 'absolute', 'top': '50%', 'transform': 'translateY(-50%)', 'z-index': '10', 'cursor': 'pointer', 'background': 'none', 'border': 'none', 'outline': 'none', 'padding': 0 });

    $('.slick-prev').css({ 'left': '10px' });
    $('.slick-next').css({ 'right': '10px' });

    // Flip right arrow
    $('.flip-arrow').css({
        'transform': 'scaleX(-1)' // Flips the image horizontally
    });

    // Apply dot styles dynamically
    let dotColor = slickSliderOptions.dot_color;
    let activeDotColor = slickSliderOptions.active_dot;

    $('.slick-dots li button').css({ 'background': dotColor, 'border-radius': '50%', 'width': '10px', 'height': '10px', 'padding': '0', 'border': 'none' });

    function updateActiveDotColor() {
        $('.slick-dots li button').css('background', dotColor); // Reset all dots
        $('.slick-dots li.slick-active button').css('background', activeDotColor); // Active dot
    }

    updateActiveDotColor(); // Set initial state

    $('.custom-slider').on('afterChange', function() {
        updateActiveDotColor(); // Update colors on slide change
    });

});
