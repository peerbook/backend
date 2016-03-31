<?php header("Content-type: application/xml; charset=\"utf-8\""); ?>
<?php echo '<?'; ?>xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" 
  xmlns:i="http://www.google.com/schemas/sitemap-image/1.1">

	<url>
		<loc>https://spull.nl</loc>
	</url>
	
	<?php foreach($pages as $page): ?>
	<url>
		<loc>https://spull.nl/page/<?=$page?></loc>
	</url>
	<?php endforeach; ?>
	
	<?php foreach($other as $link): ?>
	<url>
		<loc>https://spull.nl/<?=$link?></loc>
	</url>
	<?php endforeach; ?>
	
</urlset>