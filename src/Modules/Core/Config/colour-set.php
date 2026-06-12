<?php

return [

    // path, relative to the project root, of the css file that defines the colour variables
    // the file is inlined into the admin so the swatches can resolve the variables
    'css_file' => 'resources/css/_variables.css',

    // key => label, where the key matches a css variable name, e.g. 'grey-light' => var(--grey-light)
    // the key is the value stored against the content field
    'colours' => [],

];
