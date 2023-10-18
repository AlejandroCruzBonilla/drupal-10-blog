# Single File Components

This project allows developers to provide frontend components in a single file.
These components contain Twig, CSS, JS, and PHP code which acts similar to a
preprocess function.

The purpose of this module is to combine traditional Drupal theming with some
elements of component based design.

## Writing components

To define a component, create an `.sfc` file in any module or theme's
`components` directory. This file format is heavily inspired by Vue.js' `.vue`
files. An `.sfc` file looks like a HTML/PHP file, here's what one may look
like:

```html
<style>
  .big-text {
    font-size: 100px;
  }
</style>

<template>
  <div class="big-text">{{ text }}</div>
</template>
```

This will be parsed into a component plugin that can be used like any other
twig template. Note that the filename determines the ID for the component, so a
file named `big_text.sfc` could be included as a template with:

```twig
{% include "sfc--big-text.html.twig" with {"text": "This is big!"} %}
```

Here are all the things you can provide from a `*.sfc` component:

```html
<style>/* Styles go here. */</style>

<script>/* Full-page JS goes here. */</script>

<script data-type="attach">/* JS for your component goes here. */</script>

<script data-type="detach">/* JS for your component goes here. */</script>

<template>{# Twig goes here. #}</template>

<?php
$prepareContext = function (&$context) {} // Prepare context for your template.
$selector = ''; // Selector for attach/detach. Defaults to [data-sfc-id="pluginid"].
$dependencies = []; // Library dependencies.
$definition = []; // Additions to the derived plugin definition.
$library = []; // A library definition if your CSS/JS are in different files.
// Form methods for this component.
$buildContextForm = function (array $form, FormStateInterface $form_state, array $default_values = []) {}
$validateContextForm = function (array $form, FormStateInterface $form_state) {}
$submitContextForm = function (array $form, FormStateInterface $form_state) {}
// Actions for this component.
$actions[...] = function () {}
```

## Using your component

Components can be included or extended in Twig templates like any other
template. The name of your component's template is `sfc--plugin-id.html.twig`,
where underscores in your plugin ID are converted to dashes. For example, the
"say_hello" plugin could be included like:

```twig
{% include "sfc--say-hello.html.twig" %}
```

Or to pass data, like:

```twig
{% extend "sfc--say-hello.html.twig" with {'name': 'Sam'} %}
```

You can also use components in render arrays with the `sfc` element:

```
$build['say_hello'] = [
  '#type' => 'sfc',
  '#component_id' => 'say_hello',
  '#context' => [
    'name' => 'Sam',
  ],
];
```

The `sfc` element is just a nice wrapper around the `inline_template` element.

### Aliasing component templates

Components can also provide aliases to make it easier to include them. Aliases
are defined in the component definition using the key "aliases".

Here's what this looks like:

```php
<?php
$definition['aliases'] = [
  'fancy/button',
  'fancy-button',
];
```

### Overriding theme hooks

While some components are abstracted from their use case, sometimes components
have a closer relationship with Drupal theme hooks.

In these cases, you can define the "overrides" key in the component definition,
which lists theme hooks that this component will override.

For example, this would let a `.sfc` component take over the template for
articles:

```php
<?php
$definition['overrides'] = [
  'node__article',
];
```

Typically, the theme hook is the normal template name with dashes converted to
underscores. For example, the theme hook for `field--node--created.html.twig`
is `field__node__created`.

## Notes on CSS and libraries

When that component is rendered, the template will be used to render HTML, and
CSS and JS will be written to temporary files which are included like any
traditional Drupal library. Relative `url()` rules in CSS will be prefixed with
the providing module's path.

If your component has a library dependency, you can set `$dependencies` to an
array of library names.

If you prefer to have CSS/JS in a separate file, there are two options:

1. Create a `component_id.css` or `component_id.js` file in the same directory
as your `component_id.sfc` file. These will be auto-included in a library
definition and loaded when your component renders.

Some library dependencies will be included based on the contents of your JS
file, for instance if `jQuery` is found, `core/jquery` will be added as a
dependency. Additional dependencies can be set by setting
`$library['dependencies']` in the PHP section of yoru `.sfc` file.

See modules/sfc_example/example_local_assets.sfc/css/js for an example.

2. Set `$library` to an array that represents a Drupal library. This library
will be included automatically where your template is used, so there's no need
to use `{{ attach_library() }}`.

Any and all combinations of defining libraries and CSS/JS should work.

## Quickly defining behavior attachments

In Drupal, JavaScript that binds behavior to elements is supposed to do so
inside a behavior attachment, using the context passed to it, and is then even
expected to use the once plugin to prevent common problems like double binding
event handlers.

