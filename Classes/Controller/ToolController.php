<?php
namespace Skynettechnologies\Skynetaccessibilityscanner\Controller;

use Skynettechnologies\Skynetaccessibilityscanner\Property\TypeConverter\UploadedFileReferenceConverter;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfiguration;
use TYPO3\CMS\Tstemplate\Controller\TypoScriptTemplateModuleController;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\ConnectionPool;
use Skynettechnologies\Skynetaccessibilityscanner\Controller\ConstantClass;
use TYPO3\CMS\Core\Page\PageRenderer;


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
       
    }

    /**
     * Initialize Action
     *
     * @return void
     */
    public function initializeAction(): void
    {
        
        // Ensure that $this->constantObj is initialized before calling any methods on it
        if ($this->constantObj === null) {
            // Instantiate the ConstantClass object
            $this->constantObj = GeneralUtility::makeInstance(\Skynettechnologies\Skynetaccessibilityscanner\Controller\ConstantClass::class);
        }

        // Now call the init method on the initialized object
        $this->constantObj->init($this->pObj);

        // Get the constants from the main method
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
      
        $this->view->assign('action', 'chatSettings');
        $this->view->assign('constant', $this->constants);
        
        $host = GeneralUtility::locationHeaderUrl( '/' );
        $domain = parse_url($host, PHP_URL_HOST);

        // Query 'be_users' table
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('be_users');
        $result = $queryBuilder
            ->select('*')
            ->from('be_users')
            ->executeQuery()
            ->fetchAllAssociative();
    
        $user_name = $result[0]['username'] ?? '';
        $user_email = $result[0]['email'] ?? '';

        // echo $user_name;
        // echo $user_email;
       
        // scanning & monitoring code start
    // Add user domain
    $websitename =  $domain;
    $arrDetails = [
        'website'        => base64_encode($websitename), // Encode domain
        'platform'       => 'Typo3 CMS',
        'is_trial_period'=> 1,
        'name'           => $user_name,
        'email'          => $user_email,
        'comapany_name'  => $websitename,
        'package_type'   => '25-pages'
    ];
    // register user domain on scanning & monitoring dashboard
    $ch = curl_init('https://skynetaccessibilityscan.com/api/register-domain-platform');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $arrDetails,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
     ]);
    

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

   
    $domain_name =   $domain;
    /**
     * Common function to call cURL POST API
     */
    function callApiPost($url, $postData, $isJson = false) {
        $curl = curl_init();
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ];

        $options[CURLOPT_POSTFIELDS] = $isJson ? json_encode($postData) : $postData;
        if ($isJson) $options[CURLOPT_HTTPHEADER] = ['Content-Type: application/json'];

        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response, true);
    }

    // ----------------- Get Scan Detail -----------------
$response = callApiPost(
    'https://skynetaccessibilityscan.com/api/get-scan-detail',
    ['website' => base64_encode($domain_name)]
);

// Get main scan row
$row = $response['data'][0] ?? [];


// ✅ Get user data separately
$userData = $response['userData'] ?? [];
    $data = [
        'user_id' => $userData['id'] ?? 0,
        'user_email' => $userData['email'] ?? '',
        'domain' => $row['domain'] ?? '',
        'fav_icon' => $row['fav_icon'] ?? '',
        'url_scan_status' => $row['url_scan_status'] ?? 0,
        'scan_status' => $row['scan_status'] ?? 0,
        'total_selected_pages' => $row['total_selected_pages'] ?? 0,
        'total_last_scan_pages' => $row['total_last_scan_pages'] ?? 0,
        'total_pages' => $row['total_pages'] ?? 0,
        'last_url_scan' => $row['last_url_scan'] ?? 0,
        'total_scan_pages' => $row['total_scan_pages'] ?? 0,
        'last_scan' => $row['last_scan'] ?? null,
        'next_scan_date' => $row['next_scan_date'] ?? null,
        'success_percentage' => $row['success_percentage'] ?? '0',
        'scan_violation_total' => $row['scan_violation_total'] ?? '0',
        'total_violations' => $row['total_violations'] ?? 0,
        'package_name' => $row['name'] ?? '',
        'package_id' => $row['package_id'] ?? '',
        'page_views' => $row['page_views'] ?? '',
        'package_price' => $row['package_price'] ?? '',
        'subscr_interval' => $row['subscr_interval'] ?? '',
        'end_date' => $row['end_date'] ?? '',
        'cancel_date' => $row['cancel_date'] ?? '',
        'website_id' => $row['website_id'] ?? '',
        'paypal_subscr_id' => $row['paypal_subscr_id'] ?? '',
        'is_trial_period' => $row['is_trial_period'] ?? '',
        'dashboard_link' => callApiPost('https://skynetaccessibilityscan.com/api/get-scan-detail', ['website'=>base64_encode($domain_name)])['dashboard_link'] ?? '',
        'total_fail_sum' => $row['total_fail_sum'] ?? '',
        'is_expired' => $row['is_expired'] ?? ''
    ];
