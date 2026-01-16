<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;

$GLOBALS['TL_DCA']['tl_news_archive']['fields']['jsonLdType'] = [
    'inputType' => 'select',
    'options' => ['Article', 'BlogPosting', 'NewsArticle'],
    'eval' => ['chosen' => true, 'tl_class' => 'w50 clr'],
    'default' => 'NewsArticle',
    'sql' => "varchar(32) NOT NULL default 'NewsArticle'",
];

PaletteManipulator::create()
    ->addField('jsonLdType', 'title_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_news_archive')
;
