/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    paths: {
        'jquery/ui': 'jquery/jquery-ui',
		'jquerybase':             'Magento_Theme/js/view/jquery',
        'jqueryui':               'Magento_Theme/js/view/jquery-ui.min',       
        'bootstrap4':             'js/bootstrap.bundle.min',
        'scripts':                'js/scripts'
    },
    
	shim: {			
			'bootstrap4': {
				'deps': ['jquery']
			},
            'scripts': {
                'deps': ['jquery','jquery/jquery-ui','bootstrap4'] //gives your parent dependencies name here
            }
    }
};
