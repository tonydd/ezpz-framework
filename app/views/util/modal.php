<?php
/**
 * @var Renderer $this
 */

?>

<div id="<?php echo $data['modal-id'];?>" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo $data['modal-title'];?></h4>
            </div>
            <div class="modal-body">
                <?php $this->_include($data['modal-include']);?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
            </div>
        </div>

    </div>
</div>
