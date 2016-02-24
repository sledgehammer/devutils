<?php

namespace Sledgehammer\Devutils;

use Sledgehammer\Core\Html;
use Sledgehammer\Mvc\Component\DescriptionList;
use Sledgehammer\Mvc\Component\Nav;
use Sledgehammer\Mvc\Template;
use Sledgehammer\Mvc\View;
use Sledgehammer\Mvc\VirtualFolder;

/**
 * De Modules map, toon eigenschappen, toon unittests
 * @package DevUtils
 */
class PackageFolder extends VirtualFolder {

    /**
     * @var Package  A composer package
     */
    protected $package;

    function __construct($package) {
        parent::__construct();
        $this->package = $package;
    }

    function index() {
        $properties = $this->package->getProperties();
        if (isset($properties['Owner_email'])) {
            $properties['Owner'] = htmlentities($properties['Owner']) . ' &lt;<a href="mailto:' . $properties['Owner_email'] . '">' . $properties['Owner_email'] . '</a>&gt;';
            unset($properties['Owner_email']);
        }
        $utilityList = array();
        foreach ($this->package->getUtilities() as $utilFilename => $util) {
            $utilityList['utils/' . $utilFilename] = array('icon' => $util->icon, 'label' => $util->title);
        }
        if ($utilityList) {
            $utilities = new Nav($utilityList, array('class' => 'nav nav-list'));
        } else {
            $utilities = false;
        }
        return new Template('module.php', array(
            'module' => $this->package,
            'properties' => new DescriptionList(array('class' => 'dl-horizontal', 'items' => $properties)),
            'documentation' => $this->getDocumentationList(),
            'utilities' => $utilities,
            'unittests' => $this->getUnitTestList(),
                ), array(
            'title' => $this->package->name . ' module',
        ));
    }

    function generateContent() {
        $name = $this->package->name . ' module';
        // getDocument()->title = $name;
        $this->addCrumb($name, $this->getPath());
        return parent::generateContent();
    }

    function phpdocs_folder() {
        $folder = new PhpDocs($this->package);
        return $folder->generateContent();
    }

    function files_folder() {
        $this->addCrumb('Files', $this->getPath(true));
        $command = new FileBrowser($this->package->path, array('show_fullpath' => true, 'show_hidden_files' => true));
        return $command->generateContent();
    }

    function utils_folder($filename) {
        $folder = new UtilsFolder($this->package);
        return $folder->generateContent();
    }

    /**
     *
     * @return array
     */
    protected function get_properties() {
        $module = $this->package;
        $properties = array(
            'Owner' => htmlentities($module->owner) . ' &lt;<a href="mailto:' . $module->owner_email . '">' . $module->owner_email . '</a>&gt;',
            'Version' => $module->get_version(),
            'Revision' => $module->get_revision(),
        );
        if ($module->owner == '' && $module->owner_email == '') {
            unset($properties['Owner']);
        }
        if (!$properties['Version']) {
            unset($properties['Version']);
        }
        return $properties;
    }

    protected function getDocumentationList() {
        $iconPrefix = \Sledgehammer\WEBROOT . 'icons/';
        $actions = array(
            'phpdocs/' => array('icon' => $iconPrefix . 'documentation.png', 'label' => 'API Documentation'),
        );
        if (file_exists($this->package->path . 'docs/')) {
            $actions['files/docs/'] = array('icon' => $iconPrefix . 'documentation.png', 'label' => 'Documentation folder');
        }
        //'files/' => array('icon' => 'mime/folder.png', 'label' => 'Files'),
        return new Nav($actions, array('class' => 'nav nav-list'));
    }

    /**
     *
     * @return View
     */
    protected function getUnitTestList() {
        $tests = $this->package->getUnitTests();
        if (count($tests) == 0) {
            return new Html('<span class="muted">No tests found</span>');
        }
        $iconPrefix = \Sledgehammer\WEBROOT . 'icons/';
        $list = array(
            \Sledgehammer\WEBROOT . 'tests/' . $this->package->name . '/' => array('icon' => 'play', 'label' => 'Run all'),
        );
        foreach ($tests as $testfile) {
            if (\Sledgehammer\text($testfile)->endsWith('Test.php')) {
                $label = substr($testfile, 0, -8);
            } else {
                $label = substr($testfile, 0, -4);
            }
            $list[\Sledgehammer\WEBROOT . 'tests/' . $this->package->name . '/' . $testfile] = array('icon' => $iconPrefix . 'test.png', 'label' => $label);
        }

        return new Nav($list, array('class' => 'nav nav-list'));
    }

}

?>
