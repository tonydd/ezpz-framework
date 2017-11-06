<?php
/**
 * @var Renderer $this
 */

$allCdnCss = Registry::getInstance()->getValue('cdn/css');

foreach ($allCdnCss as $cssUrl) {
    echo "<link rel='stylesheet' href='$cssUrl' />";
}
?>
<link rel="stylesheet" href="<?php echo $this->getCSSUrl();?>" />