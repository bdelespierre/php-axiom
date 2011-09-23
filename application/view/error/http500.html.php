<h1><?=i18n('http500.title')?></h1>
<?php if (isset($error_code)): ?>
<p><?=i18n('http500.message_with_code', $error_code)?></p>
<?php else: ?>
<p><?=i18n('http500.message')?></p>
<?php endif ?>
