/**
 * @file
 * Example test for a sfc.
 */

module.exports = {
    '@tags': ['sfc'],
    before: function (browser) {
      browser
        .drupalInstall({setupFile: __dirname + '/../ExampleNightwatchSetup.php'});
    },
    after: function (browser) {
      browser
        .drupalUninstall();
    },
    'Test that clicking the component works': (browser) => {
    browser
      // You could pass context here by appending ?sfc_test_context=[json].
      .drupalRelativeURL('/sfc_test/render_component/example_js')
      .waitForElementVisible('body', 1000)
      .assert.containsText('.example-js', 'Clicked 0 times')
      .click('.example-js')
      .assert.containsText('.example-js', 'Clicked 1 times')
      .end();
    },
};
