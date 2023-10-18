/**
 * @file
 * Contains example JS for the example_local_assets component.
 */

(function ($, Drupal) {

  "use strict";

  /**
   * Changes the color of the example component.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.sfcExampleLocalAssets = {
    attach: function (context, settings) {
      $(once('sfc-example-local-assets', '.example_local_assets', context)).each(function () {
        $(this).click(function () {
          $(this).toggleClass('clicked');
        });
      });
    }
  };

}(jQuery, Drupal));
