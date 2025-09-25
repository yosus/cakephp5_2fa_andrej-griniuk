<?php
/**
 * @var \App\View\AppView $this
 * @var string $secretDataUri
 * @var string $secret
*/
?>


<img src="<?=$secretDataUri ?>" />
<h3><?= $secret ?></h3>


<div class="users form content">
    <?= $this->Form->create(null, ['url' => []]) ?>
    <fieldset>
        <legend><?= __('Using the Authentication App, scan the QR code above or manually enter the text below the QR code') ?></legend>
        <legend><?= __('Test & Verify the numbers shown in the Authentication App below') ?></legend>
        <?= $this->Form->control('code', ['label' => 'Code to Verify']) ?>
        <?= $this->Form->hidden('secret', ['value' => $secret]);   ?>
    <?= $this->Form->button(__('Verify')); ?>
    <?= $this->Form->end() ?>
</div>