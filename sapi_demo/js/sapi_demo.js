/**
 * @file
 * JavaScript library for the Statistics API (sapi) module.
 */

(function ($, Drupal, drupalSettings) {

  "use strict";
  Drupal.behaviors.sapiDemoView = {
    attach: function (context, settings) {
      $(function() {
        var adminPage = drupalSettings.path.currentPathIsAdmin;
        var path = drupalSettings.path.currentPath;
        var user = drupalSettings.user.uid;

        if (!adminPage) {
          var interaction = "View";
          var entity = path.split("/");
          var entity_type = entity[0];
          var entity_id = entity[1];

          /* if no id value then don't count it */
          if(typeof entity_id !== "undefined") {

            if (entity_type === "taxonomy") {
              var action = {"action":interaction, "entity":entity_type + "_" + entity_id, "entity_id":entity[2], "user_id":user};
              console.log(action);
            }
            else {
              var action = {"action":interaction, "entity":entity_type, "entity_id":entity_id, "user_id":user};
              console.log(action);
            }

            /* send data with ajax */
            Drupal.sapi.send("Entity_interaction", action);
          }

          /*console.log("Action: " + interaction, "; Entity: " + entity_type + ":" + entity_id + "; User: " + user);*/
        }

        else {
          /* here will go update and create detection */
        }

      });

    }
  };

})(jQuery, Drupal, drupalSettings);
