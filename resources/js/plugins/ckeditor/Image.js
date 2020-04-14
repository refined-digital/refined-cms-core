import Plugin from '@ckeditor/ckeditor5-core/src/plugin';

import imageIcon from '@ckeditor/ckeditor5-core/theme/icons/image.svg';
import editIcon from '@ckeditor/ckeditor5-core/theme/icons/pencil.svg';
import ButtonView from '@ckeditor/ckeditor5-ui/src/button/buttonview';
import ContextualBalloon from '@ckeditor/ckeditor5-ui/src/panel/balloon/contextualballoon';

const editCommand = 'refined:imageEdit';


const saveImage = (editor, attrs) => {
  editor.model.change(writer => {
    const imageElement = writer.createElement('image', attrs);

    // Insert the image in the current selection location.
    editor.model.insertContent(imageElement, editor.model.document.selection);
  });
};

export default class RefinedImage extends Plugin {
	static get requires() {
		return [ ImageEditUI ];
	}

  init() {
	  this.editor.commands.add(editCommand, () => {});

    this.editor.ui.componentFactory.add('refined:image', locale => {
      const view = new ButtonView( locale );

      view.set({
        label: 'Insert image',
        icon: imageIcon,
        tooltip: true
      });

      // Callback executed once the image is clicked.
      view.on('execute', () => {
        eventBus.$emit('rich-editor.image.insert', { id: this.editor.id });
        eventBus.$on('rich-editor.image.save', attrs => saveImage(this.editor, attrs));
      });

      return view;
    });
  }

  afterInit() {
    this.setupCustomAttributeConversions('img', 'image', 'id');
    this.setupCustomAttributeConversions('img', 'image', 'class');
  }

  setupCustomAttributeConversions(viewElement, modelElement, attribute) {
    const modelAttribute = `${ attribute }`;

    this.editor.model.schema.extend( modelElement, { allowAttributes: [ modelAttribute ] } );

    this.editor.conversion.for( 'upcast' ).add( this.upcastAttribute( viewElement, attribute, modelAttribute ) );
    this.editor.conversion.for( 'downcast' ).add( this.downcastAttribute( modelElement, viewElement, attribute, modelAttribute ) );

  }

  upcastAttribute( viewElementName, viewAttribute, modelAttribute ) {
    return dispatcher => dispatcher.on( `element:${ viewElementName }`, ( evt, data, conversionApi ) => {
      const viewItem = data.viewItem;
      const modelRange = data.modelRange;
      const viewWriter = conversionApi.writer;

      const modelElement = modelRange && modelRange.start.nodeAfter;

      if ( !modelElement ) {
        return;
      }

      const attr = modelAttribute;
      const value = viewItem.getAttribute(viewAttribute);

      if (value) {
        viewWriter.setAttribute(attr, value, modelElement);
      } else {
        viewWriter.removeAttribute(attr, modelElement);
      }
    });
  }

  downcastAttribute( modelElementName, viewElementName, viewAttribute, modelAttribute ) {
    return dispatcher => dispatcher.on( `insert:${ modelElementName }`, ( evt, data, conversionApi ) => {
      const modelElement = data.item;

      const viewWriter = conversionApi.writer;
      const viewFigure = conversionApi.mapper.toViewElement( modelElement );
      const viewElement = this.findViewChild( viewFigure, viewElementName, conversionApi );

      if ( !viewElement ) {
        return;
      }

      const attr = viewAttribute;
      const value = modelElement.getAttribute(modelAttribute);

      if (value) {
        viewWriter.setAttribute(attr, value, viewElement);
      } else {
        viewWriter.removeAttribute(attr, viewElement);
      }
    });
  }

  findViewChild( viewElement, viewElementName, conversionApi ) {
    const viewChildren = Array.from( conversionApi.writer.createRangeIn( viewElement ).getItems() );

    return viewChildren.find( item => item.is( viewElementName ) );
  }
}

/**
 * The image text alternative UI plugin.
 *
 * The plugin uses the {@link module:ui/panel/balloon/contextualballoon~ContextualBalloon}.
 *
 * @extends module:core/plugin~Plugin
 */
export class ImageEditUI extends Plugin {
	/**
	 * @inheritDoc
	 */
	static get requires() {
		return [ ContextualBalloon ];
	}

	/**
	 * @inheritDoc
	 */
	static get pluginName() {
		return 'ImageEditUI';
	}

	/**
	 * @inheritDoc
	 */
	init() {
		this._createButton();
	}

	_createButton() {
		const editor = this.editor;
		const t = editor.t;

		editor.ui.componentFactory.add(editCommand, locale => {
			const view = new ButtonView(locale);

			view.set({
				label: t('Edit Image'),
				icon: editIcon,
				tooltip: true
			});

			this.listenTo(view, 'execute', () => {

		    const element = this.editor.model.document.selection.getSelectedElement();

		    if (element.name === 'image') {
		      const attrs = {};
		      element._attrs.forEach((attr, key) => {
		        attrs[key] = attr;
          });
		      const data = {
		        id: editor.id,
            attrs
          };
		      eventBus.$emit('rich-editor.image.open', data);
          eventBus.$on('rich-editor.image.save', attrs => saveImage(this.editor, attrs));
        }
			});

			return view;
		});
	}
}

