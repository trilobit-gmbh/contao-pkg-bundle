<?php

/*
 * @copyright  trilobit GmbH
 * @author     trilobit GmbH <https://github.com/trilobit-gmbh>
 * @license    LGPL-3.0-or-later
 * @link       http://github.com/trilobit-gmbh/contao-pkg-bundle
 */

namespace Trilobit\PkgBundle\FrontendModule;

use Contao\BackendTemplate;
use Contao\FrontendTemplate;
use Contao\Module;
use Contao\StringUtil;
use Patchwork\Utf8;

/**
 * Provides Public Key Grabbing by querying keyservers for domain @domain.tld.
 *
 * Class TrilobitPublicKeyGrabber
 */
class PublicKeyGrabberModule extends Module
{
    const KEYSERVER_SEARCH_URL = '/pks/lookup?op=index&search=%s&fingerprint=on';

    const KEY_REVOKED_MARKER = '*** KEY REVOKED ***';

    /**
     * @var string
     */
    protected $strTemplate = 'mod_pkg';

    /**
     * @var string
     */
    protected $strCurrentKeyServer;

    /**
     * @return string
     */
    public function getKeyserverUrl()
    {
        return $this->pkgHost;
    }

    /**
     * @return string
     */
    public function getFallbackKeyserverUrl()
    {
        return $this->pkgHostFallback;
    }

    /**
     * @return string
     */
    public function getSearchDomain()
    {
        return $this->pkgEmailDomain;
    }

    /**
     * @return array
     */
    public function getBlacklistedEmails()
    {
        $arrBlacklistedEmails = [];

        foreach (StringUtil::deserialize($this->pkgBlacklistedEmails, true) as $pkgBlacklistEntry) {
            $arrBlacklistedEmails[] = $pkgBlacklistEntry['pkgBlacklistedEmails'];
        }

        return $arrBlacklistedEmails;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        $arrPkgFilters = [];

        foreach (StringUtil::deserialize($this->pkgFilters, true) as $pkgFilterEntry) {
            $arrPkgFilters[] = $pkgFilterEntry['pkgFilters'];
        }

        return $arrPkgFilters;
    }

    /**
     * @return string
     */
    public function getKeyserverUrlFragment()
    {
        return sprintf(self::KEYSERVER_SEARCH_URL, $this->getSearchDomain());
    }

    /**
     * Display a wildcard in the back end.
     *
     * @return string
     */
    public function generate()
    {
        if (TL_MODE === 'BE') {
            $objTemplate = new BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### '.Utf8::strtoupper($GLOBALS['TL_LANG']['FMD']['pkg'][0]).' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id='.$this->id;

            return $objTemplate->parse();
        }

        return parent::generate();
    }

    /**
     * Retrieve public keys from keyserver and return them as specified.
     *
     * @throws \Exception
     *
     * @return array|string
     */
    public function getPublicKeys()
    {
        $content = $this->getPageContent();

        $publicKeyList = $this->getPublicKeysFromPageContent($content);

        $publicKeyList = $this->stripForbidden($publicKeyList);

        return $publicKeyList;
    }

    /**
     * Generate module.
     */
    protected function compile()
    {
        $strCustomTemplate = 'pkg_default';

        if ('' !== $this->publickeygrabberTpl) {
            $strCustomTemplate = $this->publickeygrabberTpl;
        }

        $objTemplate = new FrontendTemplate($strCustomTemplate);
        $objTemplate->setData($this->arrData);
        $objTemplate->items = $this->getPublicKeys();
        $objTemplate->currentKeyServer = $this->strCurrentKeyServer;

        $this->Template->gpgKeys = $objTemplate->parse();
        $this->Template->currentKeyServer = $this->strCurrentKeyServer;
    }

    /**
     * Strips forbidden (blacklisted or revoked) keys from result.
     *
     * @return array
     */
    protected function stripForbidden(array $publicKeyList)
    {
        foreach ($publicKeyList as $listElementKey => $listElementValue) {
            if (false === $listElementValue['allowed']) {
                unset($publicKeyList[$listElementKey]);
            }
        }

        return $publicKeyList;
    }

