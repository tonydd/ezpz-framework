<?php
/**
 * @var Renderer $this
 */
?>

<!doctype html>
<html class="no-js" lang="fr">
<head>
    <meta charset="UTF-8">
    <!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge"><![endif]-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?php echo $this->getTitle();?></title>

    <?php $this->_include(Renderer::VIEW_CSS);?>
    <?php $this->_include(Renderer::VIEW_JS);?>
</head>
<body class="<?php echo $this->getTemplate();?>">

<?php $this->_include('header');?>

<div class="container">

<?php $this->_include('messages');?>