This can become quite monotonous, so as an alternative to using `<script>` to
define global JS, you can instead provide `<script data-type"attach">` and
`<script data-type="detach">`, which contain JS that is ran with `this` bound
to the element defined with `$selector`.

Note that `$selector` defaults to `[data-sfc-id="<plugin_id>"]`, so you can add
that attribute to an element in your Twig template to have it be selected.

Here's an example way to use them:

```php
<template>
  <button data-sfc-id="click_counter">Clicked 0 times</button>
</template>

<script data-type="attach">
  var count = 0;
  $(this).on('click', function () {
    $(this).text('Clicked ' + ++count + ' times');
  });
</script>
```

The above will result in an output file that
looks something like this:

```js
(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.sfc_click_counter = {
    attach: function attach(context, settings) {
      $('[data-sfc-id="click_counter"]', context).once('sfcAttach').each(function () {
var count = 0;
$(this).on('click', function () {
  $(this).text('Clicked ' + ++count + ' times');
});
      });
    },
  }
})(jQuery, Drupal, drupalSettings);
```

If you want to avoid writing out `data-sfc-id="..."` in all your components,
you can use `{{ sfc_attributes }}` instead. This includes that data attribute
as well as `data-sfc-unique-id`, which is a string that is unique to this
render of the component.

Drupal Core has recently started moving away from jQuery, so if you'd like to
use vanilla JS with your attachments you can add the "data-vanilla" attribute
to your script tag.
Note that when using vanilla JS, "element" refers to your current element, not
"this". For example:

```php
<template>
  <button data-sfc-id="click_counter">Clicked 0 times</button>
</template>

<script data-type="attach" data-vanilla>
  var count = 0;
  element.onclick = function () {
    this.innerText = 'Clicked ' + ++count + ' times';
  };
</script>
```

## Adding backend behavior with actions

If your component needs to take some action in the backend - for example submit
a form or make an AJAX call, you can use actions.

Actions are callbacks that function like Controllers, returning either a
Symfony response or a render array, and are very useful for small pieces of
functionality.

To define an action, set `$actions['action_name']` in your `*.sfc` file to a
function. Your Twig template will then have `sfc_actions.action_name` in its
context, which is a URL to your action. You can use this URL for form
actions, AJAX links (`<a href="..." class="use-ajax">`), or however else you
would normally use a link to a custom Controller in Drupal.

Here's an example where a component takes in a form submit and reflects input
back to the user:

```php
<template>
  <form id="example-actions-form" action="{{ sfc_actions.submit }}" method="post">
    <label for="example-actions-text">Text</label>
    <input type="text" name="text" id="example-actions-text" required />
    <input type="submit" value="Submit" />
  </form>
</template>

<?php

use Symfony\Component\HttpFoundation\Request;

$actions['submit'] = function (Request $request) {
  return [
    '#plain_text' => 'You submitted: ' . $request->request->get('text', ''),
  ];
};
```

You may notice this action takes in the current request as an argument - see
the "Autowiring" section below for details on how that works.

In a real implementation, "submit" would likely write to the database and
perform a redirect to another page. Action URLs aren't the most user-friendly
thing to see. For more examples, see the sfc_example module.

### Action security

All action URLs require CSRF tokens, which require users to have a session. If
you want to let anonymous users perform actions, you will need to start a
session for them. The `ComponentController` supports this if you pass
`anon_session: 'TRUE'` in the `defaults` section of your route.

Beyond all that, you are responsible for performing access checking in your
action functions.

### Making AJAX easier to handle

Action URLs also come with the query param `sfc_unique_id`, which is useful
for AJAX callbacks if you are also using `{{ sfc_attributes }}` in your
template. In your callback you can do something like:

```php
use Drupal\Core\Ajax\AjaxResponse;
use Symfony\Component\HttpFoundation\Request;

$actions['do_ajax'] = function (Request $request) {
  $unique_id = (string) $request->query->get('sfc_unique_id', '');
  $selector = '[data-sfc-unique-id="' . $unique_id . '"]';
  $response = new AjaxResponse();
  // Add commands that target $selector here...
  return $response;
};
```

And know that you are targeting the correct instance of this component.

## Autowiring / dependency injection in callbacks

Any callback in a `*.sfc` file can take advantage of dependency injection
instead of calling `\Drupal::service()`. To do so, add an argument with the
class or interface you want to have access to, and the plugin deriver will do
all the work to figure out what service best matches that argument.

For example:

```php
use Drupal\Core\Session\AccountProxyInterface;

$prepareContext = function (array &$context, AccountProxyInterface $current_user) {
  $context['user_id'] = $current_user->id();
}
```

