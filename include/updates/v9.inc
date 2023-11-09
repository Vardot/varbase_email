<?php

/**
 * @file
 * Contains varbase_email_update_9###(s) hook updates.
 */

use Vardot\Installer\ModuleInstallerFactory;

/**
 * Issue #3272285: Switch to use the Module Installer Factory.
 *
 * In the Varbase Email module.
 */
function varbase_email_update_90001() {

  // Only if the Swift Mailer and the Mail System modules were enabled.
  if (\Drupal::moduleHandler()->moduleExists('swiftmailer')
    && \Drupal::moduleHandler()->moduleExists('mailsystem')) {
    // Set the weight of the module after installation of list of modules.
    // To make sure that any hook or event subscriber worker
    // after all used modules.
    ModuleInstallerFactory::setModuleWeightAfterInstallation('varbase_email', '', [
      'swiftmailer',
      'mailsystem',
    ]);
  }
}
