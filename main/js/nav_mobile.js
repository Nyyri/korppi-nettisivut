$(document).ready(function() {
    $('#menu-icon').click(function() {
        $('#nav-items').toggleClass('show');
        $('#menu-icon').toggleClass('clicked');
    });
    
    $('div.overflow-container').click(function() {
        if($("#nav-items").hasClass("show")) {
            $('#nav-items').toggleClass('show');
            $('#menu-icon').toggleClass('clicked');
        }  
    });
});