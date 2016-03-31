<?php
$internal_message = 'Sorry, er ging iets fout';
switch ($code) {
    case 404:
	$internal_message = 'Het object kan niet worden gevonden';
	break;
    case 403:
	$internal_message = 'Je mag deze actie niet uitvoeren';
	break;
}
?>
<header>
    <section>
	<h1>Sorry</h1>
	<h2><?= $internal_message ?></h2>
    </section>
</header>
<section>
    <article>
	<h1><?= $internal_message ?></h1>
	<p><?php echo $message; ?></p>
	<p><small>(<?php echo $code; ?>)</small></p>

	<p><?php echo CHtml::link('Meld dit probleem', array('/site/bug'), array('class' => 'btn')); ?> of <a href="/spull" class="btn">Ga terug naar Spull</a></p>
    </article>
</section>
