<?php

namespace AppBundle\Composer;

use Composer\Script\Event;

class ScriptHandler
{
    /**
     * Script that installs the needed Symfony Collection jQuery plugin files
     *
     * @see https://github.com/ninsuo/symfony-collection
     *
     * @param Event $event
     */
    public static function installSymfonyCollectionFiles(Event $event)
    {
        $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
        self::installSymfonyCollectionTwigFormTheme($vendorDir);
        self::installSymfonyCollectionJQueryPlugin($vendorDir);
    }

    /**
     * Inspired by https://github.com/ninsuo/symfony-collection/blob/master/ScriptHandler.php
     */
    protected static function installSymfonyCollectionTwigFormTheme($vendorDir)
    {
        $collectionDir = $vendorDir.'/ninsuo/symfony-collection';
        echo "*** ninsuo/symfony-collection: Installing Twig form theme... \t";
        $viewsDir = realpath($vendorDir.'/../app/Resources/views/form');
        if (!is_dir($viewsDir)) {
            echo "FAILED: ";
            echo "Directory '$viewsDir' doesn't exist.\n";
            return;
        }
        if (!is_writable($viewsDir)) {
            echo "FAILED: ";
            echo "Directory '$viewsDir' is not writeable.\n";
            return;
        }
        copy($collectionDir.'/jquery.collection.html.twig', $viewsDir.'/jquery.collection.html.twig');
        echo "SUCCESS\n";
    }

    /**
     * Inspired by https://github.com/ninsuo/symfony-collection/blob/master/ScriptHandler.php
     */
    protected static function installSymfonyCollectionJQueryPlugin($vendorDir)
    {
        $collectionDir = $vendorDir.'/ninsuo/symfony-collection';
        echo "*** ninsuo/symfony-collection: Installing jQuery plugin... \t";
        $webDir = realpath($vendorDir.'/../web/');
        if (!is_dir($webDir)) {
            echo "FAILED: ";
            echo "Directory 'web' doesn't exist.\n";
            return;
        }
        $jsDir = $webDir.'/assets/front/js';
        if (!is_dir($jsDir) && !mkdir($jsDir)) {
            echo "FAILED: ";
            echo "Directory '$jsDir' can't be created.\n";
            return;
        }
        if (!is_writable($jsDir)) {
            echo "FAILED: ";
            echo "Directory '$jsDir' is not writeable.\n";
            return;
        }
        copy($collectionDir.'/jquery.collection.js', $jsDir.'/jquery.collection.js');
        echo "SUCCESS\n";
    }
}