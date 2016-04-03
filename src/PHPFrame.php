<?php

namespace Sledgehammer\Devutils;

use Sledgehammer\Core\Object;
use Sledgehammer\Mvc\Component;

/**
 * Een View dat een url inlaad en direct laat zien.
 *
 * Een soort iframe principe, maar dan wordt de html direct in document gezet.
 */
class PHPFrame extends Object implements Component
{
    private $url;

    /**
     * @param $url absolute url, deze wordt namelijk vanuit dit script ingelezen
     */
    public function __construct($url)
    {
        $this->url = $url;
    }

    public function render()
    {
        // De output buffers flushen zodat de uitvoer direct getoond wordt.
        while (ob_get_level() > 0) {
            ob_end_flush();
        }
        $fp = fopen($this->url, 'r');
        if ($fp) {
            $bufferSize = 25; // Na elke X karakters de uitvoer doorsturen.
            while (!feof($fp)) {
                echo fgets($fp, $bufferSize);
                flush();
            }
            fclose($fp);
        }
    }
}
