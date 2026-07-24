<?php
/**
 * OfferHistoryLogger - Append-only audit trail for offers.
 *
 * Every state change must go through this class so the history is complete.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class OfferHistoryLogger
{
    /**
     * Log a history entry.
     *
     * @param int $id_offer
     * @param string $action One of OfferHistory::ACTION_*
     * @param array $context
     *   - id_offer_product: int|null
     *   - id_customer: int|null
     *   - id_employee: int|null
     *   - previous_value: string|null
     *   - new_value: string|null
     *   - description: string|null
     * @return int|false Inserted ID or false on failure
     */
    public static function log($id_offer, $action, $context = [])
    {
        $row = [
            'id_offer'         => (int) $id_offer,
            'id_offer_product' => isset($context['id_offer_product']) ? (int) $context['id_offer_product'] : null,
            'id_customer'      => isset($context['id_customer']) ? (int) $context['id_customer'] : null,
            'id_employee'       => isset($context['id_employee']) ? (int) $context['id_employee'] : null,
            'action'            => pSQL($action),
            'previous_value'   => isset($context['previous_value']) ? pSQL($context['previous_value']) : null,
            'new_value'        => isset($context['new_value']) ? pSQL($context['new_value']) : null,
            'description'      => isset($context['description']) ? pSQL($context['description']) : null,
            'date_add'         => date('Y-m-d H:i:s'),
        ];

        $cols = implode(', ', array_keys($row));
        $vals = implode(', ', array_map(function ($v) {
            return $v === null ? 'NULL' : (is_numeric($v) && substr($v, 0, 1) !== '"' ? $v : '"' . $v . '"');
        }, array_values($row)));

        if (Db::getInstance()->execute(
            'INSERT INTO ' . _DB_PREFIX_ . 'offer_history (' . $cols . ') VALUES (' . $vals . ')'
        )) {
            return (int) Db::getInstance()->Insert_ID();
        }

        return false;
    }
}
