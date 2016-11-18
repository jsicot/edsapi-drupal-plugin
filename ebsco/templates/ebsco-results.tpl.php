<?php

/**
 * @file
 * Default theme implementation for displaying EBSCO results.
 *
 * @see template_preprocess_ebsco_results()
 *
 * Copyright [2014] [EBSCO Information Services]
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
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

  <?php if (!user_is_logged_in()): ?>
    <?php $link = '<a href="' . url('user') . '">' . t('Login') . '</a>'; ?>
    <p class="top-login-message">
      <?php print sprintf(t('Hello, Guest. %s for full access.'), $link); ?>
    </p>
  <?php endif; ?>

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
                      $link = '<a href="' . url('user') . '">' . t('Login') . '</a>';
                  ?>
                  <?php print sprintf(t('This result from %s cannot be displayed to guests.'), $label); ?>
                  <br />
                  <strong><?php print sprintf(t('%s for full access.'), $link); ?></strong>
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
                  <?php print t('by'); ?>
                  <?php print $record->authors; ?>
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

              <?php if (!empty($record->subjects)): ?>
                <strong><?php print t('Subjects'); ?></strong>:
                <span class="quotestart"><?php print str_replace('<br />', ', ', $record->subjects); ?></span>
              <?php endif; ?>
            </div>

            <?php if (!empty($record->custom_links)): ?>
              <div class="result-line4">
                <ul class="custom-links">
                  <?php foreach ($record->custom_links as $link): ?>
                    <li>
                      <a href="<?php print $link['Url']; ?>" target="_blank" title="<?php print $link['MouseOverText']; ?>" class="external-link">
                        <?php if ($link['Icon']): ?><img src="<?php print $link['Icon']?>" /><?php endif; ?><?php print $link['Name']; ?>
                      </a>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>

            <div class="result-line5">
              <?php if ($record->full_text_availability): ?>
                <a href="<?php print $fulltextUrl; ?>#html" class="icon html fulltext _record_link">
                  <?php print t('HTML full text'); ?>
                </a>
                &nbsp; &nbsp;
              <?php endif; ?>

              <?php if ($record->pdf_availability): ?>
                <a href="<?php print $pdfUrl; ?>" class="icon pdf fulltext">
                  <?php print t('PDF full text'); ?>
                </a>
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
  <h2><?php print t('Your search did not match any resources.');?></h2>
  <?php print search_help('search#noresults', drupal_help_arg()); ?>
<?php endif; ?>

<div id="spinner" class="spinner"></div>