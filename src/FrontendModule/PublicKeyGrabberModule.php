<?php

declare(strict_types=1);

/*
 * @copyright  trilobit GmbH
 * @author     trilobit GmbH <https://github.com/trilobit-gmbh>
 * @license    LGPL-3.0-or-later
 */

namespace Trilobit\PkgBundle\FrontendModule;

use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\Date;
use Contao\File;
use Contao\FrontendTemplate;
use Contao\Input;
use Contao\Module;
use Contao\StringUtil;
use Contao\System;
use Psr\Log\LogLevel;

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
        $customTemplate = 'pkg_default';

        if (!empty($this->publickeygrabberTpl)) {
            $customTemplate = $this->publickeygrabberTpl;
        }

        $container = System::getContainer();
        $this->rootDir = $container->getParameter('kernel.project_dir');
        $this->cacheDir = $container->getParameter('kernel.cache_dir');
        $this->logger = $container->get('monolog.logger.contao');

        $template = new FrontendTemplate($customTemplate);
        $template->setData($this->arrData);
        $template->items = $this->getPublicKeys();
        $template->keyServer = $this->keyServer;

        $this->Template->gpgKeys = $template->parse();
        $this->Template->request = $this->pkgHost.'/'.\sprintf(self::KEYSERVER_SEARCH_URL, $this->pkgEmailDomain);
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
        $container = System::getContainer();

        $cacheDir = $container->getParameter('kernel.cache_dir');
        $rootDir = $container->getParameter('kernel.project_dir');

        $cacheFile = StringUtil::stripRootDir($cacheDir).'/trilobit/pkg_'.md5($this->pkgEmailDomain).'.json';

        $result = [];
        if (file_exists($rootDir.'/'.$cacheFile)) {
            $cache = new File($cacheFile);

            if ($cache->mtime > time() - 60 * 60 * 24) {
                $result = json_decode($cache->getContent(), true);
            } else {
                $cache->delete();
            }
        }

        if (empty($result)) {
            $result = explode(
                '<hr />',
                $this->getSearchResults()
            );
            $result = [
                'response' => explode(
                    '<hr />',
                    $this->getSearchResults()
                ),
                'pkgHost' => $this->pkgHost,
                'pkgEmailDomain' => $this->pkgEmailDomain,
            ];

            File::putContent($cacheFile, json_encode($result));
        }

        $data = [];

        foreach ($result['response'] as $key => $value) {
            $tmp = $this->getPublicKeyData($this->applyFilters(trim(strip_tags($value))), $result['pkgHost'], $result['pkgEmailDomain']);

            if (!empty($tmp)) {
                $data[$tmp['name'] ?? $key] = $tmp;
            }
        }

        $data = array_filter($data);

        ksort($data);

        return $data;
    }

    private function handleCache(string $identifier, ?array $result = [], array $request = []): ?array
    {
        $checksum = md5($identifier);

        $cacheFile = StringUtil::stripRootDir($this->cacheDir).'/trilobit/pkg_'.$checksum.'.json';

        if (!empty($result)) {
            $result['cache']['request'] = $request;
            $result['cache']['file'] = $cacheFile;
            $result['cache']['checksum'] = $checksum;
            $result['cache']['tstamp'] = $this->tstamp;
            $result['cache']['date'] = Date::parse('Y-m-d H:i:s', $this->tstamp);

            File::putContent($cacheFile, json_encode($result, \JSON_THROW_ON_ERROR));
        } else {
            $result = null;

            // return $result;

            if (file_exists($this->rootDir.'/'.$cacheFile)) {
                $cache = new File($cacheFile);

                if ('clear' === Input::get('cache')) {
                    $cache->delete();

                    return null;
                }

                if ((int) $cache->mtime > (int) strtotime('-12 hours')) {
                    try {
                        $result = json_decode($cache->getContent(), true, 512, \JSON_THROW_ON_ERROR);
                        $result['cache']['is_cached_result'] = true;
                    } catch (\Exception $e) {
                        $cache->delete();

                        return null;
                    }
                } else {
                    $cache->delete();
                }
            }
        }

        return $result;
    }

    protected function sendRequest($url): array
    {
        $curl = curl_init();

        curl_setopt($curl, \CURLOPT_URL, $url);

        curl_setopt($curl, \CURLOPT_USERAGENT, 'Contao PKG API');
        curl_setopt($curl, \CURLOPT_COOKIEJAR, $this->rootDir.'/system/tmp/curl.cookiejar.txt');
        curl_setopt($curl, \CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, \CURLOPT_ENCODING, '');
        curl_setopt($curl, \CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, \CURLOPT_AUTOREFERER, true);
        curl_setopt($curl, \CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, \CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curl, \CURLOPT_TIMEOUT, 5);
        curl_setopt($curl, \CURLOPT_MAXREDIRS, 3);

        $status = curl_getinfo($curl, \CURLINFO_HTTP_CODE);
        $data = curl_exec($curl);
        $error = curl_error($curl);

        $response = [
            'status' => 200,
            'body' => $data,
            'request' => $url,
        ];

        if (!empty($error)) {
            $response['status'] = 400;
            $response['error'] = $error;

            $this->logger->log(
                LogLevel::ERROR,
                $response['status'].': '.$url.' / '.$error,
                ['contao' => new ContaoContext(__METHOD__, 'ERROR')]
            );
        }

        curl_close($curl);

        return $response;
    }

    /**
     * @throws \JsonException
     */
    protected function getSearchResults(): string
    {
        $result = $this->handleCache(
            $this->pkgEmailDomain
        );

        if (null === $result) {
            $this->keyServer = $this->pkgHost;

            $result = $this->sendRequest($this->pkgHost.'/'.\sprintf(self::KEYSERVER_SEARCH_URL, $this->pkgEmailDomain));

            if ((200 !== $result['status'] || false === $result['body'])
                && !empty($this->pkgHostFallback)
            ) {
                $this->keyServer = $this->pkgHostFallback;

                $result = $this->sendRequest($this->pkgHostFallback.'/'.\sprintf(self::KEYSERVER_SEARCH_URL, $this->pkgEmailDomain));

                if (false === $result['body']) {
                    try {
                        throw new \Exception('Keyserver page could not be opened');
                    } catch (\Exception $e) {
                        return $e->getMessage();
                    }
                }
            }
        }

        if (200 !== $result['status']) {
            return '';
        }

        $result = $this->handleCache(
            $this->pkgEmailDomain,
            $result,
            [
                'server' => $this->keyServer,
                'domain' => $this->pkgEmailDomain,
                'url' => $result['request'],
            ]
        );

        $buffer = $result['body'];

        if (false === $buffer) {
            if (!empty($this->pkgHostFallback)) {
                $response = $this->sendRequest($this->pkgHostFallback.'/'.\sprintf(self::KEYSERVER_SEARCH_URL, $this->pkgEmailDomain));
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

    protected function getPublicKeyData(string $buffer, string $pkgHost, string $pkgEmailDomain): array
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
                '/(.*)pub:(.*?):.*?::uid:(.*?)\s<(.*?)>:.*/si',
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
            'url' => \sprintf(self::KEYSERVER_SEARCH_URL, $matches[2][0] ?? ''),
            'pki' => $matches[1][0] ?? '',
            'fingerprint' => $matches[2][0] ?? '',
            'pkgHost' => $pkgHost,
            'pkgEmailDomain' => $pkgEmailDomain,
        ];
    }

    protected function isAllowed(string $item): bool
    {
        if (empty($item)) {
            return true;
        }

        foreach (StringUtil::deserialize($this->pkgBlacklistedEmails, true) as $value) {
            if (!empty($value['value']) && false !== stripos($item, $value['value'])) {
                return false;
            }
        }

        return true;
    }

    protected function applyFilters(string $item): string
    {
        $filters = [];

        foreach (StringUtil::deserialize($this->pkgFilters, true) as $value) {
            $filters[] = $value['value'];
        }

        array_filter($filters);

        foreach ($filters as $value) {
            $value = html_entity_decode($value);
            $item = str_ireplace($value, '', $item);
        }

        return $item;
    }
}
