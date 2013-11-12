<?php
namespace Werkint\Bundle\WebscriptBundle\Hook;

use Composer\Script\CommandEvent;
use Sensio\Bundle\DistributionBundle\Composer\ScriptHandler as BaseScriptHandler;

/**
 * ComposerHook.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class ComposerHook extends BaseScriptHandler
{
    const PACKAGES_URL = 'http://werkint.com/webapp-scripts/packages';

    /**
     * @param CommandEvent $event
     */
    public static function updateScripts(CommandEvent $event)
    {
        $options = self::getOptions($event);
        $scriptDir = $options['werkint-webscript-scripts'];
        $repo = static::PACKAGES_URL;

        $httpExists = function ($url) {
            $handle = curl_init($url);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            curl_exec($handle);
            $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
            if ($httpCode != 200) {
                return false;
            }
            curl_close($handle);
            return true;
        };

        $getFile = function ($url, $dest) {
            return copy($url, $dest);
        };

        $processPackageFiles = function (
            $package, $hashes, $list, $allowDirs
        ) use (&$repo, &$scriptDir, &$httpExists, &$getFile) {
            $packageDir = $scriptDir . '/' . $package;
            $packageRepo = $repo . '/' . $package;

            foreach (explode(',', $list) as $file) {
                if (!($file = trim($file))) {
                    continue;
                }
                $fileFull = $packageDir . '/' . $file;
                if (!is_file($fileFull) && !$httpExists($packageRepo . '/' . $file)) {
                    $fileFull .= '.zip';
                    $file .= '.zip';
                    if (!$allowDirs) {
                        return false;
                    }
                    if (!isset($hashes[sha1($file)])) {
                        return false;
                    }
                    if (!(file_exists($fileFull) &&
                        $hashes[sha1($file)] == sha1_file($fileFull))
                    ) {
                        $getFile($packageRepo . '/' . $file, $fileFull);
                    }
                    $zip = new \ZipArchive();
                    $zip->open($fileFull);
                    $zip->extractTo($packageDir);
                    $zip->close();
                } elseif (!(file_exists($fileFull) && ($hashes[sha1($file)] == sha1_file($fileFull)))) {
                    if (!isset($hashes[sha1($file)])) {
                        return false;
                    }
                    $getFile($packageRepo . '/' . $file, $fileFull);
                }
            }
            return true;
        };

        echo 'Updating WerkintWebapp scripts:';
        $getFile($repo . '/.packages', $scriptDir . '/.packages');
        $getFile($repo . '/.hashes', $scriptDir . '/.hashes');
        $hashTable = parse_ini_file($scriptDir . '/.hashes');
        $list = file($scriptDir . '/.packages');
        foreach ($list as $package) {
            if (!($package = trim($package))) {
                continue;
            }

            // Пакет
            echo ' ' . $package;
            $packageDir = $scriptDir . '/' . $package;
            $packageRepo = $repo . '/' . $package;
            if (!file_exists($packageDir)) {
                mkdir($packageDir);
            }

            // Хеши файлов
            if (!isset($hashTable[sha1($package)])) {
                echo '=ERROR';
                continue(1);
            }
            touch($packageDir . '/.hashes');
            touch($packageDir . '/.package.ini');
            if ($hashTable[sha1($package)] !=
                sha1(sha1_file($packageDir . '/.hashes') .
                    sha1_file($packageDir . '/.package.ini'))
            ) {
                $getFile($packageRepo . '/.hashes', $packageDir . '/.hashes');
                $getFile($packageRepo . '/.package.ini', $packageDir . '/.package.ini');
            }
            $hashes = parse_ini_file($packageDir . '/.hashes');
            $meta = parse_ini_file($packageDir . '/.package.ini');

            if (!$processPackageFiles($package, $hashes, $meta['files'], false) ||
                !$processPackageFiles($package, $hashes, $meta['res'], true)
            ) {
                echo '=ERROR';
                continue;
            }
        }
        echo "\n";
    }

}
