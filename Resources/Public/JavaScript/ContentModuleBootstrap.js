window.T3 = {
	isContentModule: location.pathname.match(/@user-/)
} || window.T3;

requirePaths = window.T3Configuration.requirejs.paths || {};
requirePaths['Library'] = '../Library';
requirePaths['text'] = '../Library/requirejs/text';

/**
 * WARNING: if changing any of the require() statements below, make sure to also
 * update them inside build.js!
 */
require(
	{
		baseUrl: window.T3Configuration.neosJavascriptBasePath,
		urlArgs: window.localStorage.showDevelopmentFeatures ? 'bust=' +  (new Date()).getTime() : '',
		paths: requirePaths,
		context: 'neos'
	},
	[
		'emberjs',
		'Content/ContentModule',
		'Content/ApplicationView',
		'Content/Components/PublishMenu',
		'Shared/ResourceCache',
		'Shared/Notification',
		'Shared/Configuration',
		'Shared/I18n',
		'Shared/NodeTypeService',
		'Shared/HttpClient',
		'InlineEditing/PositioningHelper',
		'storage'
	],
	function(
		Ember,
		ContentModule,
		ApplicationView,
		PublishMenu,
		ResourceCache,
		Notification,
		Configuration,
		I18n
	) {
		var T3 = window.T3;

		ResourceCache.fetch(Configuration.get('VieSchemaUri'));

		// Load all localizations, and then bootstrap the Neos interface
		I18n.then(function() {
			require(
				{context: 'neos'},
				function() {
					Ember.$(document).ready(function() {
						ContentModule.bootstrap();
						ContentModule.advanceReadiness();

						// Wait until the NodeTypeService is usable by resolving the promise
						ResourceCache.getItem(Configuration.get('NodeTypeSchemaUri')).then(function() {
							ApplicationView.create().appendTo('#neos-application');
							if (window.T3.isContentModule) {
								PublishMenu.create().appendTo('#neos-top-bar-right');
							}
						});
					});
				}
			);
		});

	}
);