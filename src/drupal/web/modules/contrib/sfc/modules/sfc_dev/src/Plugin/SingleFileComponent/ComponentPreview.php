<?php

namespace Drupal\sfc_dev\Plugin\SingleFileComponent;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\sfc\ComponentBase;
use Drupal\sfc\ComponentNameHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Renders a preview interface for testing out a component..
 *
 * @SingleFileComponent(
 *   id = "sfc_dev_component_preview"
 * )
 */
class ComponentPreview extends ComponentBase {

  const TEMPLATE = <<<TWIG
  <div class="component-preview js-component-preview">
    {% if component %}
    <div class="js-component-wrapper">
      <div class="component-preview__header">
        <div class="component-preview__label">{% trans %}Preview for {{ component.getId() }}{% endtrans %}</div>
        <div class="component-preview__view-template">
            <a href="{{ path('sfc_dev.view_template', {'plugin_id': component.getPluginId()}) }}" class="use-ajax" data-dialog-type="modal" data-dialog-options='{"width":"80%","height":"80%"}'>
                {{ 'View template' | t }}
            </a>
        </div>
      </div>
      <div class="component-preview__wrapper">
          <div class="component-preview__render-wrapper js-component-preview-render-wrapper">
            <div class="component-preview__render js-component-preview-render">
              <div class="js-component-preview-render-html">
                {{ preview }}
              </div>
            </div>
            <a href="" class="js-component-preview-fullscreen component-preview__button">{{ 'View fullscreen' | t }}</a>
          </div>
          <div class="component-preview__input">
              {{ preview_form }}
          </div>
      </div>
    </div>
    {% else %}
    <div class="component-preview__header">Select a component to get started.</div>
    {% endif %}
</div>
TWIG;

  const CSS = <<<CSS
.component-preview {
    line-height: 1.5;
}
.component-preview__wrapper {
    display: flex;
    flex-wrap: wrap;
    margin: 15px 25px;
}
.component-preview__button,
input[type="submit"].component-preview__button {
    display: inline-block;
    margin: 0;
    margin-top: 10px;
    font-size: 14px;
    line-height: 24px;
    font-family: var(--component-library-font-family);
    text-decoration: none;
    background: var(--component-library-dark-bg);
    color: var(--component-library-light-text);
    padding: 10px 20px;
    border-radius: 0;
    border: none;
    transition: .2s background;
}
.component-preview__button:hover,
.component-preview__button:focus,
.component-preview__button:active,
input[type="submit"].component-preview__button:hover,
input[type="submit"].component-preview__button:focus,
input[type="submit"].component-preview__button:active {
    text-decoration: none;
    background: var(--component-library-dark-bg-hover);
    color: var(--component-library-light-text);
}
.component-preview__render-wrapper {
    margin-bottom: 15px;
}
.component-preview__render-wrapper.fullscreen {
    position: fixed;
    left: 0;
    top: 0;
    z-index: 999;
    background: white;
    width: calc(100% - 20px);
    height: 100%;
    padding: 10px;
    overflow: scroll;
}
.component-preview__header {
    flex-basis: 100%;
    font-family: var(--component-library-font-family);
    margin: 15px 25px;
    display: flex;
    flex-wrap: wrap;
    align-items: baseline;
}
.component-preview__label {
    font-size: 20px;
}
.component-preview__view-template {
    margin-left: 10px;
}
.component-preview__view-template a:hover,
.component-preview__view-template a:focus,
.component-preview__view-template a:active {
    color: var(--component-library-dark-bg-hover);
    text-decoration: underline;
    border: 0;
}
.component-preview__render-wrapper {
    margin-right: 25px;
}
.component-preview__render {
    padding: 20px;
    border: 1px dashed #a7a7a7;
}
.component-preview__input .form-item {
  margin: 0;
  margin-bottom: 10px;
}
.component-preview__input .messages__wrapper {
    padding: 0;
    margin: 0;
    margin-bottom: 10px;
}
.component-preview__input .form-item label,
.component-preview__view-template a,
.component-preview__view-template a:visited {
    margin: 0;
    color: var(--component-library-dark-bg-hover);
    font-size: 14px;
    padding-bottom: 0;
    font-weight: normal;
    text-decoration: none;
    border: 0;
}
.component-preview__input .form-textarea-wrapper {
    background: #f9f9f9;
    margin-bottom: 5px;
    padding-bottom: 0;
    width: 400px;
}
.component-preview__input input[type="submit"] {
    width: 100%;
}
.component-preview__input textarea,
.component-preview__input textarea.form-textarea {
    background: #f9f9f9;
    height: 200px;
    font-size: 12px;
    font-family: monospace;
    border: none;
    color: black;
    display: block;
    background: none;
    padding: 10px;
    border-bottom: 2px solid gray;
    transition: .2s border;
}
.component-preview__input textarea:focus {
    border: none;
    outline: none;
    border-bottom: 2px solid var(--component-library-dark-bg);
}
CSS;

  const SELECTOR = '.js-component-wrapper';

  const ATTACH = <<<JS
var tabbingContext;
function toggleFullScreen(wrapper, button) {
  wrapper.toggleClass('fullscreen');
  var previewRender = wrapper.find('.js-component-preview-render');
  if (wrapper.hasClass('fullscreen')) {
    wrapper.attr('tabindex', 0);
    button.text(Drupal.t('Exit fullscreen'));
    tabbingContext = Drupal.tabbingManager.constrain(wrapper);
    previewRender.data('original-style', previewRender.attr('style') || "");
  }
  else {
    wrapper.attr('tabindex', false);
    button.text(Drupal.t('View fullscreen'));
    if (tabbingContext) {
      tabbingContext.release();
    }
    previewRender.attr('style', previewRender.data('original-style'));
  }
  button.focus();
}
$(this).find('.js-component-preview-render').resizable();
// Allow escape key to exit modal.
$(this).find('.js-component-preview-render-wrapper').keyup(function (e) {
  if (e.keyCode === 27) {
    if ($(this).hasClass('fullscreen')) {
      toggleFullScreen($(this), $(this).find('.js-component-preview-fullscreen'));
    }
    previewRender.attr('style', previewRender.data('original-style'));
  }
});
$(this).find('.js-component-preview-fullscreen').on('click', function (e) {
  e.preventDefault();
  var wrapper = $(this).closest('.js-component-wrapper').find('.js-component-preview-render-wrapper');
  toggleFullScreen(wrapper, $(this));
});
JS;

  const DEPENDENCIES = [
    'core/jquery.ui.resizable',
    'core/drupal.dialog.ajax',
    'core/jquery.form',
    'core/drupal.tabbingmanager',
  ];

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * Constructs a ComponentPreview object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param bool $debug
   *   If debug markup should be added to templates.
   * @param string $app_root
   *   The app root.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, $debug, $app_root, FileSystemInterface $file_system, FormBuilderInterface $form_builder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $debug, $app_root, $file_system);
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      static::getDebugParameter($container),
      $container->getParameter('app.root'),
      $container->get('file_system'),
      $container->get('form_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function prepareContext(array &$context) {
    if (isset($context['component'])) {
      $context['preview_form'] = $this->formBuilder->getForm('\Drupal\sfc_dev\Form\ComponentPreviewForm', $context['component']);
      $context['preview'] = [
        '#type' => 'inline_template',
        '#template' => '{% include "' . addcslashes(ComponentNameHelper::getTemplateName($context['component']), "'") . '.html.twig" %}',
      ];
    }
  }

}
