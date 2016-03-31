<!doctype html>
<html>
    <head>
	<meta charset="utf-8">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<base href="<?php echo Yii::app()->homeUrl; ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title><?= CHtml::encode($this->pageTitle) ?></title>

	<link href="https://fonts.googleapis.com/css?family=Ubuntu" rel="stylesheet" type="text/css">
	<link href="css/frontpage.css" rel="stylesheet" type="text/css">

	<link rel="icon" type="image/png" href="/images/favicon.png">	
	<link rel="apple-touch-icon" href="/images/favicon.png" />

	<meta name="description" content="Zoek in de boekenkasten van medestudenten. Leen en koop studieboeken van mensen in je eigen netwerk. Doe je mee?" />		

	<?php foreach ($this->tags as $_tag_type => $_tag_data): ?>
    	<meta property="og:<?= $_tag_type ?>" content="<?= $_tag_data; ?>"/>
	<?php endforeach; ?>

	<meta name="twitter:card" content="summary_large_image">
	<meta name="twitter:site" content="spull_nl">
	<meta name="twitter:title" content="<?= CHtml::encode($this->pageTitle) ?>">
	<meta name="twitter:description" content="Zoek in de boekenkasten van medestudenten. Leen en koop studieboeken van mensen in je eigen netwerk. Doe je mee?">
	<meta name="twitter:creator" content="spull_nl">
	<meta name="twitter:image:src" content="http://spull.nl/images/spull_facebook_img.png">
	<meta name="twitter:domain" content="http://spull.nl">

    </head>

    <body>
	<div id="fb-root"></div>

	<?= $content; ?>

	<script type="text/javascript">
            (function (i, s, o, g, r, a, m) {
                i['GoogleAnalyticsObject'] = r;
                i[r] = i[r] || function () {
                    (i[r].q = i[r].q || []).push(arguments)
                }, i[r].l = 1 * new Date();
                a = s.createElement(o),
                        m = s.getElementsByTagName(o)[0];
                a.async = 1;
                a.src = g;
                m.parentNode.insertBefore(a, m)
            })(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');

            ga('create', 'UA-1481292-15', 'spull.nl');
            ga('send', 'pageview');
	</script>

    </body>
</html>