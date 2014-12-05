$(document).ready(function(){
    /* Resize nav */
    $('.rv-main').waypoint(function(direction){
    
        if(direction == 'down'){
            $('.navbar--rv').css('min-height', 50);
            $('.navbar__link, .navbar-brand').animate({
                'line-height': '20px'
            });
        }else{
            $('.navbar--rv').css('min-height', 70);
            $('.navbar__link, .navbar-brand').animate({
                'line-height': '40px'
            });
        }
        $('.navbar--rv').toggleClass('rv-material');
    }, {offset: 50});
});
