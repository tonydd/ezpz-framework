<?php
/**
 * @var Renderer $this
 */
$user = $data['user'];
?>


<h2>Mon compte</h2>

<div id="form_user">
    <br/>
    <?php echo $user->generateForm();?>

</div>