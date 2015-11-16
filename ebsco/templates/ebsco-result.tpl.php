<?php

/**
 * @file
 * Default theme implementation for displaying an EBSCO result.
 *
 * @see template_preprocess_ebsco_result()
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

<?php if ($record): ?>

<?php
    $id          = check_plain($record->record_id());
    $fulltextUrl = url('ebsco/fulltext', array('query' => array('id' => $id)));
    $pdfUrl      = url('ebsco/pdf', array('query' => array('id' => $id)));
?>

<div class="ebsco-record">
    <h1><?php print $record->title; ?></h1>

    <div class="record-toolbar">
        <?php if ($last_search): ?>
          <div class="floatright">
            <?php if ($last_search['previous']): ?>
              <a href="<?php print url('ebsco/result', array('query' => array('id' => $last_search['previous'], 'op' => 'Previous'))); ?>" class="_record_link">
                &laquo; <?php print t('Previous'); ?>
              </a>
            <?php endif; ?>
            #<?php print $last_search['current_index']; ?> of <?php print $last_search['count']; ?>
            <?php if ($last_search['next']): ?>
              <a href="<?php print url('ebsco/result', array('query' => array('id' => $last_search['next'], 'op' => 'Next'))); ?>" class="_record_link">
                <?php print t('Next'); ?> &raquo;
              </a>
            <?php endif; ?>
          </div>
          <div class="floatleft">
            <a href="<?php print "?{$last_search['query']}"; ?>">
              &laquo; <?php print t('Back to Results list'); ?>
            </a>
          </div>
          <div class="clear"></div>
        <?php endif; ?>
    </div>

    <div class="span-5">
      <ul class="external-links">
        <?php if($record->p_link): ?>
          <li>
            <a href="<?php print $record->p_link; ?>">
              <?php print t('View in EDS'); ?>
            </a>
          </li>
        <?php endif; ?>
        <?php if ($record->pdf_link): ?>
          <li>
            <a href="<?php print $pdfUrl; ?>" class="icon pdf fulltext">
              <?php print t('PDF full text'); ?>
            </a>
          </li>
        <?php endif; ?>
        <?php if ($record->full_text_availability): ?>
          <li>
            <a href="<?php if (!user_is_logged_in()) { print $fulltextUrl; } ?>#html" class="icon html fulltext">
              <?php print t('HTML full text'); ?>
            </a>
          </li>
        <?php endif; ?>
        <?php if (!empty($record->custom_links)): ?>
          <?php foreach ($record->custom_links as $link): ?>
            <li>
              <a href="<?php print $link['Url']; ?>" target="_blank" title="<?php print $link['MouseOverText']; ?>" class="external-link">
                <?php if ($link['Icon']): ?><img src="<?php print $link['Icon']?>" /><?php endif; ?><?php print $link['Name']; ?>
              </a>
            </li>
          <?php endforeach; ?>
        <?php endif; ?>
      </ul>
    </div>

    <div class="span-13">
      <table cellpadding="2" cellspacing="0" border="0" class="citation" summary="<?php print t('Bibliographic Details'); ?>">
        <?php foreach ($record->items as $item): ?>
          <?php if (!empty($item['Data'])): ?>
          <tr valign="top">
            <th width="150"><?php print t($item['Label']); ?>:</th>
            <td><?php print auto_link($item['Data']); ?></td>
          </tr>
          <?php endif; ?>
        <?php endforeach; ?>

        <?php if ($record->db_label): ?>
          <tr valign="top">
            <th width="150"><?php print t('Database'); ?>:</th>
            <td><?php print check_plain($record->db_label); ?></td>
          </tr>
        <?php endif; ?>

        <?php if ($record->full_text): ?>
          <tr id="html" valign="top">
            <td colspan="2" class="html">
              <?php print $record->full_text; ?>
            </td>
          </tr>
        <?php elseif ($record->access_level && !user_is_logged_in()): ?>
          <tr id="html" valign="top">
            <td colspan="2" class="html">
              <p>
                <?php print t('The full text cannot be displayed to guests.'); ?>
                <br />
                <?php $link = '<a href="' . url('user') . '">' . t('Login') . '</a>'; ?>
                <strong><?php print sprintf(t('%s for full access.'), $link); ?></strong>
              </p>
            </td>
          </tr>
        <?php endif; ?>
      </table>
    </div>

    <div class="span-4">
      <?php if ($record->medium_thumb_link): ?>
        <img src="<?php print check_url($record->medium_thumb_link); ?>" class="book-jacket" alt="<?php print t('Book jacket')?>"/>
      <?php endif; ?>
      <?php if ($record->publication_type): ?>
        <p><?php print check_plain($record->publication_type); ?></p>
      <?php endif; ?>
    </div>

    <div class="clear"></div>

</div>
<?php endif; ?>

<div id="spinner" class="spinner"></div>
