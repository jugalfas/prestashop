<?php
class Order extends OrderCore
{
    public static function generateReference()
    {
        return '';
    }

    public function add($autodate = true, $null_values = false)
    {
        $result = parent::add($autodate, $null_values);

        if (!$result) {
            return false;
        }

        $this->injectSequentialReference();

        return $result;
    }

    private function injectSequentialReference()
    {
        try {
            if (!Module::isInstalled('onlyprint_seqref') || !Module::isEnabled('onlyprint_seqref')) {
                return;
            }

            $module = Module::getInstanceByName('onlyprint_seqref');
            if (!$module) {
                return;
            }

            if (!method_exists($module, 'shouldGenerateReference') || !$module->shouldGenerateReference($this->id)) {
                return;
            }

            $oldReference = $this->reference;

            if (!method_exists($module, 'generateNextReference')) {
                return;
            }

            $newReference = $module->generateNextReference($this->id, $oldReference);

            if ($newReference) {
                $this->reference = $newReference;

                Db::getInstance()->update(
                    'orders',
                    array('reference' => pSQL($newReference)),
                    'id_order = ' . (int)$this->id
                );
            }
        } catch (Exception $e) {
        }
    }


}
