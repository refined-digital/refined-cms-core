/**
 * @license Copyright (c) 2003-2020, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
 */

/**
 * @module link/linkcommand
 */

import Command from '@ckeditor/ckeditor5-core/src/command';
import findLinkRange from './findlinkrange';
import toMap from '@ckeditor/ckeditor5-utils/src/tomap';
import Collection from '@ckeditor/ckeditor5-utils/src/collection';

/**
 * The link command. It is used by the {@link module:link/link~Link link feature}.
 *
 * @extends module:core/command~Command
 */
export default class LinkCommand extends Command {
	/**
	 * The value of the `'linkHref'` attribute if the start of the selection is located in a node with this attribute.
	 *
	 * @observable
	 * @readonly
	 * @member {Object|undefined} #value
	 */

	constructor( editor ) {
		super( editor );

		/**
		 * A collection of {@link module:link/utils~ManualDecorator manual decorators}
		 * corresponding to the {@link module:link/link~LinkConfig#decorators decorator configuration}.
		 *
		 * You can consider it a model with states of manual decorators added to the currently selected link.
		 *
		 * @readonly
		 * @type {module:utils/collection~Collection}
		 */
		this.manualDecorators = new Collection();
	}

	/**
	 * Synchronizes the state of {@link #manualDecorators} with the currently present elements in the model.
	 */
	restoreManualDecoratorStates() {
		for ( const manualDecorator of this.manualDecorators ) {
			manualDecorator.value = this._getDecoratorStateFromModel( manualDecorator.id );
		}
	}

	/**
	 * @inheritDoc
	 */
	refresh() {
		const model = this.editor.model;
		const doc = model.document;

		this.value = doc.selection.getAttribute( 'linkHref' );
		this.additionalAttributes = {
      class: doc.selection.getAttribute('linkClass'),
      title: doc.selection.getAttribute('linkTitle'),
      id: doc.selection.getAttribute('linkId'),
    };

		for ( const manualDecorator of this.manualDecorators ) {
			manualDecorator.value = this._getDecoratorStateFromModel( manualDecorator.id );
		}

		this.isEnabled = model.schema.checkAttributeInSelection( doc.selection, 'linkHref' );
	}

	execute( attrs, manualDecoratorIds = {} ) {
		const model = this.editor.model;
		const selection = model.document.selection;
		// Stores information about manual decorators to turn them on/off when command is applied.
		const truthyManualDecorators = [];
		const falsyManualDecorators = [];
		const additionalAttributes = [
      {
        model: 'linkId',
        attr: 'id'
      },
      {
        model: 'linkClass',
        attr: 'class'
      },
      {
        model: 'linkTitle',
        attr: 'title'
      },
    ];

		for ( const name in manualDecoratorIds ) {
			if ( manualDecoratorIds[ name ] ) {
				truthyManualDecorators.push( name );
			} else {
				falsyManualDecorators.push( name );
			}
		}

		model.change( writer => {
			// If selection is collapsed then update selected link or insert new one at the place of caret.
			if ( selection.isCollapsed ) {
				const position = selection.getFirstPosition();

				// When selection is inside text with `linkHref` attribute.
				if ( selection.hasAttribute( 'linkHref' ) ) {
					// Then update `linkHref` value.
					const linkRange = findLinkRange( position, selection.getAttribute( 'linkHref' ), model );

					writer.setAttribute( 'linkHref', attrs.href, linkRange );

					additionalAttributes.forEach(attribute => {
            if (attrs[attribute.attr]) {
              writer.setAttribute(attribute.model, attrs[attribute.attr], linkRange);
            } else {
              writer.removeAttribute(attribute.model, linkRange);
            }
          });

					truthyManualDecorators.forEach( item => {
						writer.setAttribute( item, manualDecoratorIds[item], linkRange );
					} );

					falsyManualDecorators.forEach( item => {
						writer.removeAttribute( item, linkRange );
					} );

					// Create new range wrapping changed link.
					writer.setSelection( linkRange );
				}
				// If not then insert text node with `linkHref` attribute in place of caret.
				// However, since selection in collapsed, attribute value will be used as data for text node.
				// So, if `href` is empty, do not create text node.
				else if ( attrs.href !== '' ) {
					const attributes = toMap( selection.getAttributes() );

					attributes.set( 'linkHref', attrs.href );
					additionalAttributes.forEach(attribute => {
            if (attrs[attribute.attr]) {
              attributes.set(attribute.model, attrs[attribute.attr]);
            } else {
              attributes.remove(attribute.model);
            }
          });

					truthyManualDecorators.forEach( item => {
						attributes.set( item, true );
					} );

					const node = writer.createText( attrs.href, attributes );

					model.insertContent( node, position );

					// Create new range wrapping created node.
					writer.setSelection( writer.createRangeOn( node ) );
				}
			} else {
				// If selection has non-collapsed ranges, we change attribute on nodes inside those ranges
				// omitting nodes where `linkHref` attribute is disallowed.
				const ranges = model.schema.getValidRanges( selection.getRanges(), 'linkHref' );

				for ( const range of ranges ) {
					writer.setAttribute( 'linkHref', attrs.href, range );
					additionalAttributes.forEach(attribute => {
            if (attrs[attribute.attr]) {
              writer.setAttribute(attribute.model, attrs[attribute.attr], range);
            } else {
              writer.removeAttribute(attribute.model, range);
            }
          });

					truthyManualDecorators.forEach( item => {
						writer.setAttribute( item, manualDecoratorIds[item], range );
					} );

					falsyManualDecorators.forEach( item => {
						writer.removeAttribute( item, range );
					} );
				}
			}
		} );
	}

	/**
	 * Provides information whether a decorator with a given name is present in the currently processed selection.
	 *
	 * @private
	 * @param {String} decoratorName The name of the manual decorator used in the model
	 * @returns {Boolean} The information whether a given decorator is currently present in the selection.
	 */
	_getDecoratorStateFromModel( decoratorName ) {
		const doc = this.editor.model.document;
		return doc.selection.getAttribute( decoratorName ) || false;
	}
}
