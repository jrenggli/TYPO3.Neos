define(
['block/block'],
function(block) {
    "use strict";
	var exports = {};
	var $ = window.alohaQuery || window.jQuery;

	/**
	 * This is the TYPO3 AbstractBlock, which we use for all TYPO3-related functionality.
	 *
	 * We only render this block *once*,
	 */
	exports.AbstractBlock = block.AbstractBlock.extend({

		/**
		 * List of editable sub properties
		 *
		 * @var Array
		 */
		editableSubProperties: null,

		/**
		 * Already rendered?
		 *
		 * @var Boolean
		 */
		_alreadyRendered: false,

		/**
		 * Initialization. Here, we store the original values of all editables
		 * inside the block.
		 */
		init: function() {
			if (!this.editableSubProperties) return;
			this.editableSubProperties.forEach(function(propertyName) {
				this.attr(propertyName, this.$innerElement.find('*[data-propertyname="' + propertyName + '"]').html(), true);
			}, this);
		},

		/**
		 * Rendering. Only called once, aloha-fies the editable sub properties (making them Aloha Editables).
		 */
		render: function() {
			if (this._alreadyRendered) return;

			if (!this.editableSubProperties) return;
			// Aloha-fy the editable sub elements
			this.editableSubProperties.forEach(function(propertyName) {
				this.$innerElement.find('*[data-propertyname="' + propertyName + '"]').aloha();
			}, this);

			this._alreadyRendered = true;
		},

		/**
		 * We override this parent method, as we want to only render the handles,
		 * and not the inner contents (as they are pre-rendered from the server and we do not
		 * want to touch them).
		 */
		_renderSurroundingElements: function() {
			this.renderHandles();
		},

		/**
		 * Renders the Handles around the block.
		 * This function can be called very often, and must internally deal
		 * with the situation that the handles are already created.
		 */
		renderHandles: function() {
			this._renderHandle('t3-delete-handle', 'Delete element', T3.Content.Controller.BlockActions.deleteBlock, T3.Content.Controller.BlockActions);
			this._renderHandle('t3-add-above-handle', 'Add above', T3.Content.Controller.BlockActions.addAbove, T3.Content.Controller.BlockActions);
			this._renderHandle('t3-add-below-handle', 'Add below', T3.Content.Controller.BlockActions.addBelow, T3.Content.Controller.BlockActions);

			this.element.find('.t3-status-indicator').remove();
			if (this.attr('_status')) {
				// FIXME: do not output _status directly, but do it using CSS or localization.
				var statusIndicator = $('<span class="t3-status-indicator t3-status-indicator-' + this.attr('_status')  + '">' + this.attr('_status') + '</span>');
				this.element.prepend(statusIndicator);
			}
		},

		/**
		 * Helper function which renders a single handle, if it does not exist yet
		 *
		 * @param {String} cssClass CSS class for the handle
		 * @param {String} innerHTML Inner HTML for the handle
		 * @param {Function} callback the Callback function to be executed when clicking the handle. This function gets the current T3.Content.Model.Block as first parameter.
		 * @param {Object} scope the scope to use for the callback
		 */
		_renderHandle: function(cssClass, innerHtml, callback, scope) {
			if (this.element.find('.' + cssClass).length == 0) {
				var handle = $('<span class="' + cssClass + '">' + innerHtml + '</span>');
				this.element.prepend(handle);
				handle.click(function(event) {
					var nodePath = $(event.target).parent('.aloha-block').attr('about');
					var block = T3.Content.Model.BlockManager.getBlockByNodePath(nodePath);
					callback.call(scope, block);
				});
			}
		},

		/**
		 * We need some custom attribute setter as we need to update the innerHTML if an
		 * inline-editable is modified.
		 */
		_setAttribute: function(key, value) {
			// Code from superclass
			if (key === 'about') {
				this.element.attr('about', value);
			} else {
				this.element.attr('data-' + key, value);
			}

			if (!this.editableSubProperties) return;
			if (this.editableSubProperties.indexOf(key) !== -1) {
				this.$innerElement.find('*[data-propertyname="' + key + '"]').html(value);
			}
		}
	});

	// TODO: should be generic lateron.
	exports.TextBlock = exports.AbstractBlock.extend({
		title: 'Text',
		editableSubProperties: ['headline', 'text']
	});

	exports.PluginBlock = exports.AbstractBlock.extend({
		title: 'Plugin',

		getSchema: function() {
			return [
				{
					key: 'Plugin Settings',
					properties: [
						{
							key: 'package',
							type: 'string',
							label: 'Package'
						}, {
							key: 'subpackage',
							type: 'string',
							label: 'Sub Package'
						}, {
							key: 'controller',
							type: 'string',
							label: 'Controller'
						}, {
							key: 'action',
							type: 'string',
							label: 'Action'
						}
					]
				}
			];
		}
	});
	return exports;
});