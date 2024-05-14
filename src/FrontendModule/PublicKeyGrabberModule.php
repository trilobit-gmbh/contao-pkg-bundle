<?php

declare(strict_types=1);

/*
 * @copyright  trilobit GmbH
 * @author     trilobit GmbH <https://github.com/trilobit-gmbh>
 * @license    LGPL-3.0-or-later
 */

namespace Trilobit\PkgBundle\FrontendModule;

use Contao\FrontendTemplate;
use Contao\Module;
use Contao\StringUtil;

/**
 * Provides Public Key Grabbing by querying keyservers for domain @domain.tld.
 *
 * Class TrilobitPublicKeyGrabber
 */
class PublicKeyGrabberModule extends Module
{
    public const KEYSERVER_SEARCH_URL = 'pks/lookup?op=index&search=%s';

    // const KEY_REVOKED_MARKER = '*** KEY REVOKED ***';
    public const KEY_REVOKED_MARKER = 'revok';

    /**
     * @var string
     */
    protected $strTemplate = 'mod_pkg';

    /**
     * @var string
     */
    protected $keyServer;

    public function generate()
    {
        return parent::generate();
    }

    protected function compile()
    {
        $strCustomTemplate = 'pkg_default';

        if ('' !== $this->publickeygrabberTpl) {
            $strCustomTemplate = $this->publickeygrabberTpl;
        }

        $objTemplate = new FrontendTemplate($strCustomTemplate);
        $objTemplate->setData($this->arrData);

        $objTemplate->items = $this->getPublicKeys();

        $objTemplate->keyServer = $this->keyServer;

        $this->Template->gpgKeys = $objTemplate->parse();

        $this->Template->request = $this->pkgHost.'/'.sprintf(self::KEYSERVER_SEARCH_URL, $this->pkgEmailDomain);
        $this->Template->keyServer = $this->keyServer;
        $this->Template->filterList = array_filter(array_map(
            static function($item) {
                return $item['pkgFilters'] ?? '';
            },
            StringUtil::deserialize($this->pkgFilters, true)
        ));
        $this->Template->notAllowedList = array_filter(array_map(
            static function($item) {
                return $item['pkgBlacklistedEmails'] ?? '';
            },
            StringUtil::deserialize($this->pkgBlacklistedEmails, true)
        ));
    }

    public function getPublicKeys(): array
    {
        $data = [];

        foreach (explode(
            '<hr />',
            $this->getSearchResults()
        ) as $key => $value) {
            $tmp = $this->getPublicKeyData($this->applyFilters(trim(strip_tags($value))));

            if (!empty($tmp)) {
                $data[$tmp['name'] ?? $key] = $tmp;
            }
        }

        $data = array_filter($data);

        ksort($data);

        return $data;
    }

    protected function sendKeyserverRequest($url): array
    {
        $objCurl = curl_init();

        curl_setopt($objCurl, \CURLOPT_URL, $url);

        curl_setopt($objCurl, \CURLOPT_USERAGENT, 'Contao PKG API');
        curl_setopt($objCurl, \CURLOPT_COOKIEJAR, TL_ROOT.'/system/tmp/curl.cookiejar.txt');
        curl_setopt($objCurl, \CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($objCurl, \CURLOPT_ENCODING, '');
        curl_setopt($objCurl, \CURLOPT_RETURNTRANSFER, true);
        curl_setopt($objCurl, \CURLOPT_AUTOREFERER, true);
        curl_setopt($objCurl, \CURLOPT_SSL_VERIFYPEER, false);    // required for https urls
        curl_setopt($objCurl, \CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($objCurl, \CURLOPT_TIMEOUT, 30);
        curl_setopt($objCurl, \CURLOPT_MAXREDIRS, 3);

        $returnValue = curl_exec($objCurl);
        $status = curl_getinfo($objCurl, \CURLINFO_HTTP_CODE);

        curl_close($objCurl);

        return [
            'status' => $status,
            'body' => $returnValue,
            'request_url' => $url,
        ];
    }

    protected function getSearchResults(): string
    {
        // todo: cache result
        $response = $this->sendKeyserverRequest($this->pkgHost.'/'.sprintf(self::KEYSERVER_SEARCH_URL, $this->pkgEmailDomain));
        $this->keyServer = $this->pkgHost;

        if (200 !== $response['status']) {
            return '';
        }

        $buffer = $response['body'];

        if (false === $buffer) {
            if (!empty($this->pkgHostFallback)) {
                $response = $this->sendKeyserverRequest($this->pkgHostFallback.'/'.sprintf(self::KEYSERVER_SEARCH_URL, $this->pkgEmailDomain));
                $this->keyServer = $this->pkgHostFallback;

                $buffer = $response['body'];

                if (false === $buffer) {
                    try {
                        throw new \Exception('Keyserver page could not be opened');
                    } catch (\Exception $e) {
                        return $e->getMessage();
                    }
                }
            }
        }

        return $buffer;
    }

    protected function getPublicKeyData(string $buffer): array
    {
        $buffer = str_replace(
            [
                '&lt;',
                '&gt;',
                '%3C',
                '%3E',
                '%20',
                '&#13;',
                "\r",
                "\n",
            ],
            [
                '<',
                '>',
                '<',
                '>',
                ' ',
                '',
                '',
            ],
            $buffer
        );

        preg_match_all(
            '/^.*?(sig\s'.self::KEY_REVOKED_MARKER.')\s.*$/si',
            $buffer,
            $matches
        );

        if (isset($matches[1][0]) && 'sig '.self::KEY_REVOKED_MARKER === $matches[1][0]) {
            return [];
        }

        preg_match_all(
            '/^.*pub\s(.*?\/(.*?))\s.*?uid\s(.*?)\s<(.*?)>.*?sig\s.*$/si',
            $buffer,
            $matches
        );

        if (!isset($matches[3][0]) && !isset($matches[4][0])) {
            preg_match_all(
                '/(.*)pub:(.*?)::uid:(.*?)\s<(.*?)>:.*/si',
                $buffer,
                $matches
            );

            if (!isset($matches[3][0]) && !isset($matches[4][0])) {
                return [];
            }
        }

        if (false === $this->isAllowed($matches[4][0] ?? '')) {
            return [];
        }

        return [
            'raw' => $buffer,
            'name' => $matches[3][0] ?? '',
            'email' => $matches[4][0] ?? '',
            'url' => sprintf(self::KEYSERVER_SEARCH_URL, $matches[2][0] ?? ''),
            'pki' => $matches[1][0] ?? '',
            'fingerprint' => $matches[2][0] ?? '',
        ];
    }

    protected function isAllowed(string $item): bool
    {
        if (empty($item)) {
            return true;
        }

        foreach (StringUtil::deserialize($this->pkgBlacklistedEmails, true) as $value) {
            if (!empty($value['pkgBlacklistedEmails']) && false !== stripos($item, $value['pkgBlacklistedEmails'])) {
                return false;
            }
        }

        return true;
    }

    protected function applyFilters(string $item): string
    {
        $filters = [];

        foreach (StringUtil::deserialize($this->pkgFilters, true) as $value) {
            $filters[] = $value['pkgFilters'];
        }

        array_filter($filters);

        foreach ($filters as $value) {
            $value = html_entity_decode($value);
            $item = str_ireplace($value, '', $item);
        }

        return $item;
    }
}
