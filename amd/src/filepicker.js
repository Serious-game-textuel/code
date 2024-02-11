/**
 * get les informations du fichier userfile
 *
 * @module    mod_serioustextualgame/filepicker
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(["jquery", "core/ajax", "core/notification"], function (
  $,
  Ajax,
  Notification
) {
  return {
    init: function () {
      $(document).on("change", "#id_userfile", function () {
        var draftitemid = $(this).val();

        var request = {
          methodname: "mod_serioustextualgame_get_file_info",
          args: {
            draftitemid: draftitemid,
          },
        };

        Ajax.call([request])[0]
          .done(function (result) {
            var files = result.files;
            if (files.length > 0) {
              var file = files[0];
              console.log(file);
            }
          })
          .fail(Notification.exception);
      });
    },
  };
});
