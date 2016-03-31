<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <base href="<?php echo Yii::app()->homeUrl; ?>">

        <title><?= CHtml::encode($this->pageTitle) ?></title>

        <link rel="shortcut icon" type="image/png" href="images/logo.png">
        <link rel="apple-touch-icon" href="images/logo.png" />

        <meta name="description" content="<?=$this->tags['description'];?>" />

        <meta name="viewport" content="width=device-width, initial-scale=1">

	<?php foreach($this->tags as $_tag_type => $_tag_data): ?>
	<meta property="og:<?=$_tag_type?>" content="<?=$_tag_data;?>"/>
	<?php endforeach; ?>

	<link rel="canonical" href="<?=$this->tags['url']?>" />
        
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:site" content="spull_nl">
        <meta name="twitter:title" content="<?=$this->tags['title'];?>">
        <meta name="twitter:description" content="<?=$this->tags['description'];?>">
        <meta name="twitter:creator" content="spull_nl">
        <meta name="twitter:image:src" content="http://spull.nl/images/spull_facebook_img.png">
        <meta name="twitter:domain" content="http://spull.nl">

	<link rel="stylesheet" type="text/css" href="<?= Yii::app()->baseUrl ?><?php echo Common::path() ?>/css/app.css" />
    </head>

    <body ng-app="peerbieb" ng-strict-di>
        <div id="fb-root"></div>

        <?= $content ?>
	
	<script data-cache-key="<?php echo Common::path(); ?>" type="text/javascript" src="<?php echo Common::path('dist/min/peerbieb.min.js'); ?>"></script>
    </body>
</html>