<?php

/**
 * @file
 * Displays the basic search form block.
 *
 * @see template_preprocess_ebsco_basic_search()
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

<div class="container-inline">
  <?php if ($search_view == 'basic'): ?>
    <?php print $search_form; ?>
  <?php elseif ($search_view == 'advanced'): ?>
    <a href="<?php print url('ebsco/advanced', array('query' => array('edit' => 1))); ?>" class="small">
      <?php print t('Edit this Advanced Search'); ?>
    </a> |
    <a href="<?php print url('ebsco/advanced'); ?>" class="small">
      <?php print t('Start a new Advanced Search'); ?>
    </a> |
    <a href="<?php print url('ebsco/results'); ?>" class="small">
      <?php print t('Start a new Basic Search'); ?>
    </a>
    <br/>
    <?php print t('Your search terms'); ?> : <strong><?php print check_plain($lookfor); ?></strong>
  <?php endif; ?>
</div>
