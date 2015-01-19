<?php

/**
 * @file
 * Default theme implementation for displaying EBSCO results.
 *
 * @see template_preprocess_ebsco_results()
 */
?>

<?php if ($records): ?>

  <?php print t('Showing'); ?>
  <strong><?php print $record_start; ?></strong> - <strong><?php print $record_end; ?></strong>
  <?php print t('of'); ?> <strong><?php print $record_count; ?></strong>
  <?php if ($search_view == 'basic'): ?>
    <?php print t('for search')?>: <strong>'<?php print check_plain($lookfor); ?>'</strong>
  <?php endif; ?>
  <?php if ($search_time): ?>
    , <?php print t('query time'); ?>: <?php print check_plain(round($search_time, 2)); ?>s
  <?php endif; ?>

  <?php print $sort_form; ?>
  <?php print $pager; ?>

  <ol class="search-results ebsco">
    <?php foreach ($records as $record): ?>
      <?php
        $id = check_plain($record->record_id());
        $recordUrl = url('ebsco/result', array('query' => array('id' => $id)));
        $fulltextUrl = url('ebsco/fulltext', array('query' => array('id' => $id)));
        $pdfUrl = url('ebsco/pdf', array('query' => array('id' => $id)));
      ?>
      <li>
        <div class="record-number floatleft">
          <?php print $record->result_id; ?>
        </div>

	<div class="doc-type floatleft">
		<?php if (isset($record->PubTypeId) && !empty($record->PubTypeId) && isset($record->publication_type) && !empty($record->publication_type) && $record->PubTypeId != "pt-unknown"): ?>
		<div class="pt-icon pt-<?php echo $record->PubTypeId; ?>"></div>
		<div><?php echo $record->publication_type; ?></div>
		<?php endif; ?>
	</div>

        <div class="result floatleft">
          <div class="span-2">
            <?php if ($record->small_thumb_link): ?>
              <a href="<?php print $recordUrl; ?>" class="_record_link">
                <img src="<?php print $record->small_thumb_link; ?>" class="book-jacket" alt="<?php print t('Book jacket'); ?>"/>
              </a>
            <?php endif; ?>
          </div>

          <div class="span-9">
            <div class="result-line1">
              <?php if ($record->access_level == '1'): ?>
                <p>
                  <?php
                      $label = '<strong>' . check_plain($record->db_label) . '</strong>'; 
                  ?>
                  <?php print sprintf(t('Cette notice de %s n\'est pas visible en mode non authentifié.'), $label); ?>
                  <br />
                </p>
              <?php elseif ($record->title): ?>
                <a href="<?php print $recordUrl; ?>" class="title _record_link">
                  <?php print $record->title; ?>
                </a>
              <?php endif; ?>
            </div>

            <div class="result-line2">
              <?php if (!empty($record->authors)): ?>
                <p>
                  <?php print t('by')." ";
			$authors = explode('<br />',$record->authors);
			foreach($authors as $i => $author) {
				if ($i < 3) echo ($i!=0?' ; ':'').preg_replace("/a>.*$/","a>",$author);
			}
			if ($i > 2) echo " et al.";
		  ?>
                </p>
              <?php endif; ?>

              <?php if (!empty($record->source)): ?>
                <p>
                  <?php print t('Published in'); ?>
                  <?php print $record->source; ?>
                </p>
              <?php endif; ?>
            </div>

            <div class="result-line3">
              <?php if (!empty($record->summary)): ?>
                <cite><?php print $record->summary; ?></cite>
                <br />
              <?php endif; ?>

            </div>

            <div class="result-line5">
              <?php if ($record->full_text_availability): ?>
		<div class="clearfix">
		  <div class="item-status item-web">
		    <i class="fa fa-globe"> </i>
		  </div>
		  <div class="webressources">
		    <a href="<?php print $fulltextUrl; ?>" target="_blank">Consulter en html</a>
		  </div>
		</div>
              <?php endif; ?>

              <?php if ($record->pdf_availability): ?>
		<div class="clearfix">
		  <div class="item-status item-pdf">
                    <i class="fa fa-file-pdf-o"> </i>
                  </div>
                  <div class="webressources">
                    <a href="<?php print $pdfUrl; ?>" target="_blank">Consulter le PDF</a>
                  </div>                
		</div>
              <?php endif; ?>

	      <?php if (!empty($record->custom_links)): ?>
		<?php foreach ($record->custom_links as $link): ?>
		  <div class="clearfix">
                    <div class="item-status item-web">
		      <i class="fa fa-globe"> </i>
		    </div>
		    <div class="webressources">
                      <a href="<?php print $link['Url']; ?>" target="_blank" title="<?php print $link['MouseOverText']; ?>" class="external-link">
                        <?php print $link['MouseOverText']; ?>
		      </a>
		    </div>
		  </div>
		<?php endforeach; ?>
	      <?php endif; ?>

	      <?php if ($record->access_level == '1'): ?>
		<div class="clearfix">
                   <div class="item-status item-web">
                     <i class="fa fa-globe"> </i>
                   </div>
                   <div class="webressources">
                     <a href="/cas">S'indentifier pour voir les notices masquées</a>
                   </div>
                 </div>
	      <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="clear"></div>
      </li>
    <?php endforeach; ?>
  </ol>
  <?php print $pager; ?>

<?php elseif (!empty($lookfor)) : ?>
  <h2><?php print 'Votre recherche n\'a donné aucun résultat';?></h2>
  <?php print search_help('search#noresults', drupal_help_arg()); ?>
<?php endif; ?>

<div id="spinner" class="spinner"></div>
