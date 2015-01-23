<?php

/**
 * @file
 * Display the sidebar block with facets filters
 *
 * @see template_preprocess_ebsco_side_facets()
 */
?>

<?php
  $limiters = array_slice($limiters, 0, 3);
?>

<div class="sidegroup">
  <?php if ($record_count >= 0): ?>
    <div id="facets" class="facets sidenav">
      <div class="top-panel-heading panel-heading">
        <button data-target="#facet-panel-collapse" data-toggle="collapse" class="facets-toggle" type="button">
          <span class="sr-only">Déplier les facettes</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <h2><?php print t('Narrow Search')?></h2>
      </div>
<div id="facet-panel-collapse" class="panel-group collapse" style="height: 0px;">
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
        <dl class="narrow-list navmenu filters">
          <dt><?php print t('Remove Filters'); ?></dt>
          <?php foreach ($filters as $filter): ?>
            <?php
                $removeLink = remove_filter_link($filter);
            ?>
            <dd>
              <a href="<?php print $removeLink; ?>" class="icon13 expanded">
                <?php print t($filter['displayField']); ?>: <?php print t($filter['displayValue']); ?>
              </a>
            </dd>
          <?php endforeach; ?>
        </dl>
      <?php endif; ?>


      <div class="panel panel-default facet_limit eds-limiters">
        <div class="collapse-toggle panel-heading collapsed " data-toggle="collapse" data-target="#facet-limiters">
          <h5 class="panel-title">
            <a data-no-turbolink="true" href="#"><?php print t('Limit Results'); ?></a>
          </h5>
        </div>
        <div id="facet-limiters" class="panel-collapse facet-content collapse in" style="height: auto;">
          <div class="panel-body facet-panel">
            <ul class="facet-values list-unstyled">
        <?php foreach ($limiters as $limiter): ?>
          <li>
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
              <input type="checkbox" name="filter[]" value="<?php print check_plain(str_replace('value', 'y', $limiter['Action'])); ?>"
                <?php print $limiter['selected'] ? ' checked="checked"' : ''; ?> id="<?php print check_plain($limiter['Id']); ?>"
              />
              <label for="<?php print check_plain($limiter['Id']); ?>">
                <?php print check_plain(t($limiter['Label'])); ?>
              </label>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</div>

      <div class="panel panel-default facet_limit eds-expanders">
        <div class="collapse-toggle panel-heading collapsed " data-toggle="collapse" data-target="#facet-expanders">
          <h5 class="panel-title">
            <a data-no-turbolink="true" href="#"><?php print t('Expand Results'); ?></a>
          </h5>
        </div>
        <div id="facet-expanders" class="panel-collapse facet-content collapse in" style="height: auto;">
          <div class="panel-body facet-panel">
            <ul class="facet-values list-unstyled">
        <?php foreach($expanders as $expander): ?>
          <li>
            <input type="checkbox" name="filter[]" value="<?php print check_plain($expander['Action']); ?>"
              <?php print $expander['selected'] ? ' checked="checked"' : ''; ?> id="<?php print check_plain($expander['Id']); ?>"
            />
            <label for="<?php print check_plain($expander['Id']); ?>">
              <?php print check_plain(t($expander['Label'])); ?>
            </label>
          </li>
        <?php endforeach; ?>

        <li class="submit">
          <input type="submit" name="submit" class="form-submit" value="<?php print t('Update'); ?>" />
        </li>
     </ul>
    </div>
  </div>
</div>

      <?php if (!empty($facets)): ?>
        <?php foreach ($facets as $title => $cluster): ?>
     <div class="panel panel-default facet_limit eds-<?php print check_plain(t($title)); ?>">
        <div class="collapse-toggle panel-heading collapsed " data-toggle="collapse" data-target="#facet-<?php print check_plain(t($title)); ?>">
          <h5 class="panel-title">
            <a data-no-turbolink="true" href="#"><?php print check_plain(t($cluster['Label'])); ?></a>
          </h5>
        </div>
        <div id="facet-<?php print check_plain(t($title)); ?>" class="panel-collapse facet-content collapse in" style="height: auto;">
          <div class="panel-body facet-panel">
            <ul class="facet-values list-unstyled">

            <?php foreach ($cluster['Values'] as $index => $facet): ?>
              <?php if ($facet['applied']): ?>
                <li>
                  <?php print check_plain($facet['Value']); ?>
                  <span class="icon16 tick"></span>
                </li>
              <?php else: ?>
                <li>
                  <input type="checkbox" name="filter[]" value="<?php print check_plain($facet['Action']); ?>"
                    id="filter<?php print check_plain($index); ?>"
                  />
                  <label for="filter<?php print check_plain($index); ?>">
                    <a href="<?php print url('ebsco/results', array('query' => array_merge($link_search_params, array('filter[]' => $facet['Action'])))); ?>">
                      <?php print check_plain($facet['Value']); ?>
                    </a>
                    (<?php print check_plain($facet['Count']); ?>)
                  </label>
                </li>
              <?php endif; ?>
            <?php endforeach; ?>
           </ul>
    </div>
  </div>
</div>      
            <div class="submit">
                <input type="submit" class="form-submit" name="submit" value="<?php print t('Update'); ?>" />
            </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </form>
  </div>
</div>
  <?php endif; ?>
</div>
