(function (Drupal, $) {
  "use strict";
  let initialized = false;
  let $container;

  /**
   * Restore the notification messages opened/closed state.
   */
  function restoreMessagesState() {
    let hasCookie = $.cookie("_notification") !== undefined;
    // let $messages = $('.notification-box.display-toggle .messages');
    if (hasCookie) {
      let cookieContent = JSON.parse($.cookie("_notification"));

      if (cookieContent.state !== undefined) {
        switch (cookieContent.state) {
          case "opened":
            $container.removeClass("messages--closed");
            $(".message-toggle").removeClass("is--closed");
            break;
          case "closed":
            $container.addClass("messages--closed");
            $(".message-toggle").addClass("is--closed");
            break;
        }
      }
    } else {
      $.cookie("_notification", JSON.stringify({ state: "opened" }));
      $container.removeClass("messages--closed");
      $(".message-toggle").removeClass("is--closed");
    }
  }

  function init() {
    if (initialized) {
      return;
    }

    // Look for messages
    $container = $("#block-alert-notification-messages .messages")
      .attr("id", "message-container")
      .attr("role", "alert");

    // Abort if there are no messages.
    if ($container.length <= 0) {
      return;
    }
    //Move the notification block for mobile/tablet after utility menu hides
    if (window.innerWidth <= 767) {
      $('<div class="l--constrained notification-box"></div')
      .prepend($container)
      .insertAfter($("#mobile-utility"));
    } else {
      // Move notification messages on desktop
      $('<div class="l--constrained notification-box"></div')
        .prepend($container)
        .insertAfter($("#utility"));
    }

    restoreMessagesState();

    $(".message-toggle")
      .attr("role", "button")
      .attr("aria-controls", "message-container")
      .once()
      .click(function () {
        $(".message-toggle")
        .toggleClass("is--closed")
        // $messages = $('#message-container');
        let messagesState = $container
          .toggleClass("messages--closed")
          .hasClass("messages--closed")
          ? "closed"
          : "opened";

        $.cookie("_notification", JSON.stringify({ state: messagesState }));
      });

    initialized = true;
  }

  Drupal.behaviors.notification_box = {
    attach: function (context, settings) {
      init();
    },
  };
})(Drupal, jQuery);
