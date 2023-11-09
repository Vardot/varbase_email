<?php

/**
 * @file
 * Contains varbase_email_update_10###(s) hook updates.
 */

/**
 * Issue #3364734: Update Drupal Symfony Mailer from ~2.0 to ~2.3.0.
 *
 * And remove enabling Symfony Mailer Override
 * Backward Compatibility sub module.
 */
function varbase_email_update_100001() {

  // Only if the symfony_mailer_bc was enabled.
  if (\Drupal::moduleHandler()->moduleExists('symfony_mailer_bc')) {
    \Drupal::service('module_installer')->uninstall(['symfony_mailer_bc'], FALSE);
  }
}