Would inject the `current_user` service as the second argument. Some services
implement the same interface or have the same class. In those cases, if you
match the ID of the service exactly, replacing `.` with `_`, the correct
service will be injected. For example, `\Drupal\Core\KeyValueStore\KeyValueFactoryInterface $keyvalue`
will match the `keyvalue` service even though many other services implement
that interface.

## Component caching

Cache metadata for components is collected/bubbled when their template is
rendered. SFC provides two features to make caching easier.

Inside your template, you can pass cache tags, cacheable objects, or arrays of
cache tags and cacheable objects to the `sfc_cache` function.

For example, if you were rendering a node title, you may write:

```twig
{{ node.label() }}
{{ sfc_cache(node) }}
```

Or for an array of nodes:

```twig
{% for node in nodes %}
  ...
{% endif %}
{{ sfc_cache(nodes) }}
{{ sfc_cache('node_list') }}
```

To add cache contexts, pass "contexts" as the second arg to `sfc_cache`. To set
a max age, pass "max-age".

Inside of your `prepareContext` callback, you can also set the `cache` Twig
context variable, which is automatically rendered in the compiled template
if present.

For example, to add a cache context to a component, you could write:

```php
public function prepareContext(array &$context) {
  $context['cache']['#cache']['contexts'][] = 'user';
}
```

Although it may be easier to add cache metadata to a render array already
passed in `&$context`, since some caching is conditional.

## Using components as blocks, layouts, and more

Components can quickly provide a block and/or layout plugin by adding special
configuration to their plugin definition, provided by `$definition`.

For blocks, add the `block` key, which should contain an array of block plugin
definition values you need. For layouts, use the `layout` key, for field
formatters, use the `field_formatter` key.

Here's an example of providing a block from a component:

```php
<template>
  Hello block!
</template>

<?php

$definition['block']['admin_label'] = 'Say hello';
```

Components can also implement form methods to be used in plugin configuration
forms, or in the sfc_dev component library.

Note that the values from your form will be passed as context to your template,
so be sure to properly validate those values and treat them as untrusted input.

### Notes for field formatters

When used as a field formatter, the `validateContextForm` and
`submitContextForm` functions will not be called. Normally your component will
be rendered for each item in the field, with field item properties passed as
context to keep your component agnostic to how it's used.

If your component is meant to handle a single item, your template will receive
`item` (of type `\Drupal\Core\Field\FieldItemInterface`) and `langcode` as
context.

If your component is meant to handle multiple items, like a slider or a list,
add `$definition["field_formatter"]["sfc_multiple"] = TRUE`. Then your template
will receive `items` (of type `\Drupal\Core\Field\FieldItemListInterface`) and
`langcode` as context.

### Notes for layouts

When used as a layout, make sure you use the `attributes` and
`region_attributes` variables in your template, to stay compatible with modules
like Layout Builder.

## Extending components

Themes can extend components by:

### Overriding the Twig template

By creating a Twig template file in your `templates/` directory with the same
name as what you use to include your component, ex: `sfc--say-hello.html.twig`,
you can override its Twig template.

If you override a component's template, you will probably need to prepend your
template with the following code:

```twig
{{ sfc_prepare_context('plugin_id') }}
{{ attach_library('sfc/component.plugin_id') }}
```

Where "plugin_id" is the original plugin ID for the component you're
overriding. This code is normally added automatically for convenience sake.

Note that due to core's Twig theme engine, you can only override components
that are included in the format `sfc--plugin-id.html.twig`. Components included
without the `.html.twig` extension or using an alias will not be recognized.

### Overriding CSS/JS

Using the `libraries-override` key in your theme's `.info.yml` file, you can
override any component's CSS or JS. The library name format for a component is
`sfc/component.plugin_id`.

Modules can extend components by:

### Extending the class

