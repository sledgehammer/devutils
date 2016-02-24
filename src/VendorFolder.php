<?php

namespace Sledgehammer\Devutils;

use Sledgehammer\Mvc\VirtualFolder;

class VendorFolder extends VirtualFolder {

    /**
     * @var string
     */
    private $vendor;
    /**
     * @var Collection|Package[]
     */
    private $packages;

    public function __construct($vendor, $packages) {
        parent::__construct();
        $this->vendor = $vendor;
        $this->packages = $packages;
    }

    function dynamicFoldername($folder) {
        $packages = $this->packages->where(['name' => $this->vendor.'/'.$folder]);
        if (count($packages) === 1) {
            $controller = new PackageFolder($packages[0]);
            return $controller->generateContent();
        }
        return $this->onFolderNotFound();
        
    }

//
//	function generateContent() {
//		$name = $this->module->name.' module';
//		// getDocument()->title = $name;
//		$this->addCrumb($name, $this->getPath());
//		return parent::generateContent();
//	}
}

?>
