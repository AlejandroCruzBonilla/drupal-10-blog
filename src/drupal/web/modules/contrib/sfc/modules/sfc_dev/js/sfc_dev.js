/**
 * @file
 * Contains all SFC Dev behaviors that don't make sense in components.
 */

(function ($, Drupal) {

  "use strict";

  /**
   * Command to refresh component assets.
   *
   * @param {Drupal.Ajax} [ajax]
   *   The ajax object.
   * @param {Object} response
   *   Object holding the server response.
   * @param {String} response.id
   *   The ID for the moderation note.
   * @param {Number} [status]
   *   The HTTP status code.
   */
  Drupal.AjaxCommands.prototype.sfc_refresh_component_assets = function (ajax, response, status) {
    // This protection is in place since reloading CSS/JS is intensive and may
    // cause bugs with Drupal behaviors that don't implement detach.
    if (!$('.js-component-auto-preview').length || !$('.js-component-auto-preview')[0].checked) {
      return;
    }
    if ($('.js-component-preview-render-html').length) {
      Drupal.detachBehaviors($('.js-component-preview-render-html')[0]);
    }
    response.assets.forEach(function (asset) {
      var url = Drupal.url(asset + '?t=' + (new Date()).getTime());
      // CSS can be easily refreshed.
      $('link[href*="' + asset + '"]').each(function () {
        this.href = url;
      });
      // JS is a little stubborn.
      $('script[src*="' + asset + '"]').each(function () {
        $(this).replaceWith($('<script>').attr('src', url));
      });
    });
  };

  /**
   * Polls for changes on the active component.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.sfcDevPollChanges = {
    attach: function (context, settings) {
      $(once('sfc-dev-poll-changes', 'body')).each(function () {
        setInterval(function () {
          var $checkbox = $('.js-component-auto-preview');
          if (!$checkbox.length) {
            return;
          }
          if (!$checkbox.data('locked') && $checkbox[0].checked) {
            $checkbox.data('locked', true);
            $.getJSON(Drupal.url('sfc/library/should-write-assets/' + $checkbox.data('component-plugin-id')), function (data) {
              if (data) {
                setTimeout(function () {
                  $checkbox.closest('form').find('.js-component-preview-button').trigger('auto_preview');
                }, 1000);
              }
              else {
                $checkbox.data('locked', false);
              }
            });
          }
        }.bind(this), 1000);
      });
    }
  };

}(jQuery, Drupal));