Since single file components are PHP classes, you can extend them and override
any changes you'd like. If you use the same plugin ID as an existing component,
the behavior is unclear but your class may take precedence. See
[this issue](https://www.drupal.org/project/drupal/issues/1879496) for details.

### Changing information for existing components

To change the plugin ID or class you can use the plugin alter hook, which is
`hook_single_file_component_alter`. Here's an example of changing a component's
class:

```php
<?php

function my_module_single_file_component_alter(&$info) {
  $info['say_hello']['class'] = 'Drupal\my_module\ScreamHello';
}
```

## Writing a component class

Single File Components are plugins, and can also be defined by creating a new
file in your module's `src/Plugin/SingleFileComponent` directory that contains
a class that extends [\Drupal\sfc\ComponentBase] or otherwise implements
[\Drupal\sfc\ComponentInterface].

A simple component may look like this:

```php
<?php

namespace Drupal\modulename\Plugin\SingleFileComponent;

use Drupal\sfc\ComponentBase;

/**
 * Contains an example single file component.
 *
 * @SingleFileComponent(
 *   id = "say_hello",
 * )
 */
class SayHello extends ComponentBase {

  const TEMPLATE = <<<TWIG
<p class="say-hello">Hello {{ name }}!</p>
TWIG;

  const CSS = <<<CSS
.say-hello {
  color: pink;
}
CSS;

  const JS = <<<JS
console.log('hi');
JS;

  /**
   * {@inheritdoc}
   */
  public function prepareContext(array &$context) {
    if (!isset($context['name'])) {
      $context['name'] = \Drupal::currentUser()->getDisplayName();
    }
  }

}
```

Classes may look a little ugly, but are useful for components with a lot of
PHP.

## Defining inline assets that need a compilation step

By default, single file components provide vanilla CSS/JS exactly as they'll
appear in the browser, but you may prefer to use Saas or modern/modular JS
instead.

In this case, you should put `.sfc` files that need a compilation step into an
alternate directory like `components_src`, and use a task runner like Gulp to
replace inline Sass/ES6/etc. with the compiled version.

This is less complicated than you may think, and the sfc_example module
provides an example component and [gulpfile.js] configuration that minimally
compiles inline assets using Babel and node-sass.

Alternatively, someone clever could make a module that provides a base
component that uses [scssphp] to compile Saas to CSS as its requested. This may
have performance implications but could be cool!

## Development tips

### Using the sfc_dev sub-module

To help with debugging and testing components, you can enable the sfc_dev
sub-module. Currently the main feature it provides is a component library,
which is accessible at `/sfc/library` if you have the `use sfc dev` permission.

If you want to filter down to a specific component group, you can visit
`/sfc/library/{group}`, where `{group}` is a case-sensitive search string.

Since this user interface allows you to enter Twig, the `use sfc dev`
permission should only be given to trusted users.

### Disabling cache

As you write CSS, JS, and Twig, you may grow tired of rebuilding cache. To
disable the caching used by this module, add this code to your site's
`services.yml` file:

```yml
parameters:
  twig.config:
    debug: true
```

and this code to your site's `settings.php` file:

```php
$settings['cache']['bins']['data'] = 'cache.backend.null';
```

This should add debugging output to your component's HTML, and disable caching
so that you can just reload your browser to see new changes.

## Watching for component asset changes

As an alternative to disabling the data cache bin, you can run the
`drush sfc:watch` command to watch for changes in all available components and
automatically write assets, or `drush sfc:write <plugin id>` to target a
specific plugin.

If you want to automatically refresh the page when `sfc:watch` writes a
component's assets, you can add this setting to settings.php:

```
$settings['sfc_watch_refresh'] = TRUE;
```

and rebuild cache. This will add a naive bit of JavaScript to each page that
checks for the last time any component was written and refresh if it's out of
date. **Do not enable this in production**.

## Testing components

Since single file components are PHP classes, they can be unit and integration
tested.

If you're writing kernel tests, you can use the
[\Drupal\Tests\sfc\Kernel\ComponentTestTrait] trait which adds helper methods
for rendering components.

For examples of integration testing and mocking, see
[\Drupal\Tests\sfc_example\Kernel\ExampleClassTest].

If you're writing functional javascript tests, you can use the
[\Drupal\Tests\sfc\Functional\FunctionalComponentTestTrait] trait which adds a
helper method that makes visiting a page with your component on it very easy.

For examples of javascript testing, see
[\Drupal\Tests\sfc_example\FunctionalJavascript\ExampleJsTest], or for
Nightwatch see [modules/sfc_example/tests/src/Nightwatch/Tests/exampleTest.js].
With Nightwatch it should be hypothetically possible to also run accessibility
tests but for now that's hypothetical.

[\Drupal\sfc\ComponentInterface]: src/ComponentInterface.php
[\Drupal\sfc\ComponentBase]: src/ComponentBase.php
[\Drupal\Tests\sfc\Kernel\ComponentTestTrait]: tests/src/Kernel/ComponentTestTrait.php
[\Drupal\Tests\sfc_example\Kernel\ExampleClassTest]: modules/sfc_example/tests/src/Kernel/ExampleClassTest.php
[\Drupal\Tests\sfc\Functional\FunctionalComponentTestTrait]: tests/src/Functional/FunctionalComponentTestTrait.php
[\Drupal\Tests\sfc_example\FunctionalJavascript\ExampleJsTest]: modules/sfc_example/tests/src/FunctionalJavascript/ExampleJsTest.php
[modules/sfc_example/tests/src/Nightwatch/Tests/exampleTest.js]: modules/sfc_example/tests/src/Nightwatch/Tests/exampleTest.js
[scssphp]: https://scssphp.github.io/scssphp/
[gulpfile.js]: modules/sfc/modules/sfc_example/gulpfile.js
