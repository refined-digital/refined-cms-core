/**
 * @license Copyright (c) 2003-2020, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
 */

/**
 * @module link/ui/linkformview
 */

import View from '@ckeditor/ckeditor5-ui/src/view';
import ViewCollection from '@ckeditor/ckeditor5-ui/src/viewcollection';
import Collection from "@ckeditor/ckeditor5-utils/src/collection";
import Model from "@ckeditor/ckeditor5-ui/src/model";

import ButtonView from '@ckeditor/ckeditor5-ui/src/button/buttonview';
import SwitchButtonView from '@ckeditor/ckeditor5-ui/src/button/switchbuttonview';
import LabeledInputView from '@ckeditor/ckeditor5-ui/src/labeledinput/labeledinputview';
import InputTextView from '@ckeditor/ckeditor5-ui/src/inputtext/inputtextview';
import { createDropdown, addListToDropdown } from '@ckeditor/ckeditor5-ui/src/dropdown/utils';

import submitHandler from '@ckeditor/ckeditor5-ui/src/bindings/submithandler';
import FocusTracker from '@ckeditor/ckeditor5-utils/src/focustracker';
import FocusCycler from '@ckeditor/ckeditor5-ui/src/focuscycler';
import KeystrokeHandler from '@ckeditor/ckeditor5-utils/src/keystrokehandler';

import checkIcon from '@ckeditor/ckeditor5-core/theme/icons/check.svg';
import cancelIcon from '@ckeditor/ckeditor5-core/theme/icons/cancel.svg';
import '@ckeditor/ckeditor5-link/theme/linkform.css';

/**
 * The link form view controller class.
 *
 * See {@link module:link/ui/linkformview~LinkFormView}.
 *
 * @extends module:ui/view~View
 */
export default class LinkFormView extends View {
	/**
	 * Creates an instance of the {@link module:link/ui/linkformview~LinkFormView} class.
	 *
	 * Also see {@link #render}.
	 *
	 * @param {module:utils/locale~Locale} [locale] The localization services instance.
	 * @param {module:utils/collection~Collection} [manualDecorators] Reference to manual decorators in
	 * {@link module:link/linkcommand~LinkCommand#manualDecorators}.
	 */
	constructor( locale, manualDecorators = [] ) {
		super( locale );

		const t = locale.t;

		/**
		 * Tracks information about DOM focus in the form.
		 *
		 * @readonly
		 * @member {module:utils/focustracker~FocusTracker}
		 */
		this.focusTracker = new FocusTracker();

		/**
		 * An instance of the {@link module:utils/keystrokehandler~KeystrokeHandler}.
		 *
		 * @readonly
		 * @member {module:utils/keystrokehandler~KeystrokeHandler}
		 */
		this.keystrokes = new KeystrokeHandler();

		/**
		 * The URL input view.
		 *
		 * @member {module:ui/labeledinput/labeledinputview~LabeledInputView}
		 */
		this.urlInputView = this._createUrlInput('Link URL','');

		this.classInputView = this._createUrlInput('Class','');
		this.idInputView = this._createUrlInput('Id','');
		this.titleInputView = this._createUrlInput('Title','');
		this.additionalAttributes = [
		  this.classInputView,
      this.idInputView,
      this.titleInputView
    ];

		this.listTypes = [
	    'Internal Page',
      'External Page',
      'File / Image',
      'Email'
    ];

		this.linkTypeView = this._createListView();

		const serverConfig = {
		  label: t( 'Browser Server' ), className: 'ck-button-browse ck-button-browse-server', withText: true
    };
		const mediaConfig = {
		  label: t( 'Browser Media' ), className: 'ck-button-browse ck-button-browse-media', withText: true
    };

		this.browserServerButtonView = this._createButton( serverConfig, 'browse-server');
		this.browserMediaButtonView = this._createButton( mediaConfig, 'browse-media',);

		/**
		 * The Save button view.
		 *
		 * @member {module:ui/button/buttonview~ButtonView}
		 */
		const saveConfig = {
		  label: t( 'Save' ),
      icon: checkIcon,
      tooltip: true,
      className: 'ck-button-save'
    };
		this.saveButtonView = this._createButton( saveConfig );
		this.saveButtonView.type = 'submit';

		/**
		 * The Cancel button view.
		 *
		 * @member {module:ui/button/buttonview~ButtonView}
		 */
		const cancelConfig = {
		  label: t( 'Cancel' ),
      icon: cancelIcon,
      tooltip: true,
      className: 'ck-button-cancel'
    };
		this.cancelButtonView = this._createButton( cancelConfig, 'cancel' );

		/**
		 * A collection of {@link module:ui/button/switchbuttonview~SwitchButtonView},
		 * which corresponds to {@link module:link/linkcommand~LinkCommand#manualDecorators manual decorators}
		 * configured in the editor.
		 *
		 * @private
		 * @readonly
		 * @type {module:ui/viewcollection~ViewCollection}
		 */
		this._manualDecoratorSwitches = this._createManualDecoratorSwitches( manualDecorators );

		/**
		 * A collection of child views in the form.
		 *
		 * @readonly
		 * @type {module:ui/viewcollection~ViewCollection}
		 */
		this.children = this._createFormChildren( manualDecorators );

		/**
		 * A collection of views that can be focused in the form.
		 *
		 * @readonly
		 * @protected
		 * @member {module:ui/viewcollection~ViewCollection}
		 */
		this._focusables = new ViewCollection();

		/**
		 * Helps cycling over {@link #_focusables} in the form.
		 *
		 * @readonly
		 * @protected
		 * @member {module:ui/focuscycler~FocusCycler}
		 */
		this._focusCycler = new FocusCycler( {
			focusables: this._focusables,
			focusTracker: this.focusTracker,
			keystrokeHandler: this.keystrokes,
			actions: {
				// Navigate form fields backwards using the Shift + Tab keystroke.
				focusPrevious: 'shift + tab',

				// Navigate form fields forwards using the Tab key.
				focusNext: 'tab'
			}
		} );

		const classList = [ 'ck', 'ck-link-form', 'ck-link-form_layout-vertical'];

		this.setTemplate( {
			tag: 'form',

			attributes: {
				class: classList,

				// https://github.com/ckeditor/ckeditor5-link/issues/90
				tabindex: '-1'
			},

			children: this.children
		} );
	}

