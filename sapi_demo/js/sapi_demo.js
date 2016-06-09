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
        }

        else {
          /* here will go update and create detection */
          var rest = path.substring(0, path.lastIndexOf("/") + 1);
          var last = path.substring(path.lastIndexOf("/") + 1, path.length);
          var entity = rest.split("/");
          var entity_type = entity[0];
          if(last === "edit") {
            $("#edit-submit, ul[data-drupal-selector$='edit-save'] .form-submit").click(function() {
              var interaction = "Update";
              var entity_id = entity[1];
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
            });
          }
        }

      });

    }
  };

})(jQuery, Drupal, drupalSettings);
