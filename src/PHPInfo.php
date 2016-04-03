<?php


namespace Sledgehammer\Devutils;

use Sledgehammer\Core\Object;
use Sledgehammer\Mvc\Component;
/**
 * phpinfo() with TwBootstrap styling.
 */
class PHPInfo extends Object implements Component
{
    public function getHeaders()
    {
        return array(
            'title' => 'PHP Version '.phpversion(),
        );
    }

    public function render()
    {
        echo '<div class="phpinfo"><h2>PHP Version '.phpversion().'</h2>';
        ob_start();
        phpinfo();
        $phpinfo = ob_get_clean();
        $pos = strpos($phpinfo, '</table>');
        $html = substr($phpinfo, $pos + 8, -14); // strip default styling.
        $html = str_replace('<table', '<table class="table table-striped table-condensed"', $html);
        echo $html;
    }
}
