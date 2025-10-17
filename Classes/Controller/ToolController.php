<?php
namespace Skynettechnologies\Skynetaccessibilityscanner\Controller;

use Skynettechnologies\Skynetaccessibilityscanner\AdaConstantModule\TypoScriptTemplateConstantEditorModuleFunctionController;
use Skynettechnologies\Skynetaccessibilityscanner\Property\TypeConverter\UploadedFileReferenceConverter;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfiguration;
use TYPO3\CMS\Tstemplate\Controller\TypoScriptTemplateModuleController;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\ConnectionPool;

/***
 *
 * This file is part of the "SkynetAccessibility Scanner" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2020
 *
 ***/

/**
 * ToolController
 */
class ToolController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * toolstyleRepository
     *
     * @var \Skynettechnologies\Skynetaccessibilityscanner\Domain\Repository\ToolRepository
     */
    protected $toolstyleRepository = null;

    public function __construct(
        \Skynettechnologies\Skynetaccessibilityscanner\Domain\Repository\ToolstyleRepository $toolstyleRepository
    ) {
        $this->toolstyleRepository = $toolstyleRepository;
    }

    protected $constantObj;

    protected $constants;

    /**
     * @var TypoScriptTemplateModuleController
     */
   
   
    protected $pObj;

    protected $contentObject = null;

    protected $pid = null;

    /**
     * Initializes this object
     *
     * @return void
     */
    public function initializeObject()
    {
        $this->contentObject = GeneralUtility::makeInstance('TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer');
        $this->constantObj = GeneralUtility::makeInstance(TypoScriptTemplateConstantEditorModuleFunctionController::class);
    }

    /**
     * Initialize Action
     *
     * @return void
     */
    public function initializeAction()
    {

        //GET CONSTANTs
        $this->constantObj->init($this->pObj);
        $this->constants = $this->constantObj->main();
    }

    /**
     * action list
     *
     * @return ResponseInterface
     */
    public function mainAction(): ResponseInterface
    {
        return $this->htmlResponse();
    }

    /**
     * action chatSettingsAction
     *
     * @return ResponseInterface
     */
    public function chatSettingsAction(): ResponseInterface
    {
  
        // Scanning & Monitoring PHP code

            
        // scanning & monitoring code start
        
        $domain_name = $_SERVER['HTTP_HOST'];
    

        // Add User domain on scanning & monitoring dashboard
          $arrDetails = [
                'website'        => base64_encode($domain_name), // Encode domain
                'platform'       => 'Typo3 CMS',
                'is_trial_period'=> 1,
                'name'           => $domain_name,
                'email'          => 'no-reply@' . $domain_name,
                'comapany_name'  => $domain_name,
                'package_type'   => '10-pages'
            ];
            // register user domain on scanning & monitoring dashboard
            $ch = curl_init('https://skynetaccessibilityscan.com/api/register-domain-platform');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $arrDetails);

            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                echo 'Curl error: ' . curl_error($ch);
            }
            curl_close($ch);

            // Decode API response
            $jsonStart = strpos($response, '{');
            if ($jsonStart !== false) {
                $jsonPart = substr($response, $jsonStart);
                $result = json_decode($jsonPart, true);
             
            } else {
                echo "Invalid response: " . $response;
            }       

        // Get Scan Detail API start
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://skynetaccessibilityscan.com/api/get-scan-detail',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POSTFIELDS => array(
                'website' => base64_encode($domain_name) // your domain
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $result = json_decode($response, true);
        // Fetch scan detail response data
        if (isset($result['data'][0])) {
            $row = $result['data'][0];

            $data['domain'] = $row['domain'] ?? '';
            $data['fav_icon'] = $row['fav_icon'] ?? '';
            $data['url_scan_status'] = $row['url_scan_status'] ?? 0;
            $data['scan_status']= $row['scan_status'] ?? 0;
            $data['total_selected_pages'] = $row['total_selected_pages'];
            $data['total_last_scan_pages'] = $row['total_last_scan_pages'];
            $data['total_pages'] = $row['total_pages'] ?? 0;
            $data['last_url_scan'] = $row['last_url_scan'] ?? 0;
            $data['total_scan_pages'] = $row['total_scan_pages'] ?? 0;
            $data['last_scan'] = $row['last_scan'] ?? null;
            $data['next_scan_date'] = $row['next_scan_date'] ?? null;
            $data['success_percentage'] = $row['success_percentage'] ?? '0';
            $data['scan_violation_total'] = $row['scan_violation_total'] ?? '0';
            $data['total_violations'] = $row['total_violations'] ?? 0;
            $data['package_name'] = $row['name'] ?? '';
            $data['package_id'] = $row['package_id'] ?? '';
            $data['page_views'] = $row['page_views'] ?? '';
            $data['package_price'] = $row['package_price'] ?? '';
            $data['subscr_interval'] = $row['subscr_interval'] ?? '';
            $data['end_date'] = $row['end_date'] ?? '';
            $data['website_id'] = $row['website_id'] ?? '';
            $data['is_trial_period'] = $row['is_trial_period'] ?? '';
            $data['dashboard_link'] = $result['dashboard_link'] ?? '';
            $data['total_fail_sum'] = $row['total_fail_sum'] ?? '';
              $data['is_expired'] = $row['is_expired'] ?? '';
        }
        // end Get scan detail API 

        // Get Scan Count API (Fetch the violation report data)
        $curl1 = curl_init();
        curl_setopt_array($curl1, array(
            CURLOPT_URL => 'https://skynetaccessibilityscan.com/api/get-scan-count',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POSTFIELDS => array(
                'website' => base64_encode($domain_name) // same as in first API
            ),
        ));

        $response1 = curl_exec($curl1);
     
        curl_close($curl1);

        $result1 = json_decode($response1, true);

            // Safely fetch values
        $with_rem     = $result1['scan_details']['with_remediation'] ?? [];
        $without_rem  = $result1['scan_details']['without_remediation'] ?? [];
        $widgetPurchased = $result1['widget_purchased'] ?? false;

        // Always assign to with_remediation (since your template uses it)
        if ($widgetPurchased === false || $widgetPurchased === "false" || $widgetPurchased == 0) {
            // Use with_remediation data
            $data['scan_details'] = [
                'with_remediation' => $without_rem,
            ];
        } else {
            // Use without_remediation data
            $data['scan_details'] = [
                'with_remediation' => $with_rem,
            ];
        }
        // end count detail
        //fetch package list 
        $payload = json_encode([
            'website' => base64_encode($domain_name)
        ]);

        $curl2 = curl_init();
        curl_setopt_array($curl2, array(
            CURLOPT_URL => 'https://skynetaccessibilityscan.com/api/packages-list',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
            CURLOPT_POSTFIELDS => $payload,  
        ));

        $response2 = curl_exec($curl2);
        $decoded = json_decode($response2, true);
            
        
        $activePackageId = $data['package_id'] ?? '';
        $activeInterval  = $data['subscr_interval'] ?? ''; // 'M' or 'Y'
        $allowedNames = ['Small Site', 'Medium Site', 'Large Site', 'Extra Large Site'];
        $plans = [];

        $websiteId = (string)($data['website_id'] ?? '');
        $today = new \DateTime('now', new \DateTimeZone('UTC'));
        $todayStr = $today->format('Y-m-d');

        // ----------------- Check active or expired -----------------
        $today = new \DateTime('now', new \DateTimeZone('UTC'));
        $todayStr = $today->format('Y-m-d');

        if (!empty($decoded['current_active_package'])) {
            foreach ($decoded['current_active_package'] as $key => $package) {
                if ((string)$key === $websiteId) {
                    $endDate = !empty($package['end_date']) ? new \DateTime($package['end_date'], new \DateTimeZone('UTC')) : null;
                    
                    if ($endDate && $endDate->format('Y-m-d') === $todayStr) {
                        if (!empty($decoded['expired_package_detail'][$websiteId])) {
                            $expiredPackage = $decoded['expired_package_detail'][$websiteId];
                            $data['expired_package'] = $expiredPackage;

                            $activePackageId = $expiredPackage['package_id'] ?? '';
                            $activeInterval  = $expiredPackage['subscr_interval'] ?? '';
                        }
                    } else {
                        $activePackageId = $package['package_id'] ?? '';
                        $activeInterval  = $package['subscr_interval'] ?? '';
                    }
                    break;
                }
            }


        } elseif (!empty($decoded['expired_package_detail'])) {
            // No active, fallback to expired package
            if (!empty($decoded['expired_package_detail'][$websiteId])) {
                $expiredPackage = $decoded['expired_package_detail'][$websiteId];
                $data['expired_package'] = $expiredPackage;

                $activePackageId = $expiredPackage['package_id'] ?? '';
                $activeInterval  = $expiredPackage['subscr_interval'] ?? '';
            }
        }

        // ----------------- Final price handling -----------------
        if (!empty($decoded['current_active_package'])) {
            $data1 = $decoded['current_active_package'];
            $firstKey = array_key_first($data1);
            if ($firstKey !== null) {
                $finalPrice = $data1[$firstKey]['final_price'] ?? 0;
                $data['final_price'] = $finalPrice;
            }
        } elseif (!empty($decoded['expired_package_detail'])) {
            $firstKey = array_key_first($decoded['expired_package_detail']);
            $finalPrice = $decoded['expired_package_detail'][$firstKey]['final_price'] ?? 0;
            $data['final_price'] = $finalPrice;
        }

        // ----------------- Plans loop -----------------
        foreach ($decoded['Data'] as $plan) {
            if (isset($plan['name']) && in_array($plan['name'], $allowedNames)) {
                $packageId = $plan['id'] ?? null;
                if (!$packageId) {
                    continue; // skip if no ID
                }

                $action = 'upgrade'; // default action

                // Check if current active package matches this plan
                if ($packageId == $activePackageId) {
                    $plan['interval'] = $activeInterval; // 'M' or 'Y'

                    // Check if subscription has expired
                    $endDateStr = $data['end_date'] ?? ''; // end date from backend
                    if ($endDateStr) {
                        $endDate = new \DateTime($endDateStr, new \DateTimeZone('UTC'));
                        if ($today <= $endDate) {
                            // Still active → allow cancel
                            $action = 'cancel';
                        } else {
                            // Expired → force upgrade
                            $action = 'upgrade';
                        }
                    } else {
                        // If no end_date, default to cancel for active package
                        $action = 'cancel';
                    }
                }

                $plan['action'] = $action; // attach action to plan

                $data['activePackageId'] = $activePackageId;
                $data['packageId']       = $packageId;
                $data['websiteId']       = $websiteId;

                // Later: Generate autologin link for upgrade/cancel button
                $plans[] = $plan;
                // Generate violation autologin link
                $curl4 = curl_init();
                curl_setopt_array($curl4, [   // should be $curl4, not $curl
                    CURLOPT_URL => 'https://skynetaccessibilityscan.com/api/generate-plan-action-link',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => [
                        'website_id'         => $websiteId,
                        'current_package_id' => $activePackageId,
                        'action'             => 'violation'
                    ],
                    CURLOPT_SSL_VERIFYPEER => false
                ]);
                $response4 = curl_exec($curl4);
                curl_close($curl4);

                // Decode JSON
                $decodedLink1 = json_decode($response4, true);

                // Get the action_link or fallback to #
                $violationLink = $decodedLink1['action_link'] ?? '#';

                // Send to Twig
                $data['violation_link'] = $violationLink;


            }
    }


        $data['plans'] = $plans;
        ?>
          <meta name="description" content="" />
    <link rel="mask-icon" href="" />
    <meta name="Generator" content="Drupal 9 (https://www.drupal.org)" />
    <meta name="MobileOptimized" content="width" />
    <meta name="HandheldFriendly" content="true" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <link rel="icon" href="https://www.skynettechnologies.com/sites/default/files/favicon_0.webp" type="image/png" />


