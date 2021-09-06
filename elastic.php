<?php

/**
 * Copyright © 2018 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

define('DS', DIRECTORY_SEPARATOR);
define('BP', __DIR__);

// uncomment this line if issue with Mageplaza UltimateSEO
//define('VENDOR_PATH', BP. DS. 'app/etc/vendor_path.php');

require BP . DS . 'vendor' . DS . 'autoload.php';


header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
header('Pragma: no-cache');

$enableDebugMode = 0;
$result = [];

$params = filter_input_array(INPUT_POST);
$get = filter_input_array(INPUT_GET);
if (is_array($get)) {
    $params = array_merge($params, filter_input_array(INPUT_GET));
}

$storeCode = isset($params['store']) ? $params['store'] : '';

try {
    try {
        $config = new \Wyomind\ElasticsearchCore\Helper\Autocomplete\Config($storeCode);
        $config->getData();
    } catch (\Exception $e) {
        throw $e;
    }

    $configData = $config->getData();

    $client = new \Wyomind\ElasticsearchCore\Model\Client($config);
    $client->init($storeCode);

    $cache = new \Wyomind\ElasticsearchCore\Helper\Cache\FileSystem();
    $synonymsHelper = new \Wyomind\ElasticsearchCore\Helper\Synonyms();

    extract($params);
    $searchTerm = strtolower(trim($searchTerm));

    $requester = new \Wyomind\ElasticsearchCore\Helper\Requester($client, $config, $cache, $synonymsHelper);

    if (!isset($customerGroupId)) {
        $customerGroupId = 0;
    }

    $displayOutofstockProducts = $configData['display_outofstock_products'];
    $requester->setDisplayOutofstockProducts($displayOutofstockProducts);
    $useCategoryUrl = $configData['use_category_url'];
    $requester->setUseCategoryUrl($useCategoryUrl);


    if (!isset($debug)) {
        $debug = false;
    } else {
        $debug = $debug == "true";
    }
    $requester->setDebug($debug);

    // enable this for not searching on simple products not visible
    // individually (or not searchable) and return their parent
    $requester->setSkipConfigurableChildRelation(false);

    // SmartAutocomplete
    if (isset($eaConfig) && isset($ea) && $ea === 'true') {
        $highlightEnabled = $eaConfig['general']['enable_highlight'];
        foreach (array_keys($configData['types']) as $type) {
            if ($type == 'product' && $eaConfig['product']['enable_autocomplete']) {
                $products = $requester->getProducts($storeCode, $customerGroupId, -1, $searchTerm, 0, $eaConfig['product']['autocomplete_limit'], 'score', 'desc', [], false, false, $highlightEnabled, 0);
                if ($products['amount']['total'] == 0) {
                    $count = 0;
                } else {
                    $count = $products['amount']['total'];
                }
                $result['product'] = ['docs' => $products['products'], 'count' => $count, 'time' => $products['time']];
            } elseif ($configData['types'][$type]['enable'] && $eaConfig[$type]['enable_autocomplete']) {
                $result[$type] = $requester->searchByType($storeCode, $type, $searchTerm, $eaConfig[$type]['autocomplete_limit'], $highlightEnabled);
            }
        }
        if ($eaConfig['didyoumean']['enable_autocomplete']) {
            $suggests = $requester->getSuggestions($storeCode, $searchTerm, $eaConfig['didyoumean']['autocomplete_limit']);
            $result['suggest'] = $suggests;
        }
        $result = json_encode($result);
    } elseif (isset($quickorder) && $quickorder === 'true') {
        // Quick Order
        $products = $requester->getProducts($storeCode, $customerGroupId, -1, $searchTerm, 0, $size, 'score', 'desc', $filters, false, false, false);
        $result = $callback . "(" . str_replace("'", "&#39;", json_encode($products['products'])) . ")";
    } else {
        // MultifacetedAutocomplete
        // LayeredNavigation
        if (!isset($filters)) {
            $filters = [];
        }
        if (!isset($loadSelectedFilters)) {
            $loadSelectedFilters = true;
        } else {
            $loadSelectedFilters = $loadSelectedFilters == 'true';
        }
        if (!isset($loadBuckets)) {
            $loadBuckets = true;
        } else {
            $loadBuckets = $loadBuckets == 'true';
        }
        if (!isset($highlightEnabled)) {
            $highlightEnabled = true;
        } else {
            $highlightEnabled = $highlightEnabled == 'true';
        }

        if (!isset($layerUpdateSorting)) {
            $layerUpdateSorting = 1;
        }

        if (isset($aggs)) {
            $requester->setBucketsToBuild(array_unique($aggs));
        }

        if (isset($ids)) {
            $result = $requester->getProducts($storeCode, $customerGroupId, $categoryId, $searchTerm, $from, $size, $order, $direction, $filters, $loadSelectedFilters, $loadBuckets, $highlightEnabled, $layerUpdateSorting, false, $ids);
        } else {
            $result = $requester->getProducts($storeCode, $customerGroupId, $categoryId, $searchTerm, $from, $size, $order, $direction, $filters, $loadSelectedFilters, $loadBuckets, $highlightEnabled, $layerUpdateSorting);
        }

        if (isset($elsln) && $elsln === "true") { // Elasticsearch Layered Navigation

            if (!isset($eaHighlightEnabled)) {
                $eaHighlightEnabled = true;
            } else {
                $eaHighlightEnabled = $eaHighlightEnabled == 'true';
            }

            if (isset($suggest) && $suggest === "true") {
                $result['suggest'] = $requester->getSuggestions($storeCode, $searchTerm, 1);
            }

            foreach (array_keys($configData['types']) as $type) {
                if ($configData['types'][$type]['enable'] && isset($$type) && $$type === "true") {
                    $limit = $type."Limit";
                    if (!isset($$limit)) {
                        $$limit = 5;
                    }
                    $result[$type] = $requester->searchByType($storeCode, $type, $searchTerm, $$limit, $eaHighlightEnabled);
                }
            }
        } else { // Elasticsearch Multifaceted Autocomplete

            if (isset($eaConfig) && $eaConfig['didyoumean']['enable_autocomplete'] && isset($suggest) && $suggest === "true") {
                if ($eaConfig['didyoumean']['enable_autocomplete']) {
                    $result['suggest'] = $requester->getSuggestions($storeCode, $searchTerm, $eaConfig['didyoumean']['autocomplete_limit']);
                }
            }
            foreach (array_keys($configData['types']) as $type) {
                if (isset($eaConfig) && $configData['types'][$type]['enable'] && $eaConfig[$type]['enable_autocomplete'] && isset($$type) && $$type === "true") {
                    $limit = $type."Limit";
                    $result[$type] = $requester->searchByType($storeCode, $type, $searchTerm, $eaConfig[$type]['autocomplete_limit'], $eaHighlightEnabled);
                }
            }
        }
        $result = json_encode($result);
    }
} catch (\Throwable $e) {
    $result = $e->getMessage();
}

if (is_array($result)) {
    $result = json_encode($result);
}
printf("%s", $result);