	/**
	 * Obtains the state of the {@link module:ui/button/switchbuttonview~SwitchButtonView switch buttons} representing
	 * {@link module:link/linkcommand~LinkCommand#manualDecorators manual link decorators}
	 * in the {@link module:link/ui/linkformview~LinkFormView}.
	 *
	 * @returns {Object.<String,Boolean>} Key-value pairs, where the key is the name of the decorator and the value is
	 * its state.
	 */
	getDecoratorSwitchesState() {
		return Array.from( this._manualDecoratorSwitches ).reduce( ( accumulator, switchButton ) => {
			accumulator[ switchButton.name ] = switchButton.isOn;
			return accumulator;
		}, {} );
	}

	/**
	 * @inheritDoc
	 */
	render() {
		super.render();

		submitHandler( {
			view: this
		} );

		const childViews = [
			this.urlInputView,
      this.linkTypeView,
      this.browserServerButtonView,
      this.browserMediaButtonView,
      ...this.additionalAttributes,
			...this._manualDecoratorSwitches,
			this.saveButtonView,
			this.cancelButtonView
		];

		childViews.forEach( v => {
			// Register the view as focusable.
			this._focusables.add( v );

			// Register the view in the focus tracker.
			this.focusTracker.add( v.element );
		} );

		// Start listening for the keystrokes coming from #element.
		this.keystrokes.listenTo( this.element );
	}

	/**
	 * Focuses the fist {@link #_focusables} in the form.
	 */
	focus() {
		this._focusCycler.focusFirst();
	}

	/**
	 * Creates a labeled input view.
	 *
	 * @private
	 * @returns {module:ui/labeledinput/labeledinputview~LabeledInputView} Labeled input view instance.
	 */
	_createUrlInput(label, placeholder = '') {
		const t = this.locale.t;

		const labeledInput = new LabeledInputView( this.locale, InputTextView );

		labeledInput.label = t( label );
		labeledInput.inputView.placeholder = placeholder;

		return labeledInput;
	}

	_createListView( ) {
	  const items = new Collection();
	  const labels = [...this.listTypes];
	  labels.forEach(label => {
      items.add({
        type: 'button',
        model: new Model({
          withText: true,
          label,
        })
      });
    });

	  const dropdown = createDropdown(this.locale);
	  dropdown.buttonView.set({
      label: 'Link Type',
      withText: true,
      isOn: false,
    });

	  addListToDropdown(dropdown, items);

	  dropdown.on('execute', element => {
	    const source = element.source;
	    const form = source.element.closest('form');
	    const label = source.label;
	    this._onDropdownChange(form, label);
    });

    return dropdown;
  }

