<?php
$success = flash('success');
$error = flash('error');
$info = flash('info');
?>
<?php if ($success): ?>
    <div class="flash flash--success">✓ <?= h($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="flash flash--error">⚠ <?= h($error) ?></div>
<?php endif; ?>
<?php if ($info): ?>
    <div class="flash flash--info">ℹ <?= h($info) ?></div>
<?php endif; ?>
