<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "skynetaccessibilityscanner".
 *
 * Auto generated 18-12-2025 06:36
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
  'title' => 'SkynetAccessibility Scanner',
  'description' => 'WCAG Website Accessibility widget improves Typo3 website ADA compliance and browser experience for ADA, WCAG 2.1 & 2.2, Section 508, Australian DDA, European EAA EN 301 549, UK Equality Act (EA), Israeli Standard 5568, California Unruh, Ontario AODA, Canada ACA, German BITV, France RGAA, Brazilian Inclusion Law (LBI 13.146/2015), Spain UNE 139803:2012, JIS X 8341 (Japan), Italian Stanca Act and Switzerland DDA Standards.',
  'category' => 'plugin',
  'version' => '13.0.2',
  'state' => 'stable',
  'uploadfolder' => false,
  'clearcacheonload' => false,
  'author' => 'Skynet Technologies USA LLC',
  'author_email' => 'hello@skynetindia.info',
  'author_company' => 'Skynet Technologies USA LLC',
  'constraints' => 
  array (
    'depends' => 
    array (
      'typo3' => '13.0.0-13.9.99',
    ),
    'conflicts' => 
    array (
    ),
    'suggests' => 
    array (
    ),
  ),
  'autoload' => 
  array (
    'psr-4' => 
    array (
      'Skynettechnologies\\Skynetaccessibilityscanner\\' => 'Classes/',
    ),
  ),
);