  _onDropdownChange(form, label) {
    const elements = [
      form.querySelector('.ck-button-browse-server'),
      form.querySelector('.ck-text__item'),
      form.querySelector('.ck-button-browse-media')
    ];
    const labelIndex = this.listTypes.indexOf(label);

    elements.forEach((element, index) => {
      element.style.display = labelIndex === index
        ? element.classList.contains('ck-button') ? 'inline-flex' : 'block'
        : 'none';
    });

    this.linkTypeView.buttonView.label = label;

    this.urlInputView.label = label === 'Email' ? 'Email Address' : 'Link URL';
  }

	/**
	 * Creates a button view.
	 *
	 * @private
	 * @param {String} options The button label.
	 * @param {String} [eventName] An event name that the `ButtonView#execute` event will be delegated to.
	 * @returns {module:ui/button/buttonview~ButtonView} The button view instance.
	 */
	_createButton( options, eventName ) {
		const button = new ButtonView( this.locale );


		button.set( options );

		button.extendTemplate( {
			attributes: {
				class: options.className
			}
		} );

		if ( eventName ) {
			button.delegate( 'execute' ).to( this, eventName );
		}

		return button;
	}

	/**
	 * Populates {@link module:ui/viewcollection~ViewCollection} of {@link module:ui/button/switchbuttonview~SwitchButtonView}
	 * made based on {@link module:link/linkcommand~LinkCommand#manualDecorators}.
	 *
	 * @private
	 * @param {module:utils/collection~Collection} manualDecorators A reference to the
	 * collection of manual decorators stored in the link command.
	 * @returns {module:ui/viewcollection~ViewCollection} of switch buttons.
	 */
	_createManualDecoratorSwitches( manualDecorators ) {
		const switches = this.createCollection();

		for ( const manualDecorator of manualDecorators ) {
			const switchButton = new SwitchButtonView( this.locale );

			switchButton.set( {
				name: manualDecorator.id,
				label: manualDecorator.label,
				withText: true
			} );

			switchButton.bind( 'isOn' ).to( manualDecorator, 'value' );

			switchButton.on( 'execute', () => {
				manualDecorator.set( 'value', !switchButton.isOn );
			} );

			switches.add( switchButton );
		}

		return switches;
	}

	/**
	 * Populates the {@link #children} collection of the form.
	 *
	 * If {@link module:link/linkcommand~LinkCommand#manualDecorators manual decorators} are configured in the editor, it creates an
	 * additional `View` wrapping all {@link #_manualDecoratorSwitches} switch buttons corresponding
	 * to these decorators.
	 *
	 * @private
	 * @param {module:utils/collection~Collection} manualDecorators A reference to
	 * the collection of manual decorators stored in the link command.
	 * @returns {module:ui/viewcollection~ViewCollection} The children of link form view.
	 */
	_createFormChildren( manualDecorators ) {
		const children = this.createCollection();

		children.add(this.linkTypeView);

		const textView = new View();
		textView.setTemplate({
      tag: 'div',
      attributes: {
        class: [
          'ck',
          'ck-text__item'
        ]
      },
      children: [
        'Must include ',
        {
          tag: 'code',
          children: [
            'http://'
          ]
        },
        ' or ',
        {
          tag: 'code',
          children: [
            'https://'
          ]
        }
      ]
    });
		children.add(textView);

		children.add(this.browserServerButtonView);
		children.add(this.browserMediaButtonView);
		children.add( this.urlInputView );

    this.additionalAttributes.map(input => {
      children.add(input);
    });

		if ( manualDecorators.length ) {
			const additionalButtonsView = new View();

			additionalButtonsView.setTemplate( {
				tag: 'ul',
				children: this._manualDecoratorSwitches.map( switchButton => ( {
					tag: 'li',
					children: [ switchButton ],
					attributes: {
						class: [
							'ck',
							'ck-list__item'
						]
					}
				} ) ),
				attributes: {
					class: [
						'ck',
						'ck-reset',
						'ck-list'
					]
				}
			} );
			children.add( additionalButtonsView );
		}

		children.add( this.saveButtonView );
		children.add( this.cancelButtonView );

		return children;
	}
}

/**
 * Fired when the form view is submitted (when one of the children triggered the submit event),
 * for example with a click on {@link #saveButtonView}.
 *
 * @event submit
 */

/**
 * Fired when the form view is canceled, for example with a click on {@link #cancelButtonView}.
 *
 * @event cancel
 */
