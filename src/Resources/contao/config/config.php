<?php

/*
 * @copyright  trilobit GmbH
 * @author     trilobit GmbH <https://github.com/trilobit-gmbh>
 * @license    LGPL-3.0-or-later
 * @link       http://github.com/trilobit-gmbh/contao-pkg-bundle
 */

use Trilobit\PkgBundle\FrontendModule\PublicKeyGrabberModule;

$GLOBALS['FE_MOD']['application']['pkg'] = PublicKeyGrabberModule::class;
