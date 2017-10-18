<?php
/**
 * @var Renderer $this
 */
?>

<?php if ($this->hasMessages()) : ?>
    <div class="messages">
        <?php foreach ($this->popMessages() as $messageInfo) : ?>
            <div class="alert alert-<?php echo $messageInfo['type']?>">
                <strong><?php echo ucfirst($messageInfo['type']);?> !</strong>
                <?php echo $messageInfo['label']; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>