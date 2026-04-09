<?php

declare(strict_types=1);

/*
 * @copyright  trilobit GmbH
 * @author     trilobit GmbH <https://github.com/trilobit-gmbh>
 * @license    LGPL-3.0-or-later
 */

$GLOBALS['TL_LANG']['tl_module']['publickeygrabber_legend'] = 'Public Key Grabber Settings';

$GLOBALS['TL_LANG']['tl_module']['publickeygrabberTpl'] = [
    'Template',
    'Please select a template.',
];

$GLOBALS['TL_LANG']['tl_module']['pkgHost'] = [
    'Public Keyserver URL',
    'Please enter the public keyserver here on which your keys should be searched.',
];

$GLOBALS['TL_LANG']['tl_module']['pkgHostFallback'] = [
    'Fallback Keyserver URL',
    'If the public keyserver shown here cannot be reached, we will try to contact it.',
];
$GLOBALS['TL_LANG']['tl_module']['pkgEmailDomain'] = [
    'Domain',
    'The domain whose keys are to be displayed.',
];

$GLOBALS['TL_LANG']['tl_module']['pkgBlacklistedEmails'] = [
    'Negative list',
    'Please enter the email addresses you wish to hide here.',
    'columnEmail' => [
        'E-mail address',
    ],
];

$GLOBALS['TL_LANG']['tl_module']['pkgFilters'] = [
    'Filter list',
    'Text snippets that should be filtered out of the display.',
    'columnFilter' => [
        'Text snippets',
    ],
];
