import Plugin from '@ckeditor/ckeditor5-core/src/plugin';

import ButtonView from '@ckeditor/ckeditor5-ui/src/button/buttonview';

import linkIcon from '@ckeditor/ckeditor5-link/theme/icons/link.svg';

import LinkEditing from './Editing';//@ckeditor/ckeditor5-link/src/linkediting';
import LinkUI from './Ui';

export default class Link extends Plugin {
	/**
	 * @inheritDoc
	 */
	static get requires() {
		return [
		  LinkEditing,
      LinkUI
    ];
	}

	/**
	 * @inheritDoc
	 */
	static get pluginName() {
		return 'Link';
	}
}