</head>

<body class="layout-no-sidebars has-featured-top page-node-1860 path-node node--type-page scrolled scrolldown">

<!-- Section 1-->

<div id="section1" style="max-height: 90vh !important;
    overflow-y: overlay !important;">

<div class="dialog-off-canvas-main-canvas" data-off-canvas-main-canvas>
<div id="page-wrapper">
<div id="page">
<div id="main-wrapper" class="layout-main-wrapper clearfix">
<div id="main" class="container">
<div class="row row-offcanvas row-offcanvas-left clearfix">
<main class="main-content col" id="content" r ole="main">
<section class="section">
<div id="main-content" tabindex="-1"></div>
<div id="block-skynettechnologies-content" class="block block-system block-system-main-block">
    <div class="content">
        <article data-history-node-id="529" class="node node--type-page node--view-mode-full clearfix">
            <div class="node__content clearfix ">
                <div class="scanning-monitoring-app">
                    <div class="scans">

                        <p class="title">My Scans</p>

                        <!-- Status Section -->
                        <section class="status">
                            <div class="page-background"></div>
                            <div class="status-card">
                                <?php
                                $url_scan_status = $data['url_scan_status'] ?? 0;
                                $total_pages = $data['total_pages'] ?? 0;
                                $dashboard_link = $data['dashboard_link'] ?? '#';
                                ?>
                                   <span class="status-title">Sitemap</span>
                                    <span class="status-value <?= $data['is_expired'] ? 'status-paused' : ($url_scan_status < 2 ? 'status-progress' : '') ?>">
                                        <?php
                                        if (!empty($data['is_expired']) && $data['is_expired'] == 1) {
                                            echo "Scanning Paused";
                                        } elseif ($url_scan_status < 2) {
                                            echo "Generating Sitemap";
                                        } elseif ($url_scan_status == 2) {
                                            echo '<a style="color: black; font-weight: 700;" href="'.htmlspecialchars($dashboard_link).'" target="_blank">
                                                    <span style="font-size: 40px;">'.htmlspecialchars($total_pages).'</span> Pages
                                                </a>';
                                        } elseif ($url_scan_status == 3) {
                                            echo '<span style="color: orange;font-size: 15px; font-weight: 600;">Sitemap Not Generated</span>';
                                        }
                                        ?>
                                    </span>
                                    
                            </div>
                            <div class="status-card">
                                <span class="status-title">Scan Score</span>

                                <?php 
                                
                                        if (!empty($data['is_expired']) && $data['is_expired'] == 1): ?>
                                            <span class="status-value status-inactive">N/A</span>
                                        <?php else: ?>
                                            <?php if (($data['scan_violation_total'] ?? 0) == 0): ?>
                                                <span class="status-value status-inactive">N/A</span>
                                            <?php else: ?>
                                                <span class="status-value status-progress" style="cursor:pointer;" id="show-details-btn">
                                                    <?= $data['success_percentage'] ?? 0; ?>%
                                                    <div class="progress-bar">
                                                        <div class="progress-fill" style="width: <?= $data['success_percentage'] ?? 0; ?>%;"></div>
                                                    </div>
                                                    <div class="violations">
                                                        Violations: <span class="status-value" style="font-size: 15px;"><?= $data['total_fail_sum'] ?? 0; ?></span>
                                                    </div>
                                                </span>
                                            <?php endif; ?>
                                        <?php endif; ?>

             
                            </div>
                            <div class="status-card">
                                <span class="status-title">Last Scanned</span>
                                 <?php if (($data['url_scan_status'] ?? 0) < 2): ?>
                                  <span class="status-value status-inactive">
                                      <img src="../../../packages/skynetaccessibilityscanner/css/Image/not-shared.svg" 
                                          alt="" 
                                          title="Not Started">
                                      Not Started
                                  </span>

                              <?php elseif (($data['scan_status'] ?? 0) == 0): ?>
                                  <span class="status-value status-inactive">
                                      <img src="../../../packages/skynetaccessibilityscanner/css/Image/not-shared.svg" 
                                          alt="" 
                                          title="Not Started">
                                      Not Started
                                  </span>

                              <?php elseif (($data['scan_status'] ?? 0) == 1 || ($data['scan_status'] ?? 0) == 2): ?>
                                  <span class="status-value status-inactive">
                                      <img src="../../../packages/skynetaccessibilityscanner/css/Image/not-shared.svg" 
                                          alt="" 
                                          title="Scanning in process">
                                      Scanning<br>
                                      <?php echo $data['total_scan_pages'] ?? 0; ?>/<?php echo $data['total_selected_pages'] ?? 0; ?>
                                  </span>

                              <?php elseif (($data['scan_status'] ?? 0) == 3): ?>
                                  <span class="status-value status-active">
                                      <?php echo $data['total_scan_pages'] ?? 0; ?> Pages<br>
                                      <?php 
                                          if (!empty($data['last_scan'])) {
                                              echo date("F jS Y", strtotime($data['last_scan']));
                                          }
                                      ?>
                                  </span>
                              <?php endif; ?>

                            </div>
                        </section>

                        <hr class="divider">

                        <section class="plan">
                            <div class="page-background"></div>
                            <div class="plan-info">
                                <div class="plans-left">
                                    <span class="plan-type free">
                                        <div class="icon-circle">
                                            <div class="icon-image"></div>
                                            <!-- <img src="../css/Image/round.svg" alt=""
                                                                                    height="20" width="20"> -->
                                        </div>
                                       <?php
                                        $is_expired = isset($data['end_date']) && (date('Y-m-d', strtotime($data['end_date'])) < date('Y-m-d'));
                                        ?>
                                        <span>
                                            <?php if ($is_expired): ?>
                                                <span style="color: #9F0000; font-weight: 700;">Your Plan has Expired</span>
                                            <?php else: ?>
                                                <?php if (!empty($data['is_trial_period']) && $data['is_trial_period'] == 1): ?>
                                                    Free Plan
                                                <?php else: ?>
                                                    <?= htmlspecialchars($data['package_name']) ?> Plan
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </span>

                                        <span class="plan-desc">
                                            <ul>
                                                <?php if (!$is_expired): ?>
                                                    <li>Scan up to <?= htmlspecialchars($data['page_views']) ?> Pages</li>
                                                <?php endif; ?>
                                            </ul>
                                        </span>
                                    <span class="plan-badge">Current Plan</span>
                                </div>
                                <div class="plans-right">
                                <span class="plan-renewal">Renews on:
                                    <strong>
                                        <?php 
                                            if (!empty($data['end_date'])) {
                                                echo date("F j, Y", strtotime($data['end_date']));
                                            }
                                        ?>
                                    </strong>
                                </span>
                                <button 
                                    class="cancel-btn" 
                                    style="<?= $is_expired ? 'background-color: #420083; color: #fff;' : '' ?>" 
                                    data-url="<?= htmlspecialchars($data['dashboard_link']) ?>">
                                    <?= $is_expired ? 'Renew Plan' : 'Cancel Subscription' ?>
                                </button>

                                </div>
                            </div>
                        </section>
                        <!-- Plan Section -->
                        <section class="pricing">
                            <div class="page-background"></div>
                            <div class="billing-toggle">
                                <span class="label active" id="monthly-label">Pay
                                    Monthly</span>
                                <label class="switch">
                                    <input type="checkbox" id="billing-toggle">
                                    <span class="slider"></span>
                                </label>
                                <span class="label" id="annual-label">Pay
                                    Annually</span>

                                <span class="save">Save
                                    20%</span>
                            </div>
                        <!-- Monthly Plans -->
                        <div id="monthlyclass" class="monthlyclass">                                                                                                     
                            <div class="pricing-tiers">
                                <?php if (!empty($data['plans'])): ?>
                                    <?php foreach ($data['plans'] as $index => $plan): ?>
                                        <div class="tier"
                                            data-plan-id="<?php echo $plan['id']; ?>"
                                            data-annual-price="<?php echo $plan['price']; ?>"
                                            data-monthly-price="<?php echo $plan['monthly_price']; ?>">

                                            <div class="pricing-top">
                                                <div class="pricing-header">
                                                    <div class="icon-circle">
                                                        <?php if ($index == 0): ?>     
                                                            <div class="icon-diamond"></div>
                                                        <?php elseif ($index == 1): ?>
                                                             <div class="icon-pentagon"></div>
                                                        <?php elseif ($index == 2): ?>
                                                             <div class="icon-hexagon"></div>
                                                        <?php elseif ($index == 3): ?>
                                                             <div class="icon-hexagon"></div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="pricing-info">
                                                    <h3 class="tier-title"><?php echo $plan['name']; ?></h3>
                                                    <p class="tier-pages"><?php echo $plan['page_views']; ?> Pages</p>
                                                </div>
                                            </div>

                                            <hr class="pricing-divider">

                                            <div class="pricing-body">
                                                <p class="old-price">$<?php echo $plan['strick_monthly_price']; ?></p>
                                                <p class="new-price">$<?php echo $plan['monthly_price']; ?><span class="per-year">/Monthly</span></p>
                                            </div>

                                            <?php                                      // Check if plan expired
                                            $is_expired = $data['end_date'] && (date('Y-m-d', strtotime($data['end_date'])) < date('Y-m-d'));
                                            ?>
                                            <button 
                                                class="upgrade-btn<?= (!$is_expired && $data['final_price'] == $plan['monthly_price']) ? ' cancel-btnn' : '' ?>"
                                                data-action="<?= $is_expired ? 'upgrade' : ($data['final_price'] == $plan['monthly_price'] ? 'cancel' : 'upgrade') ?>"
                                                data-plan-id="<?= $plan['id'] ?>"
                                                data-interval="M">
                                                <?= $is_expired ? 'Upgrade' : ($data['final_price'] == $plan['monthly_price'] ? 'Cancel' : 'Upgrade') ?>
                                            </button>
                                            <div id="plans-container"
                                                data-website-id="<?= $data['website_id'] ?>"
                                                data-package-id="<?= $data['package_id'] ?>"
                                                data-subscr-interval="<?= $data['subscr_interval'] ?>"
                                                data-end-date="<?= $data['end_date'] ?>">
                                            </div>


                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p>No plans available.</p>
                                <?php endif; ?>
                            </div>

                        </div>
                        <!-- Annual Plans -->
                        <div id="annualclass" class="annualclass">                                                                                                   
                           <div class="pricing-tiers">
                                <?php if (!empty($data['plans'])): ?>
                                    <?php foreach ($data['plans'] as $index => $plan): ?>
                                        <div class="tier"
                                            data-plan-id="<?php echo $plan['id']; ?>"
                                            data-annual-price="<?php echo $plan['strick_price']; ?>"
                                            data-monthly-price="<?php echo $plan['strick_monthly_price']; ?>">

                                            <div class="pricing-top">
                                                <div class="pricing-header">
                                                    <div class="icon-circle">
                                                        <?php if ($index == 0): ?>     
                                                            <div class="icon-diamond"></div>
                                                        <?php elseif ($index == 1): ?>
                                                             <div class="icon-pentagon"></div>
                                                        <?php elseif ($index == 2): ?>
                                                             <div class="icon-hexagon"></div>
                                                        <?php elseif ($index == 3): ?>
                                                             <div class="icon-hexagon"></div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="pricing-info">
                                                    <h3 class="tier-title"><?php echo $plan['name']; ?></h3>
                                                    <p class="tier-pages"><?php echo $plan['page_views']; ?> Pages</p>
                                                </div>
                                            </div>

                                            <hr class="pricing-divider">

                                            <div class="pricing-body">
                                        <p class="old-price">$<?php echo $plan['strick_price']; ?></p>
                                                <p class="new-price">$<?php echo $plan['price']; ?><span class="per-year">/Year</span></p>
                                            </div>

                                            <?php
                                            $is_expired = $data['end_date'] && (date('Y-m-d', strtotime($data['end_date'])) < date('Y-m-d'));
                                            ?>
                                           

                                            <button 
                                                class="upgrade-btn<?= (!$is_expired && $data['final_price'] == $plan['price']) ? ' cancel-btnn' : '' ?>"
                                                data-action="<?= $is_expired ? 'upgrade' : ($data['final_price'] == $plan['price'] ? 'cancel' : 'upgrade') ?>"
                                                data-plan-id="<?= $plan['id'] ?>"
                                                data-interval="Y">
                                                <?= $is_expired ? 'Upgrade' : ($data['final_price'] == $plan['price'] ? 'Cancel' : 'Upgrade') ?>
                                            </button>
                                            <div id="plans-container"
                                                data-website-id="<?= $data['website_id'] ?>"
                                                data-package-id="<?= $data['package_id'] ?>"
                                                data-subscr-interval="<?= $data['subscr_interval'] ?>"
                                                data-end-date="<?= $data['end_date'] ?>">
                                            </div>

                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p>No plans available.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                            <p class="pricing-contact">
                                Are you looking for a custom plan or Enterprise
                                plan? Contact us
                                <a href="mailto:hello@skynettechnologies.com">hello@skynettechnologies.com</a>
                            </p>
                        </section>

                        <hr class="divider">

                        <!-- Help Section -->
                        <section class="help">
                            <p class="help-text">
                                <strong>Facing any issues with SkynetAccessibility Scanner?</strong>
                                Report a problem, we will get back to you very soon!
                            </p>
                          <a href="https://www.skynettechnologies.com/report-accessibility-problem" 
                            class="help-btn" 
                            target="_blank" 
                            rel="noopener noreferrer">
                            Report a problem
                          </a>
                        </section>
                    </div>


                </div>
            </div>

        </article>
    </div>
