<?php
/**
* @file last.php
* @brief Template ultimi elementi rassegna stampa
*
* Variabili disponibili:
* - **section_id**: string, attributo id per la section
* - **feed_url**: string, url feed RSS
* - **items**: array, array di oggetti Gino.App.PressReview.PressReviewItem
* - **archive_url**: string, url archivio
*
* @copyright 2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @author Marco Guidotti guidottim@gmail.com
* @author abidibo abidibo@gmail.com
*/
?>
<? namespace Gino\App\PressReview; ?>
<? //@cond no-doxygen ?>
<section id="<?= $section_id ?>">
    <h1><?= _('Rassegna stampa') ?> <a class="fa fa-rss" href="<?= $feed_url ?>"></a></h1>
    <? foreach($items as $item): ?>
        <? $newspaper = new PressNewspaper($item->newspaper, $item->getController()); ?>
        <article>
            <div class="row">
                <div class="col-md-2">
                <? if ($newspaper->logo): ?>
                        <p><img class="img-responsive" src="<?= $newspaper->logoUrl() ?>" alt="<?= $newspaper->name ?>"></p>
                <? endif ?>
                </div>
                <div class="col-md-10">
                    <p><?= \Gino\htmlChars($newspaper->name) ?> - <?= \Gino\dbDateToDate($item->date, '/') ?></p>
                    <p><a href="<?= $item->resourceUrl() ?>"><?= \Gino\htmlChars($item->ml('title')) ?></a></p>
                </div>
            </div>
        </article>
    <? endforeach ?>
</section>

<? // @endcond ?>
