<?php
namespace Sledgehammer\Devutils;

use Sledgehammer\Mvc\Component\Breadcrumbs;
use Sledgehammer\Mvc\Component\Headers;
use Sledgehammer\Mvc\Component\HttpError;
use Sledgehammer\Mvc\Folder;

/**
 * Run utilities from inside a module folder.
 */
class UtilsFolder extends Folder
{
    private $module;

    public function __construct($module)
    {
        parent::__construct();
        $this->handle_filenames_without_extension = true;
        $this->module = $module;
    }

    public function file($filename)
    {
        $utils = $this->module->getUtilities();
        $util = $utils[$filename];
        Breadcrumbs::instance()->add($util->title, false);
        Util::$module = $this->module;
        $component = $util->generateContent();
        if (\Sledgehammer\is_valid_component($component)) {
            return new Headers($component, array(
                'title' => $util->title,
            ), true);
        }
        warning(get_class($util).'->generateContent() didn\'t return a Component');

        return new HttpError(500);
    }
}
