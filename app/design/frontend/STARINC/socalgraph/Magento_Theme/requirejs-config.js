/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    map: {
        '*': {
            'rowBuilder':             'Magento_Theme/js/row-builder',
            'toggleAdvanced':         'mage/toggle',
            'translateInline':        'mage/translate-inline',
            'sticky':                 'mage/sticky',
            'tabs':                   'mage/tabs',
            'zoom':                   'mage/zoom',
            'collapsible':            'mage/collapsible',
            'dropdownDialog':         'mage/dropdown',
            'dropdown':               'mage/dropdowns',
            'accordion':              'mage/accordion',
            'loader':                 'mage/loader',
            'tooltip':                'mage/tooltip',
            'deletableItem':          'mage/deletable-item',
            'itemTable':              'mage/item-table',
            'fieldsetControls':       'mage/fieldset-controls',
            'fieldsetResetControl':   'mage/fieldset-controls',
            'redirectUrl':            'mage/redirect-url',
            'loaderAjax':             'mage/loader',
            'menu':                   'mage/menu',
            'popupWindow':            'mage/popup-window',
            'validation':             'mage/validation/validation',
            'welcome':                'Magento_Theme/js/view/welcome',
            'breadcrumbs':            'Magento_Theme/js/view/breadcrumbs'
        }
    },
    paths: {
        'jquery/ui': 'jquery/jquery-ui',
		'jquerybase':             'Magento_Theme/js/view/jquery',
        'jqueryui':               'Magento_Theme/js/view/jquery-ui.min',
        'poppermin':               'Magento_Theme/js/view/popper.min',
        'bootstrapmin':           'Magento_Theme/js/view/bootstrap.min',
        'scripts':                'js/scripts'
    },
    deps: [
        'jquery/jquery.mobile.custom',
        'mage/common',
        'mage/dataPost',
        'mage/bootstrap'
    ],
    config: {
        mixins: {
            'Magento_Theme/js/view/breadcrumbs': {
                'Magento_Theme/js/view/add-home-breadcrumb': true
            },
            'jquery/jquery-ui': {
                'jquery/patches/jquery-ui': true
            }
        }
    },
	shim: {
			 'poppermin': {
				'deps': ['jquery'],
				'exports': 'Popper'
			},
			'bootstrapmin': {
				'deps': ['jquery', 'poppermin']
			},
            'scripts': {
                'deps': ['jquery','jquery/jquery-ui','mage/bootstrap','poppermin'] //gives your parent dependencies name here
            }
    }
};
