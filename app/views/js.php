<?php
/**
 * @var Renderer $this
 */

$allCdnJs = Registry::getInstance()->getValue('cdn/js');

foreach ($allCdnJs as $jsUrl) {
    echo "<script src='$jsUrl'></script>";
}
?>

<script src="<?php echo $this->getCoreJSUrl()?>"></script>
<script>
    <?php if (!empty($this->jsParameters)) : ?>
        <?php foreach ($this->jsParameters as $key => $val) {
            echo "Ez.setData('$key', JSON.parse('" . json_encode($val) . "'));\n";
        }?>
    <?php endif;?>
</script>
<script src="<?php echo $this->getJSUrl();?>"></script>