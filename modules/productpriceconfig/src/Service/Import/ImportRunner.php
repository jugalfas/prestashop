<?php

namespace ProductPriceConfig\Service\Import;

use Db;
use Exception;

class ImportRunner
{
    /** @var \Module */
    private $module;

    public function __construct($module)
    {
        $this->module = $module;
    }

    /**
     * Run the import using parsed data and admin selections.
     * Returns an array with imported/skipped/errors and a log id.
     *
     * @param array $parsedData
     * @param array $selections
     * @return array
     * @throws Exception
     */
    public function runImport(array $parsedData, array $selections)
    {
        $db = Db::getInstance();
        $results = [
            'imported' => [],
            'skipped' => [],
            'warnings' => [],
            'errors' => [],
        ];

        // Ensure log table exists
        $this->ensureLogTable();

        // Start transaction
        if (!$db->execute('START TRANSACTION')) {
            throw new Exception('Could not start DB transaction.');
        }

        try {
            // 1) Variables and options
            if (!empty($selections['variables']) && is_array($selections['variables'])) {
                foreach ($selections['variables'] as $varCode => $selectedOptions) {
                    if (!isset($parsedData['variables'][$varCode])) {
                        $results['warnings'][] = "Variable not present in file: $varCode";
                        continue;
                    }

                    $var = $parsedData['variables'][$varCode];
                    $this->importVariable($var, $selectedOptions, $selections, $results);
                }
            }

            // 2) Tooltips
            if (!empty($selections['tooltips']) && is_array($selections['tooltips'])) {
                foreach ($selections['tooltips'] as $tooltipCode) {
                    if (!isset($parsedData['tooltips'][$tooltipCode])) {
                        $results['warnings'][] = "Tooltip not present: $tooltipCode";
                        continue;
                    }
                    $this->importTooltip($tooltipCode, $parsedData['tooltips'][$tooltipCode], $selections, $results);
                }
            }

            // 3) Alerts
            if (!empty($selections['alerts']) && is_array($selections['alerts'])) {
                // selections['alerts'] expected to be array of integer indices or full objects
                foreach ($selections['alerts'] as $idx => $sel) {
                    // allow selected by index if payload provided as array
                    $alert = null;
                    if (is_int($idx) && isset($parsedData['alerts'][$idx])) {
                        $alert = $parsedData['alerts'][$idx];
                    } elseif (is_array($sel) && isset($sel['product_ref'])) {
                        $alert = $sel;
                    }
                    if (!$alert) {
                        continue;
                    }
                    $this->importAlert($alert, $selections, $results);
                }
            }

            // 4) Product configurations
            if (!empty($selections['products']) && is_array($selections['products'])) {
                foreach ($selections['products'] as $productRef => $elements) {
                    if (!isset($parsedData['products'][$productRef])) {
                        $results['warnings'][] = "Product config not present in file: $productRef";
                        continue;
                    }
                    $this->importProductConfig($productRef, $parsedData['products'][$productRef], $elements, $selections, $results);
                }
            }

            if ($selections['dry_run']) {
                // Rollback changes on dry-run
                $db->execute('ROLLBACK');
                $logId = $this->writeLog($parsedData, $selections, $results, 'dry_run');
                $results['log_id'] = $logId;
                return $results;
            }

            // Commit
            if (!$db->execute('COMMIT')) {
                throw new Exception('Could not commit DB transaction.');
            }

            $logId = $this->writeLog($parsedData, $selections, $results, 'success');
            $results['log_id'] = $logId;
            return $results;
        } catch (Exception $e) {
            $db->execute('ROLLBACK');
            $results['errors'][] = $e->getMessage();
            $logId = $this->writeLog($parsedData, $selections, $results, 'failed');
            $results['log_id'] = $logId;
            throw $e;
        }
    }

