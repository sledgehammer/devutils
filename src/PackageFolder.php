<?php

namespace Sledgehammer\Devutils;

use Sledgehammer\Core\Html;
use Sledgehammer\Mvc\Component\Breadcrumbs;
use Sledgehammer\Mvc\Component\DescriptionList;
use Sledgehammer\Mvc\Component\Nav;
use Sledgehammer\Mvc\Template;
use Sledgehammer\Mvc\View;
use Sledgehammer\Mvc\VirtualFolder;

/**
 * De Modules map, toon eigenschappen, toon unittests.
 */
class PackageFolder extends VirtualFolder
{
    /**
     * @var Package A composer package
     */
    protected $package;

    public function __construct($package)
    {
        parent::__construct();
        $this->package = $package;
    }

    public function index()
    {
        $properties = $this->package->getProperties();
        if (isset($properties['Owner_email'])) {
            $properties['Owner'] = htmlentities($properties['Owner']).' &lt;<a href="mailto:'.$properties['Owner_email'].'">'.$properties['Owner_email'].'</a>&gt;';
            unset($properties['Owner_email']);
        }
        $utilityList = array();
        foreach ($this->package->getUtilities() as $utilFilename => $util) {
            $utilityList['utils/'.$utilFilename] = array('icon' => $util->icon, 'label' => $util->title);
        }
        if ($utilityList) {
            $utilities = new Nav($utilityList, array('class' => 'nav nav-list'));
        } else {
            $utilities = false;
        }

        return new Template('package.php', array(
            'module' => $this->package,
            'properties' => new DescriptionList(array('class' => 'dl-horizontal', 'items' => $properties)),
            'utilities' => $utilities,
            'unittests' => $this->getUnitTestList(),
                ), array(
            'title' => $this->package->name.' package',
        ));
    }

    public function generateContent()
    {
        $name = $this->package->name;
        Breadcrumbs::instance()->add($name, $this->getPath());

        return parent::generateContent();
    }

    public function tests_folder()
    {
        $controller = new TestsFolder($this->package);

        return $controller->generateContent();
    }

    public function phpdocs_folder()
    {
        $folder = new PhpDocs($this->package);

        return $folder->generateContent();
    }

    public function files_folder()
    {
        Breadcrumbs::instance()->add('Files', $this->getPath(true));
        $command = new FileBrowser($this->package->path, array('show_fullpath' => true, 'show_hidden_files' => true));

        return $command->generateContent();
    }

    public function utils_folder($filename)
    {
        $folder = new UtilsFolder($this->package);

        return $folder->generateContent();
    }

    /**
     * @return array
     */
    protected function get_properties()
    {
        $module = $this->package;
        $properties = array(
            'Owner' => htmlentities($module->owner).' &lt;<a href="mailto:'.$module->owner_email.'">'.$module->owner_email.'</a>&gt;',
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

    /**
     * @return View
     */
    protected function getUnitTestList()
    {
        $tests = $this->package->getUnitTests();
        if (count($tests) == 0) {
            return new Html('<span class="muted">No tests found</span>');
        }
        $iconPrefix = \Sledgehammer\WEBROOT.'icons/';
        $urlPrefix = $this->getPath().'tests/';
        $list = [
             $urlPrefix => array('icon' => 'play', 'label' => 'Run all'),
        ];
        foreach ($tests as $testfile) {
            if (\Sledgehammer\text($testfile)->endsWith('Test.php')) {
                $label = substr($testfile, 0, -8);
            } else {
                $label = substr($testfile, 0, -4);
            }
            $list[$urlPrefix.$testfile] = array('icon' => $iconPrefix.'test.png', 'label' => $label);
        }

        return new Nav($list, array('class' => 'nav nav-list'));
    }
}
