<?php

return [
  'toolbar' => [
    ['viewHTML'],
    ['historyUndo', 'historyRedo'],
    ['refinedFormatting'],
    ['strong', 'em', 'del'],
    ['superscript', 'subscript'],
    ['refinedLink', 'unlink', 'refinedInsertImage', 'noembed'],
    ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
    ['unorderedList', 'orderedList'],
    ['horizontalRule'],
    ['removeformat'],
  ],
  'formatting' => [
    'dropdown' => [
        'p',
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
        'blockquote',
        'refinedCode',
        'pre',
    ],
    'ico' => 'p'
  ],
  'fontSize' => [
    '12px',
    '14px',
    '16px',
    '18px',
    '20px',
    '22px',
  ],
  'link' => [
    'type' => 'simple', // advanced or simple
  ],
  'tagClasses' => [
      'h1' => 'heading'
  ]
];
