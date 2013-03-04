<?php
/**
* @file view/last.php
* @ingroup gino-pressReview
* @brief Template per la vista ultime rassegne stampa
*
* Variabili disponibili:
* - **section_id**: attributo id del tag section
* - **title**: titolo della vista
* - **feed**: link ai feed rss
* - **items**: template per ciascuna delle ultime rassegne stampa (decisi da opzioni)   
* - **archive**: link all'archivio completo
*
* @version 0.1
* @copyright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @authors Marco Guidotti guidottim@gmail.com
* @authors abidibo abidibo@gmail.com
*/
?>
<section id="<?= $section_id ?>">
<header>
	<h1 class="left"><?= $title ?></h1>
	<div class="right feed">
		<?= $feed ?>
	</div>
	<div class="null"></div>
</header>
<? if(count($items)): ?>
<? foreach($items as $item): ?>
	<?= $item ?>
<? endforeach ?>
<p class="archive"><?= $archive ?></p>
<? else: ?>
<p><?= _('Non risultano rassegne stampa registrate') ?></p>
<? endif ?>
</section>