// contents of main.js:
require.config({
    paths: {
        jquery:         'js/jquery',
        bootstrap:      'js/bootstrap',
        meth_left_menu: 'bundles/canaltpmeth/js/left_menu',
    },
    shim: {
        bootstrap: {
            deps: ['jquery']
        }
    }
});