    /**
     * @param $strApiUrl
     *
     * @return array
     */
    protected function apiCall($strApiUrl)
    {
        $objCurl = curl_init();

        curl_setopt($objCurl, CURLOPT_URL, $strApiUrl);

        curl_setopt($objCurl, CURLOPT_USERAGENT, 'Contao PKG API');
        curl_setopt($objCurl, CURLOPT_COOKIEJAR, TL_ROOT.'/system/tmp/curl.cookiejar.txt');
        curl_setopt($objCurl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($objCurl, CURLOPT_ENCODING, '');
        curl_setopt($objCurl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($objCurl, CURLOPT_AUTOREFERER, true);
        curl_setopt($objCurl, CURLOPT_SSL_VERIFYPEER, false);    // required for https urls
        curl_setopt($objCurl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($objCurl, CURLOPT_TIMEOUT, 30);
        curl_setopt($objCurl, CURLOPT_MAXREDIRS, 3);

        $returnValue = curl_exec($objCurl);
        $returnCode = curl_getinfo($objCurl, CURLINFO_HTTP_CODE);

        curl_close($objCurl);

        return [
            'HTTP_CODE' => $returnCode,
            'CONTENT' => $returnValue,
        ];
    }

    /**
     * Retrieves content with public keys from keyserver page.
     *
     * @throws \Exception
     *
     * @return string
     */
    protected function getPageContent()
    {
        $this->strCurrentKeyServer = $this->getKeyserverUrl();

        $apiFeedback = $this->apiCall($this->strCurrentKeyServer.$this->getKeyserverUrlFragment());
        $pageContent = $apiFeedback['CONTENT'];

        if (false === $pageContent) {
            $this->strCurrentKeyServer = $this->getFallbackKeyserverUrl();

            if (!empty($this->strCurrentKeyServer)) {
                $apiFeedback = $this->apiCall($this->strCurrentKeyServer.$this->getKeyserverUrlFragment());
                $pageContent = $apiFeedback['CONTENT'];

                if (false === $pageContent) {
                    try {
                        throw new \Exception('Keyserver page could not be opened');
                    } catch (\Exception $e) {
                        return $e->getMessage();
                    }
                }
            }
        }

        return $pageContent;
    }

    /**
     * Retrieves Public Key Information from Keyserver page content.
     *
     * @param string $content
     *
     * @return array
     */
    protected function getPublicKeysFromPageContent($content)
    {
        $content = preg_replace(
            ['/<\/pre>\n+/'],
            ['</pre>'],
            $content
        );
        $content = explode('</pre><hr /><pre>', $content);

        //Shift the beginning off of the array
        array_shift($content);

        $publicKeyList = [];

        foreach ($content as $element) {
            $element = $this->applyFilters($element);

            $elementDetails = $this->getElementDetails($element);

            $elementDetails['allowed'] = $this->isPublicKeyAllowed($element);

            $publicKeyList[] = $elementDetails;
        }

        sort($publicKeyList);

        return $publicKeyList;
    }

    /**
     * Retrieves Detail Information from Element.
     *
     * @param string $element
     *
     * @return array
     */
    protected function getElementDetails($element)
    {
        $arrMatch = [];
        $elementDetails = [];

        $element = trim($element);
        $element = preg_replace(
            ['/<strong>/', '/<\/strong>/', '/\n/', '/\s+/'],
            ['', '', ' ', ' '],
            $element
        );

        // BSPL. $element:
        // pub 4096R/A11FE1C8 2016-07-04 Kristina Ivanova <kristina.ivanova@trilobit.de> Fingerprint=2AE6 D1AE 7F0A AA8D A9DE 6F9C 33A7 3086 A11F E1C8
        /*
        pub  1024D/<a href="/pks/lookup?op=get&amp;search=0x115B2B7E8BAEA4C5">8BAEA4C5</a> 2005-03-29 <ahref="/pks/lookup?op=vindex&amp;fingerprint=on&amp;search=0x115B2B7E8BAEA4C5">Oliver Reiff, trilobit (Trilobit GmbH) &lt;Oliver.Reiff@trilobit.de&gt;</a>
                               trilobit GmbH &lt;info@trilobit.de&gt;
     Fingerprint=2055 32BE 5B85 85C5 57D1  56EF 115B 2B7E 8BAE A4C5
        */
        preg_match_all(
              '/^pub\s(.*?)\/'
            .'<a.*?href="(.*?)".*?>(.*?)<\/a>\s'
            .'(.*?)\s'
            .'<a.*?href="(.*?)".*?>(.*?) \&lt;(.*?)\&gt;<\/a>\s*.*\s*'
            .'Fingerprint\=(.*?)$/i',
            $element,
            $arrMatch
        );

        $elementDetails['name'] = $arrMatch[6][0];
        $elementDetails['email'] = $arrMatch[7][0];
        $elementDetails['url'] = $arrMatch[2][0];
        $elementDetails['pki'] = $arrMatch[3][0];
        $elementDetails['fingerprint'] = $arrMatch[8][0];

        return $elementDetails;
    }

    /**
     * Checks public keys for being either revoked and/or blacklisted.
     *
     * @param string $element
     *
     * @return bool
     */
    protected function isPublicKeyAllowed($element)
    {
        if (true === $this->isPublicKeyRevoked($element)) {
            return false;
        }

        if (true === $this->isPublicKeyBlacklisted($element)) {
            return false;
        }

        return true;
    }

    /**
     * Checks public key for revoked markers.
     *
     * @param string $element
     *
     * @return bool
     */
    protected function isPublicKeyRevoked($element)
    {
        if (false !== stripos($element, self::KEY_REVOKED_MARKER)) {
            return true;
        }

        return false;
    }

    /**
     * Checks public key for manual blacklisting.
     *
     * @param string $element
     *
     * @return bool
     */
    protected function isPublicKeyBlacklisted($element)
    {
        foreach ($this->getBlacklistedEmails() as $blacklistedEmail) {
            if (false !== stripos($element, $blacklistedEmail)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $element
     *
     * @return string
     */
    protected function applyFilters($element)
    {
        foreach ($this->getFilters() as $filter) {
            $filter = html_entity_decode($filter);
            $element = $this->filterElement($element, $filter);
        }

        return $element;
    }

    /**
     * Filters strings out of elements.
     *
     * @param string $element
     * @param string $stringToFilter
     *
     * @return string
     */
    protected function filterElement($element, $stringToFilter)
    {
        return $element = str_ireplace($stringToFilter, '', $element);
    }
}
