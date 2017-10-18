<?php
/**
 * @var Renderer $this
 */
?>
<script
        src="https://code.jquery.com/jquery-3.2.1.min.js"
        integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
        crossorigin="anonymous"></script>
<script
        src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"
        integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU="
        crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="<?php echo $this->getCoreJSUrl()?>"></script>
<script>
    <?php if (!empty($this->jsParameters)) : ?>
        <?php foreach ($this->jsParameters as $key => $val) {
            echo "Ez.setData('$key', JSON.parse('" . json_encode($val) . "'));\n";
        }?>
    <?php endif;?>
</script>
<script src="<?php echo $this->getJSUrl();?>"></script>