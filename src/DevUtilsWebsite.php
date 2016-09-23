<?php

namespace Sledgehammer\Devutils;

use DirectoryIterator;
use Sledgehammer\Core\Collection;
use Sledgehammer\Core\HttpAuthentication;
use Sledgehammer\Core\Url;
use Sledgehammer\Core\Html;
use Sledgehammer\Mvc\Component\Breadcrumbs;
use Sledgehammer\Mvc\Component\HttpError;
use Sledgehammer\Mvc\Component\Nav;
use Sledgehammer\Mvc\Component\Template;
use Sledgehammer\Mvc\Document\Page;
use Sledgehammer\Mvc\Website;

/**
 * The DevUtils Application FrontController.
 */
class DevUtilsWebsite extends Website
{
    private static $username;
    private static $password;

    /**
     * @var Package[]|Collection
     */
    public $packages;
    /**
     * @var Package
     */
    public $project;

    public function __construct($path)
    {
        $this->project = new Package($path);
        $this->packages = new Collection();
        $vendorDir = new DirectoryIterator($path.'vendor');
        foreach ($vendorDir as $entry) {
            if ($entry->isDot() || $entry->isFile() || in_array($entry->getFilename(), ['composer', 'bin'])) {
                continue;
            }
            $packageDirs = new DirectoryIterator($entry->getPathname());
            foreach ($packageDirs as $subentry) {
                if ($subentry->isDot() || $subentry->isFile()) {
                    continue;
                }
                $this->packages[] = new Package($subentry->getPathname(), $this->project);
            }
        }
        Breadcrumbs::instance()->add(['icon' => 'home-white', 'label' => 'Home'], $this->getPath());
        parent::__construct();
    }

    public function index()
    {

        $iconPrefix = \Sledgehammer\WEBROOT.'icons/';

        // Properties
//        $properties = $this->project->getProperties();
//
//        // Utilities
//        $utilityList = array();
//        foreach ($this->packages as $package) {
//            foreach ($package->getUtilities() as $utilFilename => $util) {
//                $utilityList[$package->name.'/utils/'.$utilFilename] = array('icon' => $util->icon, 'label' => $util->title);
//            }
//        }
//        $utilityList['phpinfo.php'] = array('icon' => $iconPrefix.'php.gif', 'label' => 'PHP Info');

        $template = new Template('project.php', [
            'project' => $this->project->name,
            'unittests' => $this->getUnitTestList(),
            'packages' => new Nav($this->packages->orderBy('name')->select('name', 'name'), ['class' => 'nav nav-list']),
//            'properties' => new DescriptionList($properties, array('class' => 'dl-horizontal')),
//            'utilities' => new Nav($utilityList, array('class' => 'nav nav-list')),
        ], [
            'title' => $this->project->name.' project'
        ]);
        return $template;
    }

    public function project_folder()
    {
        $controller = new PackageFolder($this->project);

        return $controller->generateContent();
    }

    public function phpinfo()
    {
        Breadcrumbs::instance()->add('PHP Info');

        return new PHPInfo();
    }

    public function rewrite_check()
    {
        die('Apache Rewrite module is enabled');
    }

    public function project_icon()
    {
        $favicon = $this->project->getFavicon();
        if ($favicon) {
            return new FileDocument($favicon);
        }

        return new FileDocument(APP_DIR.'public/icons/project.png');
    }

    /**
     * Als er geen bestand in de module_icons map staat voor de opgegeven module, geeft dan het standaard icoon weer.
     */
    public function module_icons_folder()
    {
        $url = Url::getCurrentURL();
        file_extension($url->getFilename(), $module);
        $icon = $this->project->modules[$module]->path.'icon.png';
        if (file_exists($icon)) {
            return new FileDocument($icon);
        }

        return new FileDocument(APP_DIR.'public/icons/module.png');
    }

    public function folder($folder)
    {
        $vendorPackages = $this->packages->where(['vendor' => $folder]);
        if (count($vendorPackages) === 0) {
            return $this->onFolderNotFound();
        }
        $controller = new VendorFolder($folder, $vendorPackages);

        return $controller->generateContent();
    }

    public function generateDocument()
    {
        if ($this->login() == false) {
            $doc = new Page();
            $doc->content = new HttpError(401);

            return $doc;
        }

        return parent::generateDocument();
    }

    public function wrapContent($content)
    {
        
        $template = new Template('layout.php',[
            'breadcrumbs' => Breadcrumbs::instance(),
            'contents' => $content,
                ], [
            'css' => [
                'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css',
                \Sledgehammer\WEBROOT.'css/devutils.css',
            ]
        ]);

        return $template;
    }

    public function onSessionStart()
    {
        return false;
    }

    /**
     * @return Component
     */
    protected function getUnitTestList()
    {
        $tests = $this->project->getUnitTests();
        if (count($tests) == 0) {
            return new Html('<span class="muted">No tests found</span>');
        }
        $iconPrefix = \Sledgehammer\WEBROOT.'icons/';
        $urlPrefix = $this->getPath().'project/tests/';
        $list = [
             $urlPrefix => array('icon' => 'play', 'label' => 'Run all'),
        ];
        foreach ($tests as $testfile) {
            $label = preg_replace('/(Test)?\.php$/i', '', $testfile);
            $label = preg_replace('/^test[s]?\//i', '', $label);
            $list[$urlPrefix.$testfile] = array('icon' => $iconPrefix.'test.png', 'label' => $label);
        }

        return new Nav($list, array('class' => 'nav nav-list'));
    }

    private function login()
    {
        $auth = new HttpAuthentication('DevUtils', function ($username, $password) {
            \Sledgehammer\su_exec($username, $password, 'true', $retval);

            return $retval === 0;
        });
        $credentials = $auth->authenticate();
        if ($credentials) {
            self::$username = $credentials['username'];
            self::$password = $credentials['password'];

            return true;
        }

        return false;
    }

    public static function suExec($command, &$retval = null)
    {
        return \Sledgehammer\su_exec(self::$username, self::$password, $command, $retval);
    }

    public static function sudo($command)
    {
        return \Sledgehammer\sudo(self::$username, self::$password, $command);
    }
}
