jQuery(document).ready(function () {
    var height = jQuery(".node-id--6 .whole-desc-content").outerHeight();
    $('.field--name-field-database-reference .field--item:nth-last-child(-n+3) .whole-desc-content').css({ 'height': height });
    var height = jQuery(".node-id--17 .whole-desc-content").outerHeight();
    $('.database-listing.view.view-databases .view-content:nth-last-child(-n+3) .whole-desc-content').css({ 'height': height });
    jQuery("#mobile-menu").click(function () {
        $("#block-limitlesslibraries-main-menu").toggle("opened");
        $("#mobile-menu").toggleClass("menu-opened");
    });
    $(".close-btn img.closed").click(function(){
       $.cookie("notification", "notification message");
        $(".close-btn").addClass("closed");
        $('.notification-messages').css({ 'display': "block" });
      });
    $(".close-btn img.opened").click(function(){
        $(".close-btn").removeClass("closed");
        $('.notification-messages').css({ 'display': "none" });
        $.removeCookie("notification");        
    });
    if ($.cookie("notification")){
        $('.notification-messages').css({ 'display': "block" });
        $(".close-btn").addClass("closed");
        var date = new Date();
        var minutes = 1440;
        date.setTime(date.getTime() + (minutes * 60 * 1000));
        $.cookie("notification", "notification message", { expires: date });
    }
    jQuery('.paragraph--type--catalog-topic-grid .field--name-field-title').wrapInner('<span></span>');
    jQuery('.paragraph--type--catalog-topic-grid .field--name-field-title').wrapInner('<h2></h2>');
    jQuery('.paragraph--type--database-feature-list-sortable .field--name-field-title').wrapInner('<span></span>');
    jQuery('.paragraph--type--database-feature-list-sortable .field--name-field-title').wrapInner('<h2></h2>');
});
