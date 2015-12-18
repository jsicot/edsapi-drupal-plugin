<?php

/**
 * @file
 * Display the sidebar block with facets filters
 *
 * @see template_preprocess_ebsco_side_facets()
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

<?php
  $limiters = array_slice($limiters, 0, 3);
?>

<?php if ($record_count >= 0): ?>
    <form name="updateForm" action="<?php print url('ebsco/results'); ?>" method="get">
      <?php if ($search_params): ?>
        <span>
          <?php foreach($search_params as $k1 => $v1): ?>
            <?php if (is_array($v1)): ?>
              <?php foreach($v1 as $k2 => $v2): ?>
                <?php if (is_array($v2)): ?>
                  <?php foreach($v2 as $k3 => $v3): ?>
                    <input type="hidden" name="<?php print $k1; ?>[<?php print $k2; ?>][<?php print $k3; ?>]" value="<?php print check_plain($v3); ?>" />
                  <?php endforeach; ?>
                <?php else: ?>
                  <input type="hidden" name="<?php print $k1; ?>[<?php print $k2; ?>]" value="<?php print check_plain($v2); ?>" />
                <?php endif; ?>
              <?php endforeach; ?>
            <?php else: ?>
              <input type="hidden" name="<?php print $k1; ?>" value="<?php print check_plain($v1); ?>" />
            <?php endif; ?>
          <?php endforeach; ?>
        </span>
      <?php endif; ?>

      <?php if (!empty($filters)): ?>
        <div class="box box-solid eds-filters">
	        <div class="box-header with-border">
					  <h3 class="block-title box-title"><?php print t('Remove Filters'); ?></h3>
					  <div class="box-tools">
					  	<button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
		  			 </div>
		 		 </div>

          <div class="box-body no-padding">
              <ul class="facet-values nav nav-pills nav-stacked facetapi-facetapi-links facetapi-facet-field- facetapi-processed">
          <?php foreach ($filters as $filter): ?>
            <?php
                $removeLink = remove_filter_link($filter);
            ?>
            <li>
            <div>
              <a href="<?php print $removeLink; ?>" class="filter-selected"><span class="fa fa-remove text-danger">
                <span class="label label-default text-white pull-right"><?php print t($filter['displayField']); ?>: <?php print t($filter['displayValue']); ?></span>
              </a>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
        </div>
    </div>
      <?php endif; ?>







      <div class="box box-solid eds-limiters">
        <div class="box-header with-border">
          <h3 class="block-title box-title"><?php print t('Limit Results') ?></h3>
          <div class="box-tools">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
          </div>
        </div>

        <div class="box-body no-padding">
          <ul class="facet-values nav nav-pills nav-stacked">
            <?php foreach ($limiters as $limiter): ?>
              <li>
              <div>
                <?php if ($limiter['Type'] == 'multiselectvalue'): ?>
                  <label for="<?php print check_plain($limiter['Id']); ?>">
                    <?php print t($limiter['Label']); ?>
                  </label><br />
                  <select name="filter[]" multiple="multiple" id="<?php print check_plain($limiter['Id']); ?>">
                    <option value=""><?php print t('All'); ?></option>
                    <?php foreach ($limiter['Values'] as $option): ?>
                      <option value="<?php print check_plain($option['Action']); ?>"<?php $option['selected'] ? ' selected="selected"' : ''; ?>>
                        <?php print check_plain($option['Value']); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                <?php else: ?>
                  <span class="pull-left"><input type="checkbox" name="filter[]" value="<?php print check_plain(str_replace('value', 'y', $limiter['Action'])); ?>"
                  <?php print $limiter['selected'] ? ' checked="checked"' : ''; ?> id="<?php print check_plain($limiter['Id']); ?>"
                  /></span>
                  <label for="<?php print check_plain($limiter['Id']); ?>">
                    <?php print check_plain(t($limiter['Label'])); ?>
                  </label>
                <?php endif; ?>
              </div>
              </li>
            <?php endforeach; ?>
            <div class="submit">
              <input type="submit" name="submit" class="btn btn-success btn-mini" value="<?php print t('Update'); ?>" />
            </div>
          </ul>
        </div>

      </div>

      <div class="box box-solid eds-limiters">
        <div class="box-header with-border">
          <h3 class="block-title box-title"><?php print t('Expand Results'); ?></h3>
          <div class="box-tools">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
          </div>
        </div>
          <div class="box-body no-padding">
            <ul class="facet-values nav nav-pills nav-stacked facetapi-facetapi-links facetapi-facet-field- facetapi-processed">
        <?php foreach($expanders as $expander): ?>
          <li>
          <div>
            <input type="checkbox" name="filter[]" value="<?php print check_plain($expander['Action']); ?>"
              <?php print $expander['selected'] ? ' checked="checked"' : ''; ?> id="<?php print check_plain($expander['Id']); ?>"
            />
            <label for="<?php print check_plain($expander['Id']); ?>">
              <?php print check_plain(t($expander['Label'])); ?>
            </label>
          </div>
          </li>
        <?php endforeach; ?>

        <div class="submit">
          <input type="submit" name="submit" class="btn btn-success btn-mini" value="<?php print t('Update'); ?>" />
        </div>
     </ul>
    </div>
</div>

      <?php if (!empty($facets)): ?>
        <?php foreach ($facets as $title => $cluster): ?>
      <?php foreach ($cluster['Values'] as $index => $facet):
         if ($facet['applied']):
            // $classFacet = "facet_limit-active";
            $collapsed = "";
            $collapse = "collapse in";
            $height = 'style="height: auto;"';
          else:
            // $classFacet = "";
            $collapsed = "collapsed";
            $collapse = "collapse";
            $height = '';
          endif;
        endforeach; ?>


         <div class="box box-solid eds-<?php print check_plain(t($title)); ?> <?php //print $classFacet; ?> block-facetapi">
	              <div class="box-header with-border">
					  <h3 class="block-title box-title"><?php print check_plain(t($cluster['Label'])); ?></h3>
					  <div class="box-tools">
					  	<button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
		  			 </div>
		 		 </div>

           <div class="box-body no-padding">

            <ul class="nav nav-pills nav-stacked facetapi-facetapi-links facetapi-facet-field-<?php print check_plain(t($title)); ?> facetapi-processed">

            <?php foreach ($cluster['Values'] as $index => $facet): ?>
              <?php if ($facet['applied']): ?>
                <li><div>
                   <?php print check_plain($facet['Value']); ?>
                 <span class="label label-default text-white pull-right"><span class="fa fa-check text-success"></span></span>
                </div>
                </li>
              <?php else: ?>
                <li>
                    <a href="<?php print url('ebsco/results', array('query' => array_merge($link_search_params, array('filter[]' => $facet['Action'])))); ?>">
                      <?php print check_plain($facet['Value']); ?>
                      <span class="label label-default text-white pull-right"><?php print check_plain($facet['Count']); ?></span>
                    </a>
                </li>
              <?php endif; ?>
            <?php endforeach; ?>
           </ul>
          </div>
      </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </form>
  <?php endif; ?>
