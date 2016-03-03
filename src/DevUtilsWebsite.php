<?php

namespace Sledgehammer\Devutils;

use DirectoryIterator;
use Sledgehammer\Core\Collection;
use Sledgehammer\Core\HttpAuthentication;
use Sledgehammer\Core\Json;
use Sledgehammer\Core\Url;
use Sledgehammer\Mvc\Component\Breadcrumbs;
use Sledgehammer\Mvc\Component\HttpError;
use Sledgehammer\Mvc\Component\Nav;
use Sledgehammer\Mvc\FileDocument;
use Sledgehammer\Mvc\HtmlDocument;
use Sledgehammer\Mvc\Template;
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
        $composer = Json::decode(file_get_contents($path.'composer.json'));
        $this->project = new Package($composer->name);
        $this->project->path = $path;
        $this->packages = new Collection();
        $vendorDir = new DirectoryIterator(\Sledgehammer\VENDOR_DIR);
        foreach ($vendorDir as $entry) {
            if ($entry->isDot() || $entry->isFile() || in_array($entry->getFilename(), ['composer', 'bin'])) {
                continue;
            }
            $packageDirs = new DirectoryIterator($entry->getPathname());
            foreach ($packageDirs as $subentry) {
                if ($subentry->isDot() || $subentry->isFile()) {
                    continue;
                }
                $this->packages[] = new Package($entry->getFilename().'/'.$subentry->getFilename());
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

        // Unittests
//        if (file_exists($this->project->path.'tests')) {
            // tests are in the project root? extract tests
//            array_key_unshift($this->packages, 'project', new Module('project', $this->project->path));
//        } elseif (file_exists($this->project->path.'app/tests')) {
            // A non sledgehammer app? extract tests
//            array_key_unshift($this->packages, 'app', new Module('app', $this->project->path.'app'));
//        }
//        foreach ($this->packages as $identifier => $module) {
//            foreach ($module->getUnitTests() as $testfile) {
//                if (\Sledgehammer\text($testfile)->endsWith('Test.php')) {
//                    $label = substr($testfile, 0, -8);
//                } else {
//                    $label = substr($testfile, 0, -4);
//                }
//                $label = basename($label);
//                $unittestList['tests/'.$identifier.'/'.$testfile] = array('icon' => $iconPrefix.'test.png', 'label' => ucfirst($identifier).' - '.$label);
//            }
//        }
        $template = new Template('project.php', [
            'project' => $this->project->name,
//            'properties' => new DescriptionList($properties, array('class' => 'dl-horizontal')),
            'packages' => new Nav($this->packages->orderBy('name')->select('name', 'name'), ['class' => 'nav nav-list']),
//            'utilities' => new Nav($utilityList, array('class' => 'nav nav-list')),
//            'unittests' => new Nav($unittestList, array('class' => 'nav nav-list')),
                ], ['title' => $this->project->name.' project']);

        return $template;
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

    public function dynamicFoldername($folder)
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
            $doc = new HtmlDocument();
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
