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

      <dl class="narrow-list navmenu">
        <?php if ($expanders): ?>
          <dt><?php print t('Expand Results'); ?></dt>
        <?php endif; ?>

        <?php foreach($expanders as $expander): ?>
          <dd>
            <input type="checkbox" name="filter[]" value="<?php print check_plain($expander['Action']); ?>"
              <?php print $expander['selected'] ? ' checked="checked"' : ''; ?> id="<?php print check_plain($expander['Id']); ?>"
            />
            <label for="<?php print check_plain($expander['Id']); ?>">
              <?php print check_plain(t($expander['Label'])); ?>
            </label>
          </dd>
        <?php endforeach; ?>

        <dd class="submit">
          <input type="submit" name="submit" class="form-submit" value="<?php print t('Update'); ?>" />
        </dd>
      </dl>

      <?php if (!empty($facets)): ?>
        <?php foreach ($facets as $title => $cluster): ?>
          <dl id="facet-<?php print check_plain(t($title)); ?>" class="narrow-list navmenu expandable">
            <dt>
              <span class="icon13 collapsed">
                <?php print check_plain(t($cluster['Label'])); ?>
              </span>
            </dt>
          </dl>

          <dl class="narrow-list navmenu offscreen" id="narrowGroupHidden_<?php print check_plain($title); ?>">
            <?php foreach ($cluster['Values'] as $index => $facet): ?>
              <?php if ($facet['applied']): ?>
                <dd>
                  <?php print check_plain($facet['Value']); ?>
                  <span class="icon16 tick"></span>
                </dd>
              <?php else: ?>
                <dd>
                  <input type="checkbox" name="filter[]" value="<?php print check_plain($facet['Action']); ?>"
                    id="filter<?php print check_plain($index); ?>"
                  />
                  <label for="filter<?php print check_plain($index); ?>">
                    <a href="<?php print url('ebsco/results', array('query' => array_merge($link_search_params, array('filter[]' => $facet['Action'])))); ?>">
                      <?php print check_plain($facet['Value']); ?>
                    </a>
                    (<?php print check_plain($facet['Count']); ?>)
                  </label>
                </dd>
              <?php endif; ?>
            <?php endforeach; ?>

            <dd>
              <p class="submit">
                <input type="submit" class="form-submit" name="submit" value="<?php print t('Update'); ?>" />
              </p>
              <a href="javascript:void(0)" class="expandable">
                <?php print t('Less'); ?> ...
              </a>
            </dd>
          </dl>
        <?php endforeach; ?>
      <?php endif; ?>
    </form>
  </div>
</div>
  <?php endif; ?>
</div>
