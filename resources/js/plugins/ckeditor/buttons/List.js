/**
 * @license Copyright (c) 2003-2020, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
 */

/**
 * @module list/list
 */

import Editing from './Editing';
import Ui from './Ui';

import Plugin from '@ckeditor/ckeditor5-core/src/plugin';

/**
 * The list feature.
 *
 * This is a "glue" plugin that loads the {@link module:list/listediting~Editing list editing feature}
 * and {@link module:list/listui~Ui list UI feature}.
 *
 * @extends module:core/plugin~Plugin
 */
export default class RefinedButtons extends Plugin {
	/**
	 * @inheritDoc
	 */
	static get requires() {
		return [ Editing, Ui ];
	}

	/**
	 * @inheritDoc
	 */
	static get pluginName() {
		return 'Buttons';
	}
}
