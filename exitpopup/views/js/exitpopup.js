var ep_show = 0;
$( document ).mouseleave(function() {
    ep_show = 1;
    $('.ep_overlay').hide();
    $('.ep_overlay').css('visibility','visible');
    $('.ep_overlay').fadeIn();
});
$( document ).mouseover(function() {
    if(ep_show == 1){
        $('.ep_overlay').fadeOut();
    }
    ep_show = 0;
});