// echo $data['end_date'] ;
    // ----------------- Get Scan Count -----------------
    $result1 = callApiPost('https://skynetaccessibilityscan.com/api/get-scan-count', [
        'website' => base64_encode($domain_name)
    ]);

    $widgetPurchased = $result1['widget_purchased'] ?? false;
    $data['scan_details'] = [
        'with_remediation' => $widgetPurchased ? ($result1['scan_details']['with_remediation'] ?? []) : ($result1['scan_details']['without_remediation'] ?? [])
    ];

    // ----------------- Fetch Packages -----------------
    $decoded = callApiPost('https://skynetaccessibilityscan.com/api/packages-list', [
        'website' => base64_encode($domain_name)
    ], true);

    $activePackageId = $data['package_id'] ?? '';
    $activeInterval  = $data['subscr_interval'] ?? '';
    $websiteId       = (string)($data['website_id'] ?? '');
    $today           = new \DateTime('now', new \DateTimeZone('UTC'));

    $packageData = $decoded['current_active_package'][$websiteId] ?? $decoded['expired_package_detail'][$websiteId] ?? [];
    $data['final_price'] = $packageData['final_price'] ?? 0;
    $activePackageId = $packageData['package_id'] ?? $activePackageId;
    $activeInterval = $packageData['subscr_interval'] ?? $activeInterval;

    // Generate violation link once
    $violationLinkData = callApiPost('https://skynetaccessibilityscan.com/api/generate-plan-action-link', [
        'website_id' => $websiteId,
        'current_package_id' => $activePackageId,
        'action' => 'violation'
    ]);
    $data['violation_link'] = $violationLinkData['action_link'] ?? '#';

    $data['plans'] = [];
    foreach ($decoded['Data'] as $plan) {
        if (!isset($plan['platforms']) || strtolower($plan['platforms']) !== 'scanner') continue;

        $planId = $plan['id'] ?? null;
        if (!$planId) continue;

        $action = 'upgrade';
        if ($planId == $activePackageId) {
            $plan['interval'] = $activeInterval;
            $endDateStr = $data['end_date'] ?? '';
            if ($endDateStr) {
                $endDate = new \DateTime($endDateStr, new \DateTimeZone('UTC'));
                $action = ($today <= $endDate) ? 'cancel' : 'upgrade';
            } else {
                $action = 'cancel';
            }
        }

        $plan['action'] = $action;
        $data['plans'][] = $plan;
    }

    $data['activePackageId'] = $activePackageId;
    $data['websiteId'] = $websiteId;

    
 $data['userid'] = $data['user_id'];

    $email = $data['user_email'] ?? '';



    $isFallback = empty($email) || strpos($email, 'no-reply@') === 0;

    // Decide display
    $emailFormDisplay = $isFallback ? 'block' : 'none';
    ?>
    <meta name="description" content="" />
    <link rel="mask-icon" href="" />
    <meta name="Generator" content="Drupal 9 (https://www.drupal.org)" />
    <meta name="MobileOptimized" content="width" />
    <meta name="HandheldFriendly" content="true" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <link rel="icon" href="https://www.skynettechnologies.com/sites/default/files/favicon_0.webp" type="image/png" />
    <link href="https://sanity.skynettechnologies.us/assets/css/scanning-and-monitoring-app.css" rel="stylesheet">
    <style>
        p {
            line-height: 30px !important;
        }
        @media (min-width: 1200px) {
        .container {
            max-width: 1280px !important;
        }
    }
       
        .demo-card{
      width:100%;max-width:680px;background:#fff;border:1.5px solid #e8daff;
      border-radius:14px;overflow:hidden;box-shadow:0 4px 24px rgba(66,0,131,.10)
    }
    .skynet-email-toggle-bar{
      display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;
      padding:14px 20px;background:#f8f4ff;border-bottom:1.5px solid transparent
    }
    #skynetEmailToggleWrapper.skynet-form-open .skynet-email-toggle-bar{border-bottom-color:#e0cfff}
    .skynet-email-toggle-label{display:flex;align-items:center;font-size:14px;font-weight:500;color:#420083}
    .skynet-email-toggle-btn,.skynet-email-save-btn{
      display:inline-flex;align-items:center;border:none;border-radius:6px;cursor:pointer;
      transition:.2s;background:#420083;color:#fff
    }
    .skynet-email-toggle-btn{padding:8px 18px;font-size:14px;font-weight:600}
    .skynet-email-toggle-btn:hover,.skynet-email-save-btn:hover{background:#5a00b3}
    .skynet-email-toggle-btn:active{transform:scale(.97)}
    #skynetEmailToggleWrapper.skynet-form-open #skynetEmailToggleArrow{transform:rotate(180deg)}
    .skynet-email-form-panel{background:#fff;overflow:hidden}
    .skynet-email-form-inner{padding:24px 28px 20px}
    .skynet-email-form-title{margin:0 0 18px;font-size:16px;font-weight:700;color:#420083}
    .skynet-email-form-error,.skynet-email-form-success{
      padding:10px 14px;border-radius:6px;font-size:13px;margin-bottom:14px;display:none
    }
    .skynet-email-form-error{background:#fff5f5;border:1px solid #fc8181;color:#c53030}
    .skynet-email-form-success{background:#f0fff4;border:1px solid #68d391;color:#276749}
    .skynet-email-form-row{margin-bottom:16px;display:flex;flex-direction:column;gap:6px}
    .skynet-email-form-label{font-size:13px;font-weight:600;color:#333}
    .skynet-email-form-input{
      width:100%;max-width:440px;padding:10px 14px;border:1.5px solid #ccc;border-radius:6px;
      font-size:14px;color:#222;background:#fafafa;outline:none;transition:.2s
    }
    .skynet-email-form-input:focus{border-color:#420083;box-shadow:0 0 0 3px rgba(66,0,131,.12);background:#fff}
    .skynet-input-error{border-color:#e53e3e!important;box-shadow:0 0 0 3px rgba(229,62,62,.10)!important}
    .skynet-email-form-actions{display:flex;align-items:center;gap:12px;margin-top:8px}
    .skynet-email-save-btn{padding:10px 26px;font-size:14px;font-weight:700}
    .skynet-email-save-btn:disabled{opacity:.6;cursor:not-allowed}
    .skynet-email-cancel-btn{
      padding:10px 18px;background:transparent;color:#666;border:1.5px solid #ccc;border-radius:6px;
      font-size:14px;font-weight:500;cursor:pointer;transition:.2s
    }
    .skynet-email-cancel-btn:hover{border-color:#420083;color:#420083}
    @keyframes skynet-spin{to{transform:rotate(360deg)}}
    @media (max-width:600px){
      .skynet-email-toggle-bar{flex-direction:column;align-items:flex-start}
      .skynet-email-form-inner{padding:16px}
      .skynet-email-form-actions{flex-direction:column}
      .skynet-email-save-btn,.skynet-email-cancel-btn{width:100%;justify-content:center}
    }
    
    </style>
    </head>

    <body class="layout-no-sidebars has-featured-top page-node-1860 path-node node--type-page scrolled scrolldown">
     <div id="plugin-content" style="width:50%; margin:25 auto;">
        <!-- - User register -->
        <div class="userform" >
            <!-- Email Registration Toggle Wrapper
            Shown when user's email is absent/fallback (checked on page load).
            JS hides this entire block once a valid email is saved. -->
         <div id="skynetEmailToggleWrapper" style="display:<?php echo $emailFormDisplay; ?>;">
                <!-- Toggle bar: label + open/close button -->
                <div class="skynet-email-toggle-bar">
                    <span class="skynet-email-toggle-label">
                        <!-- Envelope icon -->
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="18"
                            height="18"
                            fill="none"
                            viewBox="0 0 24 24"
                            style="vertical-align:middle;margin-right:6px;"
                        >
                            <path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9
                2-2V6c0-1.1-.9-2-2-2zm0 4-8 5-8-5V6l8 5 8-5v2z" fill="#420083"/>
                        </svg>
                        Please Enter Your Email for further Scanning Reports.
                    </span>
                    <!-- Toggle button -->
                    <button
                        id="skynetEmailToggleBtn"
                        class="skynet-email-toggle-btn"
                        type="button"
                    >
                        <span id="skynetEmailToggleBtnText">Add Email</span>
                        <!-- Arrow — rotates 180° when form is open (CSS class controls it) -->
                        <svg
                            id="skynetEmailToggleArrow"
                            xmlns="http://www.w3.org/2000/svg"
                            width="12"
                            height="12"
                            viewBox="0 0 24 24"
                            fill="none"
                            style="margin-left:6px;transition:transform 0.25s;"
                        >
                            <path d="M7 10l5 5 5-5H7z" fill="currentColor"/>
                        </svg>
                    </button>
                </div>
                <!-- Collapsible registration form (hidden until toggle is clicked) -->
                <div id="skynetEmailFormPanel" class="skynet-email-form-panel" style="display:none;">
                    <div class="skynet-email-form-inner" style="
                            background-image: linear-gradient(rgb(255 255 255 / 100%), rgba(0, 0, 0, 0.1)), url(https://sanity.skynettechnologies.us/assets/images/sitemap-bg.png);

                            /* background-image: url(https://sanity.skynettechnologies.us/assets/images/sitemap-bg.png); */
                            background-repeat: no-repeat; 
                            background-position: center;

                            background-size: cover;
                        
                        ">
                        <h3 class="skynet-email-form-title">Register Your Details</h3>
                        <!-- Inline error / success banners -->
                        <div id="skynetEmailFormError" class="skynet-email-form-error" style="display:none;"></div>
                        <div id="skynetEmailFormSuccess" class="skynet-email-form-success" style="display:none;"></div>
                        <!-- Full Name field -->
                        <div class="skynet-email-form-row">
                            <label class="skynet-email-form-label" for="skynetRegName">
                                Full Name
                                <span style="color:#e53e3e;">*</span>
                            </label>
                            <input
                                id="skynetRegName"
                                class="skynet-email-form-input"
                                type="text"
                                placeholder="Enter your full name"
                                autocomplete="name"
                            >
                        </div>
                        <!-- Email Address field -->
                        <div class="skynet-email-form-row">
                            <label class="skynet-email-form-label" for="skynetRegEmail">
                                Email Address
                                <span style="color:#e53e3e;">*</span>
                            </label>
                            <input
                                id="skynetRegEmail"
                                class="skynet-email-form-input"
                                type="email"
                                placeholder="Enter your email address"
                                autocomplete="email"
                            >
                        </div>
                        <!-- Action buttons -->
                        <div class="skynet-email-form-actions">
                            <!-- Save button — shows spinner while API call is in-flight -->
                            <button
                                id="skynetRegSaveBtn"
                                class="skynet-email-save-btn"
                                type="button"
                            >
                                <span id="skynetRegSaveBtnText">Save</span>
                                <!-- Spinner (hidden; shown during async save) -->
                                <svg
                                    id="skynetRegSaveSpinner"
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="16"
                                    height="16"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    style="display:none;margin-left:8px;
                    animation:skynet-spin 0.8s linear infinite;"
                                >
                                    <circle
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="3"
                                        stroke-dasharray="31.4"
                                        stroke-dashoffset="10"
                                    />
                                </svg>
                            </button>
                            <!-- Cancel closes the form without saving -->
                            <button class="skynet-email-cancel-btn" type="button">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ═══ END OF COMPONENT ═══ -->
        </div>
    </div>
    <!-- Section 1-->

    <div id="section1">

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
                            <section class="status" style="background-image: url('https://sanity.skynettechnologies.us/assets/images/sitemap-bg.png');background-repeat: no-repeat;background-position: center;background-size: cover;">
                                <div class="page-background"></div>
                            
                                <div class="status-card">
                                    <span class="status-title">Scan Score</span>
                                    <?php 
                                        if (!empty($data['is_expired']) && $data['is_expired'] == 1): ?>
                                            <span class="status-value status-inactive">N/A</span>
                                        <?php else: ?>
                                            <?php if (($data['scan_violation_total'] ?? 0) == 0): ?>
                                                <span class="status-value status-inactive">N/A</span>
                                            <?php else: ?>
                                                <span id="showDetailsBtn" class="status-value status-progress" style="cursor:pointer;">
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
                                        <img src="https://sanity.skynettechnologies.us/assets/images/not-shared.svg" 
                                            alt="" 
                                            title="Not Started">
                                        Not Started
                                    </span>

                                    <?php elseif (($data['scan_status'] ?? 0) == 0): ?>
                                    <span class="status-value status-inactive">
                                        <img src="https://sanity.skynettechnologies.us/assets/images/not-shared.svg" 
                                            alt="" 
                                            title="Not Started">
                                        Not Started
                                    </span>

                                    <?php elseif (($data['scan_status'] ?? 0) == 1 || ($data['scan_status'] ?? 0) == 2): ?>
                                    <span class="status-value status-inactive">
                                        <img src="https://sanity.skynettechnologies.us/assets/images/not-shared.svg" 
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
                                    <?php elseif (($data['scan_status'] ?? 0) == 4): ?>
                                        <span class="status-value status-inactive">
                                            N/A
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </section>

                            <hr class="divider">

                        <section class="plan" style="background-image: url('https://sanity.skynettechnologies.us/assets/images/sitemap-bg.png');background-repeat: no-repeat;background-position: center;background-size: cover;">
                                <div class="page-background"></div>
                                <div class="plan-info">
                                    <div class="plans-left" style="margin-bottom: 5px;">
        
                                        <span class="plan-type free">
                                            <div class="icon-circle">
                                                <img src="https://sanity.skynettechnologies.us/assets/images/round.svg" alt="" height="20" width="20">
                                            </div>
                                        <?php
                                            $is_expired = isset($data['end_date']) && (date('Y-m-d', strtotime($data['end_date'])) < date('Y-m-d'));
                                            ?>
                                            <span>
                                                <?php if ($data['is_expired']): ?>
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
                                                    <?php if (!$data['is_expired']): ?>
                                                        <li>Scan up to <?= htmlspecialchars($data['page_views']) ?> Pages</li>
                                                </ul>
                                            </span>
                                            <?php 
                                            $today = date('Y-m-d');
                                            $cancel = isset($data['cancel_date']) ? substr($data['cancel_date'], 0, 10) : '';
                                            $isExpired = !empty($data['is_expired']);

                                            // Show “Cancelled Plan” only if cancel date <= today OR expired
                                            $isCancelled = ($cancel && $cancel <= $today) || $isExpired;
                                            ?>

                                            <span class="plan-badge" 
                                                    style="color: <?= $isCancelled ? '#940000;' : 'green' ?>;
                                                        background: <?= $isCancelled ? '#ffd1d1' : '#D1FFD3' ?>;">
                                                <?= $isCancelled ? 'Cancelled Plan' : 'Current Plan' ?>
                                            </span>
                                    <?php endif; ?>
                                    </div>
                                    <div class="plans-right" style="margin-left:3rem;">
                                    <?php if (!$data['is_expired']): ?>
                                        <span class="plan-renewal">
                                            <?php 
                                                $today = date('Y-m-d');
                                                $cancelDate = !empty($data['cancel_date']) ? substr($data['cancel_date'], 0, 10) : '';
                                                $endDate = !empty($data['end_date']) ? substr($data['end_date'], 0, 10) : '';

                                                $isCancelled = ($cancelDate && $cancelDate <= $today);
                                                $isExpired = !empty($data['is_expired']) && $data['is_expired']; // assuming this is boolean true/false

                                            
                                            if (!empty($endDate)) {
                                                $formattedDate = date("F j, Y", strtotime($endDate));

                                                // Trial period → always expires
                                                if (!empty($data['is_trial_period']) && $data['is_trial_period'] == 1) {
                                                    echo '<span style="color:#9F0000;">Expires on:</span> <strong>' . $formattedDate . '</strong>';
                                                } 
                                                // Non-trial
                                                else {
                                                    if ($isExpired || $isCancelled) {
                                                        echo '<span style="color:#9F0000;">Expires on:</span> <strong>' . $formattedDate . '</strong>';
                                                    } else {
                                                        echo 'Renews on: <strong>' . $formattedDate . '</strong>';
                                                    }
                                                }
                                            }
                                            ?>
                                        </span>
                                        <?php else: ?>
                                            <span class="plan-renewal">
                                                <?php 
                                                    if (!empty($data['end_date'])) {
                                                        $formattedDate = date("F j, Y", strtotime($data['end_date']));
                                                        echo 'Expired on: <strong>' . $formattedDate . '</strong>';
                                                    }
                                                ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php
                                            $today = date('Y-m-d');
                                            $cancel = isset($data['cancel_date']) ? substr($data['cancel_date'], 0, 10) : '';
                                            $isExpired = !empty($data['is_expired']);
                                            $isTrial = !empty($data['is_trial_period']) && $data['is_trial_period'] == 1;

                                            // Button logic
                                            if($isExpired)
                                            {
                                                $buttonText = 'Renew Plan';
                                                $buttonStyle = 'background-color:#420083;color:#fff;';
                                            } 
                                            elseif ($isTrial) {
                                                $buttonText = 'Activate Now';
                                                $buttonStyle = 'background-color:#420083;color:#fff;';
                                            }
                                            elseif (!empty($cancel) && $cancel <= $today) {
                                                $buttonText = 'Renew Plan';
                                                $buttonStyle = 'background-color:#420083;color:#fff;';
                                            } else {
                                                $buttonText = 'Cancel Subscription';
                                                $buttonStyle = '';
                                            }
                                        ?>
                                        <button 
                                        class="cancel-btn" 
                                        style="<?= $buttonStyle ?>" 
                                        data-url="<?= htmlspecialchars($data['dashboard_link']) ?>">
                                        
                                        <?= $buttonText ?>
                                        </button>
                                    </div>
                                </div>
                        </section>
                            <!-- Plan Section -->
                            <section class="pricing" style="background-image: url('https://sanity.skynettechnologies.us/assets/images/sitemap-bg.png');background-repeat: no-repeat;background-position: center;background-size: cover;">
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
                                                                <?php 
                                                                    $icons = ['diamond.svg', 'pentagon.svg', 'hexagon.svg', 'hexagon.svg'];
                                                                    $icon = $icons[$index] ?? 'default.svg'; // fallback if $index out of range
                                                                ?>
                                                                <img src="https://sanity.skynettechnologies.us/assets/images/<?= $icon ?>" alt="" height="20" width="20">
                                                            </div>
                                                        </div>
                                                        <div class="pricing-info">
                                                            <h3 class="tier-title"><?php echo $plan['name']; ?></h3>
                                                            <p class="tier-pages"><?php echo $plan['page_views']; ?> Pages</p>
                                                        </div>
                                                    </div>

                                                    <hr class="pricing-divider" style="width:auto;">

                                                    <div class="pricing-body">
                                                        <p class="old-price">$<?php echo $plan['strick_monthly_price']; ?></p>
                                                        <p class="new-price">$<?php echo $plan['monthly_price']; ?><span class="per-year">/Monthly</span></p>
                                                    </div>

                                                    <?php                                      // Check if plan expired
                                                    $is_expired = $data['end_date'] && (date('Y-m-d', strtotime($data['end_date'])) < date('Y-m-d'));
                                                    ?>
                                                
                                                <button 
                                                    class="upgrade-btn<?= (!$is_expired && $data['package_id'] == $plan['id'] && $plan['interval'] == 'M') ? ' cancel-btnn' : '' ?>" 
                                                    data-plan="<?= $plan['id'] ?>"
                                                    data-action="<?= $is_expired ? 'upgrade' : (($data['package_id'] == $plan['id'] && $plan['interval'] == 'M') ? 'cancel' : 'upgrade') ?>"
                                                    data-interval="M">
                                                    <?= $is_expired ? 'Upgrade' : (($data['package_id'] == $plan['id'] && $plan['interval'] == 'M') ? 'Cancel' : 'Upgrade') ?>
                                                    </button>

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
                                                        <?php 
                                                        $icons = ['diamond.svg', 'pentagon.svg', 'hexagon.svg', 'hexagon.svg'];
                                                        $icon = $icons[$index] ?? 'default.svg'; // fallback if $index out of range
                                                        ?>
                                                        <img src="https://sanity.skynettechnologies.us/assets/images/<?= $icon ?>" alt="" height="20" width="20">
                                                </div>
                                                    </div>
                                                        <div class="pricing-info">
                                                            <h3 class="tier-title"><?php echo $plan['name']; ?></h3>
                                                            <p class="tier-pages"><?php echo $plan['page_views']; ?> Pages</p>
                                                        </div>
                                                    </div>

                                                    <hr class="pricing-divider" style="width:auto;">

                                                    <div class="pricing-body">
                                                        <p class="old-price">$<?php echo $plan['strick_price']; ?></p>
                                                        <p class="new-price">$<?php echo $plan['price']; ?><span class="per-year">/Year</span></p>
                                                    </div>

                                                    <?php
                                                    $is_expired = $data['end_date'] && (date('Y-m-d', strtotime($data['end_date'])) < date('Y-m-d'));
                                                
                                                    ?>
                                                <button 
                                                        class="upgrade-btn<?= (!$is_expired && $data['package_id'] == $plan['id'] && $plan['interval'] == 'Y') ? ' cancel-btnn' : '' ?>" 
                                                        data-plan="<?= $plan['id'] ?>"
                                                        data-action="<?= $is_expired ? 'upgrade' : (($data['package_id'] == $plan['id'] && $plan['interval'] == 'Y') ? 'cancel' : 'upgrade') ?>"
                                                        data-interval="Y">
                                                        <?= $is_expired ? 'Upgrade' : (($data['package_id'] == $plan['id'] && $plan['interval'] == 'Y') ? 'Cancel' : 'Upgrade') ?>
                                                    </button>

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
                                    <a href="mailto:hello@skynettechnologies.com" target="_blank" rel="noopener noreferrer">hello@skynettechnologies.com</a>
                                </p>
                            </section>

                            <hr class="divider">

                            <!-- Help Section -->
                            <section class="help">
                                <p class="help-text">
                                    <strong>Facing any issues with SkynetAccessibility Scanner?</strong>
                                    Report a problem, we will get back to you very soon!
                                </p>
                                <a href="https://www.skynettechnologies.com/report-accessibility-problem"target="_blank" class="help-btn">Report a problem</a>
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
    <div id="section2" style="display:none;">
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
                                                <button class="view-btn" data-url="<?php echo isset($data['violation_link']) ? htmlspecialchars($data['violation_link']) : '#'; ?>">View all Violations <svg
                                                xmlns="http://www.w3.org/2000/svg" width="6"
                                                height="10" viewBox="0 0 6 10" fill="none">
                                                <path
                                                d="M6 5.00002C6 5.17924 5.92797 5.35843 5.78422 5.49507L1.25832 9.79486C0.970413 10.0684 0.503627 10.0684 0.21584 9.79486C-0.0719468 9.52145 -0.0719468 9.07807 0.21584 8.80452L4.22061 5.00002L0.21598 1.19549C-0.0718073 0.921968 -0.0718073 0.478632 0.21598 0.205242C0.503767 -0.0684128 0.970553 -0.0684128 1.25846 0.205242L5.78436 4.50496C5.92814 4.64166 6 4.82086 6 5.00002Z"
                                                fill="white" />
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


                                            <hr class="divider" style="width:auto;">

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
                                        <button class="back-btn">Back</button>
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

            // Assign variables to the view
            $this->view->assignMultiple([
                'action' => 'chatSettings',
                'constant' => $this->constants,
                'domain' => $domain,
                'data' => $data,
            ]);
        
            return $this->htmlResponse();
        }
}
