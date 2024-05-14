<?php

declare(strict_types=1);

/*
 * @copyright  trilobit GmbH
 * @author     trilobit GmbH <https://github.com/trilobit-gmbh>
 * @license    LGPL-3.0-or-later
 */

$GLOBALS['TL_LANG']['tl_module']['publickeygrabber_legend'] = 'Public Key Grabber Einstellungen';

$GLOBALS['TL_LANG']['tl_module']['publickeygrabberTpl'] = [
    'Template',
    'Bitte wählen Sie ein Template aus',
];

$GLOBALS['TL_LANG']['tl_module']['pkgHost'] = [
    'Public Keyserver URL',
    'Geben Sie hier bitte den Public Keyserver ein, auf dem nach Ihren Schlüsseln gesucht werden soll.',
];

$GLOBALS['TL_LANG']['tl_module']['pkgHostFallback'] = [
    'Fallback Keyserver URL',
    'Falls der nebenstehende Public Keyserver nicht erreicht werden kann, versuchen wir diesen zu kontaktieren.',
];
$GLOBALS['TL_LANG']['tl_module']['pkgEmailDomain'] = [
    'Domain',
    'Die Domain deren Keys dargestellt werden sollen.',
];

$GLOBALS['TL_LANG']['tl_module']['pkgBlacklistedEmails'] = [
    'Blacklist',
    'Bitte geben Sie hier die E-Mail-Adressen ein, die sie verbergen möchten.',
    'columnEmail' => [
        'E-Mail-Adresse',
    ],
];

$GLOBALS['TL_LANG']['tl_module']['pkgFilters'] = [
    'Filterliste',
    'Textbausteine die aus der Darstellung herausgefiltert werden sollen.',
    'columnFilter' => [
        'Textbausteine',
    ],
];
