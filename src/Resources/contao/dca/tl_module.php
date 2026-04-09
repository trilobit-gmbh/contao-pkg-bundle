<?php

declare(strict_types=1);

/*
 * @copyright  trilobit GmbH
 * @author     trilobit GmbH <https://github.com/trilobit-gmbh>
 * @license    LGPL-3.0-or-later
 */

$GLOBALS['TL_DCA']['tl_module']['palettes']['pkg'] = '{title_legend},name,headline,type;{template_legend:hide},publickeygrabberTpl,customTpl;{publickeygrabber_legend},pkgHost,pkgHostFallback,pkgEmailDomain,pkgBlacklistedEmails,pkgFilters;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

$GLOBALS['TL_DCA']['tl_module']['fields']['publickeygrabberTpl'] = [
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => function() { return $this->getTemplateGroup('pkg_'); },
    'eval' => ['chosen' => true, 'tl_class' => 'w50'],
    'sql' => "varchar(64) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['pkgHost'] = [
    'exclude' => true,
    'search' => true,
    'inputType' => 'text',
    'eval' => ['mandatory' => true, 'rgxp' => 'url', 'tl_class' => 'w50'],
    'default' => 'http://keys.gnupg.net:11371',
    'sql' => 'mediumtext NULL',
];
$GLOBALS['TL_DCA']['tl_module']['fields']['pkgHostFallback'] = [
    'exclude' => true,
    'search' => true,
    'inputType' => 'text',
    'eval' => ['rgxp' => 'url', 'tl_class' => 'w50'],
    'default' => 'http://keys.openpgp.org:11371',
    'sql' => 'mediumtext NULL',
];
$GLOBALS['TL_DCA']['tl_module']['fields']['pkgEmailDomain'] = [
    'exclude' => true,
    'search' => true,
    'inputType' => 'text',
    'eval' => ['tl_class' => 'clr'],
    'sql' => 'mediumtext NULL',
];

$GLOBALS['TL_DCA']['tl_module']['fields']['pkgBlacklistedEmails'] = [
    'exclude' => true,
    'inputType' => 'pkgOptionWizard',
    'sql' => 'blob NULL',
    'eval' => ['tl_class' => 'clr'],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['pkgFilters'] = [
    'exclude' => true,
    'inputType' => 'pkgOptionWizard',
    'sql' => 'blob NULL',
    'eval' => ['tl_class' => 'clr'],
];