</div>
</section>
</main>
</div>
</div>
</div>
</div>
</div>
</div>
</div>

<!-- End Section 1-->

<!-- Section 2-->
<!-- Violation Report data -->
<div id="section2" style="display:none; max-height: 90vh !important;
    overflow-y: overlay !important;">
<div class="dialog-off-canvas-main-canvas" data-off-canvas-main-canvas>
<div id="page-wrapper">
<div id="page">
<div id="main-wrapper" class="layout-main-wrapper clearfix">
<div id="main" class="container">
<div class="row row-offcanvas row-offcanvas-left clearfix">
    <main class="main-content col" id="content" r ole="main">
        <section class="section">
            <div id="main-content" tabindex="-1"></div>
            <div id="block-skynettechnologies-content"
                class="block block-system block-system-main-block">
                <div class="content">
                    <article data-history-node-id="529"
                        class="node node--type-page node--view-mode-full clearfix">
                        <div class="node__content clearfix ">
                            <div class="scanning-monitoring-app">
                                <div class="accessibility-report">
                                    <div class="report-date">
                                        <label for="report-date">Report Date:</label>
                                        <select id="report-date">
                                           <option selected>
                                                <?php 
                                                    if (!empty($data['last_scan'])) {
                                                        echo date("jS F, Y", strtotime($data['last_scan']));
                                                    }
                                                ?>
                                            </option>
                                        </select>
                                    </div>

                                    <section class="top-section">
                                        <div class="card score-card">
                                            <h3>Accessibility Score</h3>
                                            <div class="accessibility-score">
                                                <div class="score-value">
                                                    <?php echo isset($data['success_percentage']) ? $data['success_percentage'] : 0; ?>%
                                                </div>
                                                                                        <?php 
                                                $percentage = isset($data['success_percentage']) ? $data['success_percentage'] : 0;
                                                $statusClass = '';
                                                $statusText  = '';

                                                if ($percentage >= 0 && $percentage < 50) {
                                                    $statusClass = 'not-compliant';
                                                    $statusText  = 'Not Compliant';
                                                } elseif ($percentage >= 50 && $percentage < 85) {
                                                    $statusClass = 'semi-compliant';
                                                    $statusText  = 'Semi Compliant';
                                                } elseif ($percentage >= 85) {
                                                    $statusClass = 'compliant';
                                                    $statusText  = 'Compliant';
                                                }
                                            ?>

                                            <span class="status-text <?php echo $statusClass; ?>">
                                                <?php echo $statusText; ?>
                                            </span>
                                            </div>
                                            <div class="progress-bar">
                                                <div class="progress-fill" style="width: <?php echo $percentage; ?>%;"></div>
                                            </div>
                                            <p class="note">
                                                Automated Accessibility score has limitations.
                                                We recommend Manual Accessibility Audit.
                                            </p>
                                        </div>
                                                         
                                       <!-- Web Pages Scanned -->
                                        <div class="card pages-card">
                                            <h3>Web Pages Scanned</h3>
                                            <div class="pages-value">
                                                <?php echo isset($data['total_scan_pages']) ? $data['total_scan_pages'] : 0; ?>
                                            </div>

                                            <?php
                                                $totalScanPages = isset($data['total_scan_pages']) ? $data['total_scan_pages'] : 0;
                                                $totalPages     = isset($data['total_pages']) ? $data['total_pages'] : 0;
                                                $progressWidth  = ($totalPages > 0) ? ($totalScanPages / $totalPages * 100) : 0;
                                            ?>

                                            <div class="progress-bar">
                                                <div class="progress-fill" style="width: <?php echo $progressWidth; ?>%;"></div>
                                            </div>

                                            <p class="note">
                                                <?php echo $totalScanPages; ?> pages scanned out of <?php echo $totalPages; ?>
                                            </p>
                                        </div>

                                    </section>

                                    <!-- WCAG Section -->
                                    <section class="wcag-section">
                                        <div class="wcag-header">
                                            <h3>WCAG 2.1/2.2</h3>
                                            <button 
                                                class="view-btn" 
                                                data-url="<?= isset($data['violation_link']) ? htmlspecialchars($data['violation_link']) : '#' ?>">
                                                View all Violations
                                                <svg xmlns="http://www.w3.org/2000/svg" width="6" height="10" viewBox="0 0 6 10" fill="none">
                                                    <path d="M6 5.00002C6 5.17924 5.92797 5.35843 5.78422 5.49507L1.25832 9.79486C0.970413 10.0684 0.503627 10.0684 0.21584 9.79486C-0.0719468 9.52145 -0.0719468 9.07807 0.21584 8.80452L4.22061 5.00002L0.21598 1.19549C-0.0718073 0.921968 -0.0718073 0.478632 0.21598 0.205242C0.503767 -0.0684128 0.970553 -0.0684128 1.25846 0.205242L5.78436 4.50496C5.92814 4.64166 6 4.82086 6 5.00002Z" fill="white" />
                                                </svg>
                                            </button>
                                        </div>

                                        <!-- Checks Grid -->
                                        <div class="checks-grid">
                                            <div class="check-card failed">
                                                <span class="check-value">
                                                    <?php echo isset($data['scan_details']['with_remediation']['total_fail']) ? $data['scan_details']['with_remediation']['total_fail'] : 0; ?>
                                                </span>
                                                <span class="check-label">Failed Checks</span>
                                            </div>

                                            <div class="check-card passed">
                                                <span class="check-value">
                                                    <?php echo isset($data['scan_details']['with_remediation']['total_success']) ? $data['scan_details']['with_remediation']['total_success'] : 0; ?>
                                                </span>
                                                <span class="check-label">Passed Checks</span>
                                            </div>

                                            <div class="check-card na">
                                                <span class="check-value">
                                                    <?php echo isset($data['scan_details']['with_remediation']['severity_counts']['Not_Applicable']) ? $data['scan_details']['with_remediation']['severity_counts']['Not_Applicable'] : 0; ?>
                                                </span>
                                                <span class="check-label">N/A Checks</span>
                                            </div>
                                        </div>


                                        <hr class="divider">

                                        <!-- Violations Grid -->
                                        <div class="violations-grid">
                                            <div class="violation-card">
                                                <span class="violation-title">Level A</span>
                                                <span class="violation-count">
                                                    <span>
                                                        <?php echo isset($data['scan_details']['with_remediation']['criteria_counts']['A']) ? $data['scan_details']['with_remediation']['criteria_counts']['A'] : 0; ?>
                                                    </span> violations
                                                </span>
                                            </div>

                                            <div class="violation-card">
                                                <span class="violation-title">Level AA</span>
                                                <span class="violation-count">
                                                    <span>
                                                        <?php echo isset($data['scan_details']['with_remediation']['criteria_counts']['AA']) ? $data['scan_details']['with_remediation']['criteria_counts']['AA'] : 0; ?>
                                                    </span> violations
                                                </span>
                                            </div>

                                            <div class="violation-card">
                                                <span class="violation-title">Level AAA</span>
                                                <span class="violation-count">
                                                    <span>
                                                        <?php echo isset($data['scan_details']['with_remediation']['criteria_counts']['AAA']) ? $data['scan_details']['with_remediation']['criteria_counts']['AAA'] : 0; ?>
                                                    </span> violations
                                                </span>
                                            </div>
                                        </div>

                                    </section>
                                    <br>
                                     <button class="back-btn" id="go-back-btn">Back</button>
                                </div>
                          
                            </div>
                        </div>

                    </article>
                </div>
            </div>
        </section>
    </main>
</div>
</div>
</div>
</div>
</div>
</div>
</div>

<!-- End Section 2-->

</body>
        <?php
    
        return $this->htmlResponse();
    }

}
