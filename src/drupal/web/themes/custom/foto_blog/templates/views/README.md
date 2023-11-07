// https://redcrackle.com/blog/drupal-8/theme-views-templates



Understanding Views Templates
Views default templates are located under "/core/modules/views/templates/" folder.

Each view uses minimum of two templates:

* The first template is "views-view.html.twig". This template is used for all views and contains the layout for the view. (view content, header, footer, exposed form and attachments)
* The second template is the style template. The default used template will vary based on the applied view style (grid, table, html list or unformatted).

		* Grid: views-view-grid.html.twig
		* Table: views-view-table.html.twig
		* HTML List: views-view-list.html.twig
		* Unformatted: views-view-unformatted.html.twig

* The third template is "views-view-fields.html.twig". This template is used only if the view row style is set to "Fields". This template is responsible for looping through available fields and print fields wrappers, labels and markup.
* The fourth template is "views-view-field.html.twig". This template is used only if the view row style is set to "Fields". This is the last template and is responsible for printing each field markup.


Naming Views Templates
Each type of the view templates above can be overridden with a variety of names. The template name is a concatenation of (base template name, view machine name, view display type and view display id - separated by 2 hyphens "--").

The following are the possible template names sorted by precedence:

 * [base template name]--[view machine name]--[view display id].html.twig
 * [base template name]--[view machine name]--[view display type].html.twig
 * [base template name]--[view display type].html.twig
 * [base template name]--[view machine name].html.twig
 * [base template name].html.twig

For example; If we want to override "views-view.html.twig" template for our view, the following template names are valid:

	* [base template name]--[view machine name].html.twig
	* views-view--articles-accordion--page.html.twig
	* views-view--page.html.twig
	* views-view--articles-accordion.html.twig
	* views-view.html.twig


1. views-view--foobar.html.twig
2. views-view-unformatted--foobar.html.twig
3. views-view-fields--foobar.html.twig