<?php
/**
 * @var Renderer $this
 */
?>

<form method="POST" action="<?php echo $this->buildUrl('login', 'login')?>">
    <div class="form-group">
        <label>Mail</label>
        <input class="form-control" type="text" name="mail" id="mail" />
    </div>

    <div class="form-group">
        <label>Mot de passe</label>
        <input class="form-control" type="password" name="pwd" id="pwd" />
    </div>

    <input type="hidden" value="null" name="token" id="token" />

    <?php if ($data['url'] !== null) : ?>
    <input type="hidden" name="url" value="<?php echo $data['url'];?>" />
    <?php endif;?>

    <div class="form-group">
        <button type="submit" class="btn btn-primary">Connexion</button>
    </div>
</form>
