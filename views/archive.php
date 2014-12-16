<?php
/**
* @file archive.php
* @brief Template archivio rassegna stampa
*
* Variabili disponibili:
* - **section_id**: string, attributo id per la section
* - **feed_url**: string, url feed RSS
* - **items**: array, array di oggetti Gino.App.PressReview.PressReviewItem
* - **open_form**: bool, se TRUE il form di ricerca deve essere espanso
* - **search_form**: html, form di ricerca
* - **pagination**: html, paginazione
*
* @copyright 2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @author Marco Guidotti guidottim@gmail.com
* @author abidibo abidibo@gmail.com
*/
?>
<? namespace Gino\App\PressReview; ?>
<? //@cond no-doxygen ?>
<section id="<?= $section_id ?>">
    <h1>
            <?= _('Rassegna stampa') ?> 
            <a class="fa fa-rss" href="<?= $feed_url ?>"></a>
            <span class="fa fa-search link" style="margin-right: 10px;" onclick="if($('press_review_form_search').style.display == 'block') $('press_review_form_search').style.display = 'none'; else $('press_review_form_search').style.display = 'block';"></span>
    </h1>
    <div id="press_review_form_search" style="display: <?= $open_form ? 'block' : 'none'; ?>;">
        <?= $search_form ?>
    </div>
    <? foreach($items as $item): ?>
        <? $newspaper = new PressNewspaper($item->newspaper, $item->getController()); ?>
        <article>
            <div class="row">
                <div class="col-md-12">
                    <div class="left" style="margin-right: 20px;">
                    <? if ($newspaper->logo): ?>
                            <p><img class="img-responsive" src="<?= $newspaper->logoUrl() ?>" alt="<?= $newspaper->name ?>"></p>
                    <? endif ?>
                    </div>
                    <div class="left">
                        <p><?= \Gino\htmlChars($newspaper->name) ?> - <?= \Gino\dbDateToDate($item->date, '/') ?></p>
                        <p><a href="<?= $item->resourceUrl() ?>"><?= \Gino\htmlChars($item->ml('title')) ?></a></p>
                    </div>
                </div>
            </div>
        </article>
    <? endforeach ?>
    <?= $pagination ?>
</section>
<? // @endcond ?>