    private function ensureLogTable()
    {
        $db = Db::getInstance();
        $sql = 'CREATE TABLE IF NOT EXISTS `'.pSQL(_DB_PREFIX_).'productpriceconfig_import_log` (
            `id_import` INT(11) NOT NULL AUTO_INCREMENT,
            `date_add` DATETIME NOT NULL,
            `status` VARCHAR(32) NOT NULL,
            `selections` TEXT NULL,
            `results` TEXT NULL,
            PRIMARY KEY (`id_import`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';
        return $db->execute($sql);
    }

    private function writeLog(array $parsedData, array $selections, array $results, $status)
    {
        $db = Db::getInstance();
        $now = date('Y-m-d H:i:s');
        $selectionsStr = pSQL(json_encode($selections));
        $resultsStr = pSQL(json_encode($results));
        $sql = 'INSERT INTO `'.pSQL(_DB_PREFIX_).'productpriceconfig_import_log` (`date_add`, `status`, `selections`, `results`) VALUES ('
            ."'".$now."', '".pSQL($status)."', '".$selectionsStr."', '".$resultsStr."')";
        $db->execute($sql);
        return $db->Insert_ID();
    }

    private function importVariable($var, $selectedOptions, $selections, &$results)
    {
        $db = Db::getInstance();
        $code = $var['code'];
        $label = isset($var['label']) ? $var['label'] : $code;

        // base and i18n tables
        $tblVar = pSQL(_DB_PREFIX_).'variable';
        $tblVarLang = pSQL(_DB_PREFIX_).'variable_lang';
        $tblOpt = pSQL(_DB_PREFIX_).'option';
        $tblOptLang = pSQL(_DB_PREFIX_).'option_lang';
        $languages = \Language::getLanguages(true);
        $defaultIdLang = (int) \Configuration::get('PS_LANG_DEFAULT');

        // find existing variable by code (stored in variable.name)
        $idVar = $db->getValue("SELECT id_variable FROM $tblVar WHERE name='" . pSQL($code) . "'");
        $overwrite = !empty($selections['overwrite']);

        if ($idVar) {
            // update/insert variable_lang labels
            if ($overwrite) {
                foreach ($languages as $lang) {
                    $idLang = (int) $lang['id_lang'];
                    $exists = $db->getValue("SELECT 1 FROM $tblVarLang WHERE id_variable=" . (int) $idVar . " AND id_lang=" . $idLang);
                    if ($exists) {
                        $db->execute("UPDATE $tblVarLang SET label='" . pSQL($label) . "' WHERE id_variable=" . (int) $idVar . " AND id_lang=" . $idLang);
                    } else {
                        $db->execute("INSERT INTO $tblVarLang (id_variable, id_lang, label) VALUES (" . (int) $idVar . ", " . $idLang . ", '" . pSQL($label) . "')");
                    }
                }
                $results['imported'][] = "Variable updated: $code";
            } else {
                $results['skipped'][] = "Variable exists and skipped: $code";
            }
        } else {
            // Insert minimal base variable row
            $now = date('Y-m-d H:i:s');
            $db->execute(
                "INSERT INTO $tblVar (name, type, fixed_price, minimum, maximum, required, active, position, date_add, date_upd) VALUES (" .
                "'" . pSQL($code) . "', " .
                "'" . pSQL('2') . "', " . // default to select type
                "0, 0, 0, 0, 1, 0, " .
                "'" . pSQL($now) . "', '" . pSQL($now) . "')"
            );
            $idVar = (int) $db->Insert_ID();
            // Insert labels for all languages (use same label)
            foreach ($languages as $lang) {
                $idLang = (int) $lang['id_lang'];
                $db->execute("INSERT INTO $tblVarLang (id_variable, id_lang, label) VALUES (" . $idVar . ", " . $idLang . ", '" . pSQL($label) . "')");
            }
            $results['imported'][] = "Variable created: $code";
        }

        // Options
        if (!is_array($selectedOptions)) {
            $selectedOptions = [];
        }
        foreach ($selectedOptions as $optValue) {
            $optValue = (string)$optValue;
            // find option by translated label in default language
            $idOpt = $db->getValue(
                "SELECT o.id_option 
                 FROM $tblOpt o 
                 INNER JOIN $tblOptLang ol ON o.id_option = ol.id_option 
                 WHERE o.id_variable=" . (int)$idVar . " AND ol.id_lang=" . (int)$defaultIdLang . " AND ol.label='" . pSQL($optValue) . "'"
            );
            if ($idOpt) {
                if ($overwrite) {
                    // update label across languages
                    foreach ($languages as $lang) {
                        $idLang = (int)$lang['id_lang'];
                        $exists = $db->getValue("SELECT 1 FROM $tblOptLang WHERE id_option=" . (int)$idOpt . " AND id_lang=" . $idLang);
                        if ($exists) {
                            $db->execute("UPDATE $tblOptLang SET label='" . pSQL($optValue) . "' WHERE id_option=" . (int)$idOpt . " AND id_lang=" . $idLang);
                        } else {
                            $db->execute("INSERT INTO $tblOptLang (id_option, id_lang, label) VALUES (" . (int)$idOpt . ", " . $idLang . ", '" . pSQL($optValue) . "')");
                        }
                    }
                    $results['imported'][] = "Option updated: $code => $optValue";
                } else {
                    $results['skipped'][] = "Option exists and skipped: $code => $optValue";
                }
            } else {
                // create option with default values then add i18n labels
                $db->execute("INSERT INTO $tblOpt (id_variable, price, position, weight, active) VALUES (" . (int)$idVar . ", 0, 0, 0, 1)");
                $idOpt = (int)$db->Insert_ID();
                foreach ($languages as $lang) {
                    $idLang = (int)$lang['id_lang'];
                    $db->execute("INSERT INTO $tblOptLang (id_option, id_lang, label) VALUES (" . (int)$idOpt . ", " . $idLang . ", '" . pSQL($optValue) . "')");
                }
                $results['imported'][] = "Option created: $code => $optValue";
            }
        }
    }

    private function importTooltip($code, $html, $selections, &$results)
    {
        $db = Db::getInstance();
        $tblBase = pSQL(_DB_PREFIX_).'variable_tooltip';
        $tblLang = pSQL(_DB_PREFIX_).'variable_tooltip_lang';
        $overwrite = !empty($selections['overwrite']);
        $idLang = (int) \Configuration::get('PS_LANG_DEFAULT');

        // Find or create base tooltip by label
        $idTooltip = (int) $db->getValue("SELECT id_variable_tooltip FROM $tblBase WHERE label='".pSQL($code)."'");
        if (!$idTooltip) {
            $db->execute("INSERT INTO $tblBase (label) VALUES ('".pSQL($code)."')");
            $idTooltip = (int) $db->Insert_ID();
        }

        // Upsert language row (store HTML content in lang table)
        $existsLang = $db->getValue("SELECT 1 FROM $tblLang WHERE id_variable_tooltip=".(int)$idTooltip." AND id_lang=".$idLang);
        if ($existsLang) {
            if ($overwrite) {
                $db->execute("UPDATE $tblLang SET text='".pSQL($html, true)."' WHERE id_variable_tooltip=".(int)$idTooltip." AND id_lang=".$idLang);
                $results['imported'][] = "Tooltip updated: $code";
            } else {
                $results['skipped'][] = "Tooltip exists and skipped: $code";
            }
        } else {
            $db->execute("INSERT INTO $tblLang (id_variable_tooltip, id_lang, text) VALUES (".(int)$idTooltip.", ".$idLang.", '".pSQL($html, true)."')");
            $results['imported'][] = "Tooltip created: $code";
        }
    }

    private function importAlert($alert, $selections, &$results)
    {
        $db = Db::getInstance();
        $productRef = isset($alert['product_ref']) ? $alert['product_ref'] : '';
        $varCode = $alert['variable_code'];
        $optValue = $alert['option_value'];
        $message = isset($alert['message']) ? $alert['message'] : '';

        $tblAlert = pSQL(_DB_PREFIX_).'alert_messages';

        // find product by id (preferred), or by reference, or by localized name (any language)
        $idProduct = 0;
        if (isset($alert['id_product']) && (int)$alert['id_product'] > 0) {
            $idProduct = (int)$alert['id_product'];
        }
        $trimRef = trim((string)$productRef);
        if (!$idProduct && $trimRef !== '' && ctype_digit($trimRef)) {
            $idProduct = (int) $trimRef;
        }
        if (!$idProduct && $trimRef !== '') {
            $idProduct = (int) $db->getValue("SELECT id_product FROM `"._DB_PREFIX_."product` WHERE reference='".pSQL($trimRef)."'");
        }
        if (!$idProduct && $trimRef !== '') {
            $langs = \Language::getLanguages(true);
            foreach ($langs as $lang) {
                $idLang = (int)$lang['id_lang'];
                $sqlName = "SELECT pl.id_product 
                            FROM `"._DB_PREFIX_."product_lang` pl 
                            WHERE pl.name='".pSQL($trimRef)."' AND pl.id_lang=".$idLang."
                            ORDER BY pl.id_product ASC";
                $idProduct = (int) $db->getValue($sqlName);
                if ($idProduct) { break; }
            }
        }
        if (!$idProduct) {
            $results['warnings'][] = "Product not found for alert, skipped: $productRef";
            return;
        }

        // find variable and option ids if possible
        $idVar = $db->getValue("SELECT id_variable FROM ".pSQL(_DB_PREFIX_)."variable WHERE name='".pSQL($varCode)."'");
        if (!$idVar) {
            $results['warnings'][] = "Variable not found for alert, skipped: $varCode";
            return;
        }

        $defaultIdLang = (int) \Configuration::get('PS_LANG_DEFAULT');
        $idOpt = $db->getValue(
            "SELECT o.id_option 
             FROM ".pSQL(_DB_PREFIX_)."option o
             INNER JOIN ".pSQL(_DB_PREFIX_)."option_lang ol ON (o.id_option = ol.id_option AND ol.id_lang = ".(int)$defaultIdLang.")
             WHERE o.id_variable='".pSQL($idVar)."' AND ol.label='".pSQL($optValue)."'"
        );
        if (!$idOpt) {
            $results['warnings'][] = "Option not found for alert, skipped: $varCode => $optValue";
            return;
        }

        // Insert alert (do not update existing alerts automatically)
        $db->execute("INSERT INTO $tblAlert (product_id, variable_id, option_id, message) VALUES ('".pSQL($idProduct)."', '".pSQL($idVar)."', '".pSQL($idOpt)."', '".pSQL($message)."')");
        $results['imported'][] = "Alert created for product $productRef";
    }

    private function importProductConfig($productRef, $cfg, $elements, $selections, &$results)
    {
        $db = Db::getInstance();
        $tbl = pSQL(_DB_PREFIX_).'product_setting';

        // find product id (accept id or reference)
        if (is_numeric($productRef)) {
            $idProduct = (int) $productRef;
        } else {
            $idProduct = $db->getValue("SELECT id_product FROM `"._DB_PREFIX_."product` WHERE reference='".pSQL($productRef)."'");
        }
        if (!$idProduct) {
            $results['warnings'][] = "Product not found, product config skipped: $productRef";
            return;
        }

        // Map incoming keys to table columns
        $columnMap = [
            'formula_price' => 'formula_price',
            'formula_weight' => 'formula_weight',
            'formula_thickness' => 'formula_thickness',
            'formula_shipping' => 'formula_shipping',
            'tiered' => 'tiered',
            'tiered_pricing_rules' => 'tiered',
            'baned_comb' => 'baned_comb',
        ];

        // Detect existing columns to avoid SQL errors if schema varies
        $columns = $db->executeS("SHOW COLUMNS FROM `$tbl`");
        $existingCols = [];
        foreach ($columns as $c) {
            if (isset($c['Field'])) {
                $existingCols[$c['Field']] = true;
            }
        }

        // Build set of updates based on selected elements and available columns
        $setData = [];
        foreach ($elements as $el) {
            if (!isset($cfg[$el])) {
                continue;
            }
            if (!isset($columnMap[$el])) {
                continue;
            }
            $col = $columnMap[$el];
            if (!isset($existingCols[$col])) {
                continue;
            }
            $val = $cfg[$el];
            // Normalize arrays/objects to JSON for storage
            if (is_array($val) || is_object($val)) {
                $val = json_encode($val, JSON_UNESCAPED_UNICODE);
            }
            $setData[$col] = $val;
        }

        if (empty($setData)) {
            $results['warnings'][] = "No selected product elements for $productRef";
            return;
        }

        // Find existing config by product
        $existing = $db->getValue("SELECT id_product_setting FROM $tbl WHERE id_product='".pSQL($idProduct)."'");
        $overwrite = !empty($selections['overwrite']);
        if ($existing) {
            if ($overwrite) {
                $assignments = [];
                foreach ($setData as $col => $val) {
                    $assignments[] = "`$col`='".pSQL($val, true)."'";
                }
                if (!empty($assignments)) {
                    $sql = "UPDATE $tbl SET ".implode(',', $assignments)." WHERE id_product_setting='".pSQL($existing)."'";
                    $db->execute($sql);
                }
                $results['imported'][] = "Product config updated: $productRef";
            } else {
                $results['skipped'][] = "Product config exists and skipped: $productRef";
            }
        } else {
            // Provide defaults for required columns that might be absent from selection
            $defaults = [
                'formula_price' => '',
                'formula_weight' => '',
                'formula_thickness' => '',
                'formula_shipping' => '',
                'tiered' => '',
                'baned_comb' => '0',
            ];
            $insertData = $defaults;
            foreach ($setData as $col => $val) {
                $insertData[$col] = $val;
            }
            // Filter to existing columns only
            $insertData = array_filter($insertData, function ($k) use ($existingCols) { return isset($existingCols[$k]); }, ARRAY_FILTER_USE_KEY);
            $fields = array_map(function ($f) { return "`$f`"; }, array_keys($insertData));
            $values = array_map(function ($v) { 
                if (is_array($v) || is_object($v)) {
                    $v = json_encode($v, JSON_UNESCAPED_UNICODE);
                }
                return "'".pSQL($v, true)."'"; 
            }, array_values($insertData));
            $db->execute("INSERT INTO $tbl (`id_product`, ".implode(',', $fields).") VALUES ('".pSQL($idProduct)."', ".implode(',', $values).")");
            $results['imported'][] = "Product config created: $productRef";
        }
    }
}
