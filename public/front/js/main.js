$(document).ready(function(){
    //Shoping cart
    
    $("#shopingCart").click(function(){
        $("div.shopingcart").toggle('slide');
    });
    
    $("td a.closeShop").click(function(){
       $(this).parent().parent().fadeOut();
    });
    
    
   $(".slider").diyslider(); 
   //  news slider

 var owl = $("#owl-demo");
     
      $('.owl-carousel').owlCarousel({
          autoplay:1,
    loop:true,
    margin:10,
    nav:true,
    responsive:{
        0:{
            items:3
        },
        600:{
            items:6
        },
        1000:{
            items:10
        }
    }
});


// use buttons to change slide
$("#go-left").bind("click", function(){
    // Go to the previous slide
    $(".slider").diyslider("move", "back");
});
$("#go-right").bind("click", function(){
    // Go to the previous slide
    $(".slider").diyslider("move", "forth");
});
var sirina;
var visina;
function setSlider(number){
    sirina = $(".news .container .col-xs-10").css("width");
    visina = $(".news figure.newsItem").css("height");
    $(".slider").diyslider({
     width: sirina,// width of the slider
    height: "530px",
    display: number // number of slides you want it to display at once
}).bind("moved.diyslider", function(event, slide, slideNumber){
});
}

var windowWidth = $(window).width();
if(windowWidth < 600){
   setSlider(1);
}else if(windowWidth > 600 && windowWidth < 1000){
   setSlider(2);
}else{
    setSlider(3);
}

$( window ).resize(function() {
  windowWidth = $(window).width();
if(windowWidth < 600){
   setSlider(1);
}else if(windowWidth > 600 && windowWidth < 1000){
   setSlider(2);
}else{
    setSlider(3);
}
});



//  News slide hover

var sirinaNewsImg =  $('.figure div.imgNews').css("width");
sirinaNewsImg = parseInt(sirina);

    $('.imgNews').mouseover(function(){
        $('.hoverNews', this).show(0);                        
    });
    $('.imgNews').mouseleave(function(){
        $('.hoverNews', this).hide(0);                        
    });
    
    /*  Google Map  */
    
function init_map(){
    var myOptions = {
        zoom:14,
        center:new google.maps.LatLng(44.831193,20.610080),
        scrollwheel: true,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    map = new google.maps.Map(document.getElementById('gmap_canvas'), 
    myOptions);
    marker = new google.maps.Marker({
        map: map,
        position: new google.maps.LatLng(44.831193,20.410080)
    });
    infowindow = new google.maps.InfoWindow({
        content:'<strong>Cubes</strong> <br>Belgrade,<br>Bul. Mihajla Pupina 181'});
    google.maps.event.addListener(
            marker,
    'click',
    function(){infowindow.open(map,marker);
    });
    infowindow.open(map,marker);}
google.maps.event.addDomListener(window, 'load', init_map);



// Category

$("article.catalog div.category span").click(function(){
    $(this).parent().css("display", "none");
    $(this).toggleClass("glyphicon-menu-up");
});

$("aside.options > h2").click(function(){
    
    $(".showOption").toggle();
    
});
    
    
    $(".mySlider").nstSlider({
    "left_grip_selector": ".leftGrip",
    "right_grip_selector": ".rightGrip",
    "value_bar_selector": ".bar",
    "value_changed_callback": function(cause, minValue, maxValue, prevMinValue, prevMaxValue) {
        // show the suggested values in your min/max labels elements
    }
});
$('.nstSlider').nstSlider({
    "left_grip_selector": ".leftGrip",
    "right_grip_selector": ".rightGrip",
    "value_bar_selector": ".bar",
    "value_changed_callback": function(cause, leftValue, rightValue) {
        var $container = $(this).parent();
        $container.find('.leftLabel').text(leftValue);
        $container.find('.rightLabel').text(rightValue);
    }
});
$('#changeStepIncrement').click(function() {
    var $button = $(this);
    if ($button.text().indexOf('Histogram') > -1) {
        var histogram = [4, 6, 3, 20, 30, 82, 107, 75, 82, 30, 20, 3, 2, 4, 1];
        $('.nstSlider').nstSlider('set_step_histogram', histogram);
        $button.text('Use Linear Increment Step');
    } else {
        $('.nstSlider').nstSlider('unset_step_histogram');
        $button.text('Use Histogram-Based Increment Step');
    }
    $('.nstSlider').nstSlider('refresh');
});


// Product Page

 $('#myCarousel').carousel({
                interval: 5000
        });
 
        //Handles the carousel thumbnails
        $('[id^=carousel-selector-]').click(function () {
        var id_selector = $(this).attr("id");
        try {
            var id = /-(\d+)$/.exec(id_selector)[1];
            console.log(id_selector, id);
            jQuery('#myCarousel').carousel(parseInt(id));
        } catch (e) {
            console.log('Regex failed!', e);
        }
    });
        // When the carousel slides, auto update the text
        $('#myCarousel').on('slid.bs.carousel', function (e) {
                 var id = $('.item.active').data('slide-number');
                $('#carousel-text').html($('#slide-content-'+id).html());
        });
        
        
        $('#star1').starrr({
      change: function(e, value){
        if (value) {
          $('.your-choice-was').show();
          $('.choice').text(value);
        } else {
          $('.your-choice-was').hide();
        }
      }
    });
    
    /* logIn  Page  */
    
    $('#loginForm').validate({
    submit: {
        settings: {
            clear: 'keypress'
        },
        callback: {
            onError: function (error) {
                alert(error.toString());
            }
        }
    }
});
$('#loginForm').validate();

/*  Password  */
$('#passwordForgot').validate({
    submit: {
        settings: {
            clear: 'keypress'
        },
        callback: {
            onError: function (error) {
                alert(error.toString());
            }
        }
    }
});
$('#passwordForgot').validate();
  /* User Panel Page */
  
  $('#resetUserPanel').validate({
    submit: {
        settings: {
            clear: 'keypress'
        },
        callback: {
            onError: function (error) {
                alert(error.toString());
            }
        }
    }
});
$('#resetUserPanel').validate();



$("main.logIn div.form-group span").click(function(){
    var check = $("#inputPassword").attr('type');
    if(check == "password"){
        $("#inputPassword").attr("type","text");
    }else{
        $("#inputPassword").attr("type","password");
    }
    $("main.logIn div.form-group span i").toggleClass("glyphicon-eye-close");
    
});



$('#registratinForm').validate({
    submit: {
        settings: {
            clear: 'keypress'
        },
        callback: {
            onError: function (error) {
                alert(error.toString());
            }
        }
    }
});
$('#registratinForm').validate();
$('#registratinFormFirm').validate({
    submit: {
        settings: {
            clear: 'keypress'
        },
        callback: {
            onError: function (error) {
                alert(error.toString());
            }
        }
    }
});
$('#registratinFormFirm').validate();

});