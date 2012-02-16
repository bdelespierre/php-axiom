<h1><?=Axiom::locale()->i18n('http500.title')?></h1>
<?php if (isset($error_code)): ?>
<p><?=Axiom::locale()->i18n('http500.message_with_code', $error_code)?></p>
<?php else: ?>
<p><?=Axiom::locale()->i18n('http500.message')?></p>
<?php endif ?>
