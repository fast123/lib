'use strict';

var EOL = require('os').EOL;

module.exports = function(entity, naming) {
    return [
    	'@import "main-bx";',
    	'',
        '.' + naming.stringify(entity) + ' {',
		'    @include lg-block() {',
		'		',
		'		',
		'    }',
		'    @include md-block() {',
		'		',
		'		',
		'    }',
        '}',
        ''
    ].join(EOL);
};