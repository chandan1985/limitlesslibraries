jQuery(document).ready(function(){var e=jQuery(".node-id--6 .whole-desc-content").outerHeight(),e=($(".field--name-field-database-reference .field--item:nth-last-child(-n+3) .whole-desc-content").css({height:e}),jQuery(".node-id--17 .whole-desc-content").outerHeight());$(".database-listing.view.view-databases .view-content:nth-last-child(-n+3) .whole-desc-content").css({height:e}),jQuery("#mobile-menu").click(function(){$("#block-limitlesslibraries-main-menu").toggle("opened"),$("#mobile-menu").toggleClass("menu-opened")}),$(".close-btn img.closed").click(function(){$.cookie("notification","notification message"),$(".close-btn").addClass("closed"),$(".notification-messages").css({display:"block"})}),$(".close-btn img.opened").click(function(){$(".close-btn").removeClass("closed"),$(".notification-messages").css({display:"none"}),$.removeCookie("notification")}),$.cookie("notification")&&($(".notification-messages").css({display:"block"}),$(".close-btn").addClass("closed"),(e=new Date).setTime(e.getTime()+864e5),$.cookie("notification","notification message",{expires:e})),jQuery(".paragraph--type--catalog-topic-grid .field--name-field-title").wrapInner("<span></span>"),jQuery(".paragraph--type--catalog-topic-grid .field--name-field-title").wrapInner("<h2></h2>"),jQuery(".paragraph--type--database-feature-list-sortable .field--name-field-title").wrapInner("<span></span>"),jQuery(".paragraph--type--database-feature-list-sortable .field--name-field-title").wrapInner("<h2></h2>")});