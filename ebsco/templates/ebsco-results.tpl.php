<?php

/**
* @file
* Default theme implementation for displaying EBSCO results.
*
* @see template_preprocess_ebsco_results()
*/
?>

<?php if ($records): ?>
  <?php print $pager; ?>
  <div class="clearfix" id="sortAndPerPage">
    <div class="page_links">
      <span class="page_entries">
        <strong><?php print $record_start; ?></strong> - <strong><?php print $record_end; ?></strong>
        <?php print t('of'); ?> <strong><?php print $record_count; ?></strong> résultats,
      </span>
      <?php if ($search_view == 'basic'): ?>
        <?php print t('for search')?>: <strong>'<?php print check_plain($lookfor); ?>'</strong>
        <?php endif; ?>
        <?php if ($search_time): ?>
          <?php print t('query time'); ?>: <?php print check_plain(round($search_time, 2)); ?>s
        <?php endif; ?>
    </div>

    <div class="search-widgets pull-right">
      <?php print $sort_form; ?>
    </div>

  </div>



  <div class="search-results ebsco">
    <?php foreach ($records as $record): ?>
      <?php
      $id = check_plain($record->record_id());



      $recordUrl = $record->p_link();


      $fulltextUrl = url('ebsco/fulltext', array('query' => array('id' => $id)));
      $pdfUrl = url('ebsco/pdf', array('query' => array('id' => $id)));
      if ($record->pdf_availability):
            $recordUrl = $pdfUrl;
      elseif (!empty($record->custom_links)):
        foreach ($record->custom_links as $link):
          if ($link['Name'] == 'Full Text Finder'):
            $recordUrl = $link['Url'];
          endif;
       endforeach;
     else:
       $recordUrl = $record->p_link();
     endif;


      ?>
      <div class='row-fluid'>
        <div class="span1">
          <?php print $record->result_id; ?>
        </div>
        <div class="record span11">
          <div class='row-fluid'>
            <div class="span11">
            <span class="pull-left hidden-sm">
            <?php if (isset($record->PubTypeId) && !empty($record->PubTypeId) && isset($record->publication_type) && !empty($record->publication_type) && $record->PubTypeId != "pt-unknown"): ?>
              <div class="pt-icon pt-<?php echo $record->PubTypeId; ?>"></div>
              <?php //echo $record->publication_type; ?>
            <?php endif; ?>
            <?php if ((!empty($record->summary)) || (!empty($record->subjects))): ?>
            <div class="MoreButton">
            <a class="accordion-toggle" data-toggle="collapse" data-parent="#MoreDetails" href="#collapseMore<?php print $record->result_id(); ?>" title="plus d'infos">
              <span class="fa-stack fa-lg">
                <i class="fa fa-circle fa-stack-2x"></i>
                <i class="fa fa-plus fa-stack-1x fa-inverse"></i>
                </span>
              </a>
            </div>
          <?php endif; ?>
          </span>
          <div class='record-detail'>
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

                <?php if ($record->db_label): ?>
                <span class="label label-inverse"><?php print check_plain($record->db_label); ?></span>
                <?php endif; ?>
                <h4 class='doc-title'><a href="<?php print $recordUrl; ?>" class="record_link">
                  <?php print $record->title; ?>
                </a></h4>
              <?php endif; ?>
            </div>

            <div class="result-line2">
              <?php if (!empty($record->authors)): ?>
                <p class='authors'>
                  <?php //print t('by')." ";
                  $authors = explode('<br />',$record->authors);
                  foreach($authors as $i => $author) {
                    if ($i < 3) echo ($i!=0?' ; ':'').preg_replace("/a>.*$/","a>",$author);
                  }
                  if ($i > 2) echo " et al.";
                  ?>
                </p>
              <?php endif; ?>

              <?php if (!empty($record->source)): ?>
                <p class='ref'>
                  <?php //print t('Published in'); ?>
                  <?php print $record->source; ?>
                </p>
              <?php endif; ?>
            </div>

            <div class="result-line3">

              <?php if (!empty($record->summary)): ?>
                <div class="accordion" id="MoreDetails">

                  <div id="collapseMore<?php print $record->result_id(); ?>" class="collapse">
                    <div class='record-abstract well'><?php print $record->summary; ?></div>

                      <?php if (!empty($record->subjects)): ?>
                        <div class='record-subjects'><i class="fa fa-tags"></i> <?php $subjects = str_replace('<br />', ', ', $record->subjects); print str_replace('*', '', $subjects); ?></div>
                      <?php endif; ?>

                  </div>
                </div>
            <?php endif; ?>



            </div>
            </div>
          </div>
          <?php if ($record->access_level == '1'): ?>
            <div class="span1">
              <div class="pull-right view-record hidden-sm">
                <a class="btn btn-success" href="/cas" title="S'identifier pour voir les notices masquées" class="external-link">
                  <span class="fa fa-lock"></span>
                </a>
              </div>
            </div>
          <?php elseif ($record->pdf_availability): ?>
              <div class="span1">
                <div class="pull-right view-record hidden-sm">
                  <a class="btn btn-danger" href="<?php print $pdfUrl; ?>" target='_blank' title="Consulter le PDF" class="external-link">
                    <span class="fa fa-file-pdf-o"></span>
                  </a>
                </div>
              </div>
            <?php elseif (!empty($record->custom_links)): ?>
                <?php foreach ($record->custom_links as $link): ?>
                  <?php if ($link['Name'] == 'Full Text Finder'): ?>
                <div class="span1">
                  <div class="pull-right view-record hidden-sm">
                    <a class="btn btn-primary" href="<?php print $link['Url']; ?>" target='_blank' title="<?php print $link['MouseOverText']; ?>" class="external-link">
                      <span class="fa fa-globe"></span>
                    </a>
                  </div>
                </div>
              <?php endif; ?>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="span1">
                <div class="pull-right view-record hidden-sm">
                  <a class="btn btn-info" href="<?php print $recordUrl; ?>" target='_blank' title="consulter la notice détaillée" class="external-link">
                    <span class="fa fa-eye"></span>
                  </a>
                </div>
              </div>
            <?php endif; ?>
          </div>
          <hr>
          </div>
      </div>
    <?php endforeach; ?>
  </div>
  <?php print $pager; ?>

<?php elseif (!empty($lookfor)) : ?>
  <h2><?php print 'Votre recherche n\'a donné aucun résultat';?></h2>
  <?php print search_help('search#noresults', drupal_help_arg()); ?>
<?php endif; ?>

<div id="spinner" class="spinner"></div>
