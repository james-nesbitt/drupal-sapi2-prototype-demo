/**
 * @file
 * JavaScript library for the Statistics API (sapi) module.
 */

(function ($, Drupal) {

  "use strict";
  Drupal.behaviors.sapiDemoView = {
    attach: function (context) {
      $(function() {
        var action = $("link[rel='shortlink']").attr("href");
        console.log(action);
      });

    }
  };

})(jQuery, Drupal);
