# Field View Mode
Field View Mode provides a field on an entity add/edit form that allows editors / content creators to choose the view 
mode they want to show the content in. A config form has been provided to enable and add this field, along with option
to choose which view modes to show in the dropdown field. Any entity with only default view mode is skipped from this
config form thus keeping the config form clean. After you choose the Entity bundle and its view modes to show, the field
is created and only selected options are displayed. If no options are selected, all enabled view modes for that bundle 
are displayed in the list.

**Disable Field** - If you want to disable this field and it has data, please do not uncheck from config form, as all past
data will be lost. It is better to hide the field in form view mode of that bundle. You can alternatively uncheck the
bundle and that, in turn, will delete the field, removing all saved data.

**Layout Builder** - If you are planning to use this module in conjunction with Layout Builder, please see that Layout
builder also provides similar field and both don't hand in hand. By default FVM will hide its field, however there is
an option in configuration to hide the Layout builder view mode field. The module takes care of things automagically. 

### Installation
This component can be installed using Composer. To add it to your Drupal code base:

```
composer require drupal/fvm
```
