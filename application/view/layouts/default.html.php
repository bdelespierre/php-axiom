<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?=$lang?>" lang="<?=$lang?>">
	<head>
		<title><?=$title?></title>
        <meta name="keywords" lang="<?=$lang?>" content="<?=implode(',',$keywords)?>" />
        <meta name="description" content="<?=$description;?>" />
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta http-equiv="Content-Language" content="<?=$lang?>" />
        <link rel="stylesheet" href="<?=src('css/style.css')?>" type="text/css" media="screen" />
        <script type="text/javascript" src="<?=src('js/jquery-1.7.min.js')?>"></script>
        <script type="text/javascript" src="<?=src('js/jquery.sprintf.js')?>"></script>
        <script type="text/javascript" src="<?=src('js/sugar.js')?>"></script>
        <script type="text/javascript" src="<?=src('js/jquery.axiom.js')?>"></script>
	</head>
	<body>
		<?php if (!empty($alerts)): ?>
			<?php foreach ($alerts as $alert): ?>
			<p class="message-alert"><?=$alert?></p>
			<?php endforeach ?>
		<?php endif ?>
		<?php if (!empty($warnings)): ?>
            <?php foreach ($warnings as $warning): ?>
			<p class="message-warning"><?=$warning?></p>
			<?php endforeach ?>
		<?php endif ?>
		<?=$content?>
		<script type="text/javascript">
		$(function () {
			$.axiom.init({
    			baseUrl: '<?=$base_url?>',
    			lang   : '<?=$lang?>'
			});

			$.axiom.loadTranslations();
		});
		</script>
	</body>
</html>