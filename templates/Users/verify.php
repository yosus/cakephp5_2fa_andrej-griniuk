<div class="users form content">
    <?= $this->Form->create(null, ['url' => ['action' => 'login']]) ?>
    <fieldset>
        <legend><?= __('Please enter your 2FA code') ?></legend>
        <?= $this->Form->control('code') ?>
    </fieldset>
    <?= $this->Form->button(__('Continue')); ?>
    <?= $this->Form->end() ?>
</div>