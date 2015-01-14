<?php

/**
 * @file
 * Default theme implementation for displaying EBSCO results.
 *
 * @see template_preprocess_ebsco_results()
 */
?>

<?php if ($records): ?>

  <?php echo '<pre>' ?>
    <?php var_dump($records); ?>
  <?php echo '</pre>'; ?>


<?php endif; ?>
