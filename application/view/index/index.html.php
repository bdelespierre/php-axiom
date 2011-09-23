<?php
/**
 * - NOTE -
 *
 * This page contains many <?=i18n(xxx)?> but
 * don't be affraid, these are just here to translate
 * content to current language.
 * These translations can be found in
 * /application/locale/langs/en.ini
 *
 * Cette page contient beaucoup de <?=i18n(xxx)?>
 * mais ne soyez pas effrayés, ce n'est q'une fonction
 * de traduction vers la langue courante de l'utilisateur.
 * Les traductions de cette page sont dans
 * /application/local/langs/fr.ini
 */
?>
<div>
	<img src="<?=src('img/axiom.png')?>" alt="axiom.png" />
	<p><?=i18n('axiom.subtitle')?></p>
</div>
<p>Version: <?=$axiom_version?></p>

<h2><?=i18n('axiom.getting_started')?></h2>
<p><?=i18n('axiom.getting_started.summary')?></p>
<p><?=i18n('axiom.getting_started.start_here')?></p>
<pre><?=realpath(__FILE__)?></pre>

<h3 class="layout"><?=i18n('axiom.getting_started.layout')?></h3>
<p><?=i18n('axiom.getting_started.layout.summary')?></p>
<pre><?=realpath(ViewManager::getLayoutFilePath())?></pre>

<h3 class="routing"><?=i18n('axiom.getting_started.routing')?></h3>
<p><?=i18n('axiom.getting_started.routing.summary')?></p>
<pre><?=realpath(APPLICATION_PATH . '/config/bootstrap/routes.php')?></pre>

<h3 class="lang"><?=i18n('axiom.getting_started.lang_management')?></h3>
<p><?=i18n('axiom.getting_started.lang_management.summary')?>
<pre><?=realpath(APPLICATION_PATH . '/locale/langs/')?></pre>

<h2><?=i18n('axiom.documentation.title')?></h2>
<p><?=i18n('axiom.documentation.summary')?></p>

<h2><?=i18n('axiom.stay_in_touch')?></h2>
<p><?=i18n('axiom.stay_in_touch.summary')?></p>
<a href="http://code.google.com/p/php-axiom/" target="_blank">http://code.google.com/p/php-axiom/</a>

<h3 class="contribute"><?=i18n('axiom.stay_in_touch.contribute.title')?></h3>
<p><?=i18n('axiom.stay_in_touch.contribute.summary')?></p>

<h3 class="report"><?=i18n('axiom.stay_in_touch.issue_reporting.title')?></h3>
<p><?=i18n('axiom.stay_in_touch.issue_reporting.summary')?></p>
<a href="http://code.google.com/p/php-axiom/issues/list" target="_blank">http://code.google.com/p/php-axiom/issues/list</a>

<h2><?=i18n('axiom.license.title')?></h2>
<pre id="licence"><?=file_get_contents(dirname(APPLICATION_PATH) . '/LICENSE.TXT')?></pre>