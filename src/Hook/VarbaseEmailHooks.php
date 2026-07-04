<?php

declare(strict_types=1);

namespace Drupal\varbase_email\Hook;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\file\Entity\File;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Hook implementations for the Varbase Email module.
 */
class VarbaseEmailHooks {

  use StringTranslationTrait;

  /**
   * Constructs a VarbaseEmailHooks object.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   * @param \Drupal\Core\File\FileUrlGeneratorInterface $fileUrlGenerator
   *   The file URL generator.
   */
  public function __construct(
    protected LanguageManagerInterface $languageManager,
    protected ConfigFactoryInterface $configFactory,
    protected RequestStack $requestStack,
    protected FileUrlGeneratorInterface $fileUrlGenerator,
  ) {}

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme($existing, $type, $theme, $path) {

    $templates = $path . '/templates';

    $return['email'] = [
      'template' => 'varbase_emails',
      'path' => $templates,
      'variables' => [
        'email' => NULL,
      ],
      'mail theme' => TRUE,
    ];

    return $return;
  }

  /**
   * Prepares variables for varbase_emails.html.twig templates.
   *
   * Implements template_preprocess_email().
   */
  #[Hook('preprocess_email')]
  public function preprocessEmail(&$variables) {

    $email = $variables['email'];

    $language = $this->languageManager->getCurrentLanguage();
    $theme_id = $this->configFactory->get('system.theme')->get('default');
    $site_config = $this->configFactory->get('system.site');

    $request = $this->requestStack->getCurrentRequest();
    $host = $request->getSchemeAndHttpHost();

    // Add the default email styling library with the direction of LTR or RTL.
    $email->addLibrary('varbase_email/default.email-style.' . $language->getDirection());

    // Default we use the logo image.
    if (theme_get_setting('email_logo_default', $theme_id)) {
      $variables['logo'] = $host . theme_get_setting('logo.url', $theme_id);
    }
    else {
      $fid = theme_get_setting('email_logo_upload', $theme_id);
      if ($fid && is_array($fid) && count($fid)) {
        $file = File::load($fid[0]);
        if ($file) {
          $url = $file->createFileUrl();
          $variables['logo'] = $url;
        }
      }
      elseif (theme_get_setting('email_logo_path', $theme_id)) {
        $uri = theme_get_setting('email_logo_path', $theme_id);
        $scheme = StreamWrapperManager::getScheme($uri);

        if ($scheme) {
          $variables['logo'] = $this->fileUrlGenerator->generateAbsoluteString($uri);
        }
        else {
          $variables['logo'] = $host . $this->fileUrlGenerator->generateAbsoluteString($uri);
        }
      }
      else {
        $variables['logo'] = $host . theme_get_setting('logo.url', $theme_id);
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
