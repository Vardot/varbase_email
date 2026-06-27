<?php

namespace Drupal\varbase_email\Hook;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ThemeSettingsProvider;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Object-oriented hook implementations for Varbase Email.
 *
 * Drupal 11 replaces procedural hooks with methods carrying the #[Hook]
 * attribute (https://www.drupal.org/node/3442349). The real logic lives here
 * and uses dependency injection; the procedural functions in
 * varbase_email.module are kept as #[LegacyHook] shims that delegate here.
 */
class VarbaseEmailHooks {

  use StringTranslationTrait;

  /**
   * Constructs a VarbaseEmailHooks object.
   */
  public function __construct(
    protected ConfigFactoryInterface $configFactory,
    protected LanguageManagerInterface $languageManager,
    protected RequestStack $requestStack,
    protected FileUrlGeneratorInterface $fileUrlGenerator,
    protected EntityTypeManagerInterface $entityTypeManager,
    protected ThemeSettingsProvider $themeSettingsProvider,
  ) {}

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme($existing, $type, $theme, $path): array {
    $templates = $path . '/templates';

    return [
      'email' => [
        'template' => 'varbase_emails',
        'path' => $templates,
        'variables' => [
          'email' => NULL,
        ],
        'mail theme' => TRUE,
      ],
    ];
  }

  /**
   * Implements hook_preprocess_email().
   *
   * Prepares variables for the varbase_emails.html.twig template.
   */
  #[Hook('preprocess_email')]
  public function preprocessEmail(array &$variables): void {
    $email = $variables['email'];

    $language = $this->languageManager->getCurrentLanguage();
    $theme_id = $this->configFactory->get('system.theme')->get('default');
    $site_config = $this->configFactory->get('system.site');

    $request = $this->requestStack->getCurrentRequest();
    $host = $request->getSchemeAndHttpHost();

    // Add the default email styling library with the direction of LTR or RTL.
    $email->addLibrary('varbase_email/default.email-style.' . $language->getDirection());

    // By default we use the logo image.
    if ($this->themeSettingsProvider->getSetting('email_logo_default', $theme_id)) {
      $variables['logo'] = $host . $this->themeSettingsProvider->getSetting('logo.url', $theme_id);
    }
    else {
      $fid = $this->themeSettingsProvider->getSetting('email_logo_upload', $theme_id);
      if ($fid && is_array($fid) && count($fid)) {
        $file = $this->entityTypeManager->getStorage('file')->load($fid[0]);
        if ($file) {
          $variables['logo'] = $file->createFileUrl();
        }
      }
      elseif ($this->themeSettingsProvider->getSetting('email_logo_path', $theme_id)) {
        $uri = $this->themeSettingsProvider->getSetting('email_logo_path', $theme_id);
        $scheme = StreamWrapperManager::getScheme($uri);

        if ($scheme) {
          $variables['logo'] = $this->fileUrlGenerator->generateAbsoluteString($uri);
        }
        else {
          $variables['logo'] = $host . $this->fileUrlGenerator->generateAbsoluteString($uri);
        }
      }
      else {
        $variables['logo'] = $host . $this->themeSettingsProvider->getSetting('logo.url', $theme_id);
      }
    }

    if ($site_config) {
      $variables['site_link'] = TRUE;
      $variables['site_name'] = $site_config->get('name');
      if ($site_config->get('slogan')) {
        $variables['site_slogan'] = $site_config->get('slogan');
      }
    }
    else {
      $variables['site_name'] = $this->t('Varbase');
      $variables['site_slogan'] = '"' . $this->t('The Ultimate Drupal CMS Starter Kit') . '"';
    }

    $variables['body'] = $email->getBody();
    $variables = array_merge($variables, $email->getVariables());
  }

}
