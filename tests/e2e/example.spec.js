import { loginUser, visitAdminPage } from '@wordpress/e2e-test-utils'

// https://www.wpgraphql.com/2022/02/17/adding-end-2-end-tests-to-wordpress-plugins-using-wp-env-and-wp-scripts
describe('example test', () => {

    it('works', () => {
        expect(true).toBeTruthy();
    });

    it('verifies the plugin is active', async () => {

        // login as admin
        await loginUser();

        // visit the plugins page
        await visitAdminPage('plugins.php');

        // Select the plugin based on slug and active class
        const activePlugin = await page.$x('//tr[contains(@class, "active") and not(contains(@class, "plugin-update-tr")) and contains(@data-slug, "wpgraphql-content-blocks")]');

        // assert that our plugin is active by checking the HTML
        expect(activePlugin?.length).toBe(1);

    });

})
