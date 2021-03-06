<?php

/**
 * @file
 * Displays the advanced search form.
 *
 * @see template_preprocess_ebsco_advanced_search()
 */
?>

<div class="container-inline ebsco-advanced">
  <?php print $search_form; ?>
</div>

<div class="offscreen" id="advanced-row-template">
  <fieldset id="edit-groupNN" class="form-wrapper _advanced-row">
    <div class="fieldset-wrapper">
      <div class="form-item form-type-select form-item-groupNN-bool">
        <select id="ebsco-advanced-search-boolNN" name="groupNN[bool]" class="form-select">
          <option value="AND" selected="selected">All terms</option>
          <option value="OR">Any terms</option>
          <option value="NOT">No terms</option>
        </select>
      </div>
      <div class="form-item form-type-textfield form-item-groupNN-lookfor">
        <input title="Enter the terms you wish to search for." type="text" id="ebsco-advanced-search-lookforNN" name="groupNN[lookfor]" value="" size="30" maxlength="128" class="form-text" />
      </div>
      <div class="form-item form-type-select form-item-groupNN-type">
        <label for="ebsco-advanced-search-type1">in</label>
        <select id="ebsco-advanced-search-typeNN" name="groupNN[type]" class="form-select">
          <option value="AllFields" selected="selected">All Text</option>
          <option value="Title">Title</option>
          <option value="Author">Author</option>
          <option value="Subject">Subject terms</option>
        </select>
      </div>
      <div class="delete-search">
        <a href="#" class="delete _delete_row" id="delete_search_link_NN">
          <?php print t('Remove Search Field'); ?>
        </a>
      </div>
    </div>
  </fieldset>
</div>