<?php

namespace Sledgehammer\Devutils;

use Sledgehammer\Mvc\Folder;

class VendorFolder extends Folder
{
    /**
     * @var string
     */
    private $vendor;
    /**
     * @var Collection|Package[]
     */
    private $packages;

    public function __construct($vendor, $packages)
    {
        parent::__construct();
        $this->vendor = $vendor;
        $this->packages = $packages;
    }

    public function folder($folder)
    {
        $packages = $this->packages->where(['name' => $this->vendor.'/'.$folder]);
        if (count($packages) === 1) {
            $controller = new PackageFolder($packages[0]);

            return $controller->generateContent();
        }

        return $this->onFolderNotFound();
    }

}
