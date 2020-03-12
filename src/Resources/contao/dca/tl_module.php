<?php

/*
 * @copyright  trilobit GmbH
 * @author     trilobit GmbH <https://github.com/trilobit-gmbh>
 * @license    LGPL-3.0-or-later
 * @link       http://github.com/trilobit-gmbh/contao-pkg-bundle
 */

$GLOBALS['TL_DCA']['tl_module']['palettes']['pkg'] = '{title_legend},name,headline,type;{template_legend:hide},publickeygrabberTpl,customTpl;{publickeygrabber_legend},pkgHost,pkgHostFallback,pkgEmailDomain,pkgBlacklistedEmails,pkgFilters;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

$GLOBALS['TL_DCA']['tl_module']['fields']['publickeygrabberTpl'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['publickeygrabberTpl'],
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => function () { return $this->getTemplateGroup('pkg_'); },
    'eval' => ['chosen' => true, 'tl_class' => 'w50'],
    'sql' => "varchar(64) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['pkgHost'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['pkgHost'],
    'exclude' => true,
    'search' => true,
    'inputType' => 'text',
    'eval' => ['mandatory' => true, 'rgxp' => 'url', 'tl_class' => 'w50'],
    'sql' => 'mediumtext NULL',
];
$GLOBALS['TL_DCA']['tl_module']['fields']['pkgHostFallback'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['pkgHostFallback'],
    'exclude' => true,
    'search' => true,
    'inputType' => 'text',
    'eval' => ['rgxp' => 'url', 'tl_class' => 'w50'],
    'sql' => 'mediumtext NULL',
];
$GLOBALS['TL_DCA']['tl_module']['fields']['pkgEmailDomain'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['pkgEmailDomain'],
    'exclude' => true,
    'search' => true,
    'inputType' => 'text',
    'eval' => ['tl_class' => 'clr'],
    'sql' => 'mediumtext NULL',
];

$GLOBALS['TL_DCA']['tl_module']['fields']['pkgBlacklistedEmails'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['pkgBlacklistedEmails'],
    'exclude' => true,
    'inputType' => 'multiColumnWizard',
    'sql' => 'blob NULL',
    'eval' => [
        'tl_class' => 'clr',
        'columnFields' => [
            'pkgBlacklistedEmails' => [
                'label' => &$GLOBALS['TL_LANG']['tl_module']['pkgBlacklistedEmails']['columnEmail'],
                'exclude' => true,
                'inputType' => 'text',
                'eval' => [
                    'decodeEntities' => true,
                ],
            ],
        ],
        'buttons' => ['up' => false, 'down' => false],
    ],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['pkgFilters'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['pkgFilters'],
    'exclude' => true,
    'inputType' => 'multiColumnWizard',
    'sql' => 'blob NULL',
    'eval' => [
        'tl_class' => 'clr',
        'columnFields' => [
            'pkgFilters' => [
                'label' => &$GLOBALS['TL_LANG']['tl_module']['pkgFilters']['columnFilter'],
                'exclude' => true,
                'inputType' => 'text',
            ],
        ],
        'buttons' => ['up' => false, 'down' => false],
    ],
];
