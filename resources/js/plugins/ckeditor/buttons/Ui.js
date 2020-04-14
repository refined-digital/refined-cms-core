/**
 * @license Copyright (c) 2003-2020, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
 */

/**
 * @module list/listui
 */

import { createUIComponent } from './Utils';

import numberedListIcon from '@ckeditor/ckeditor5-list/theme/icons/numberedlist.svg';

import Plugin from '@ckeditor/ckeditor5-core/src/plugin';

/**
 * The list UI feature. It introduces the `'numberedList'` and `'bulletedList'` buttons that
 * allow to convert paragraphs to and from list items and indent or outdent them.
 *
 * @extends module:core/plugin~Plugin
 */
export default class ButtonsUi extends Plugin {
	/**
	 * @inheritDoc
	 */
	init() {
		const t = this.editor.t;

		createUIComponent( this.editor, 'refined:buttons', t( 'Button List' ), numberedListIcon );
	}
}
