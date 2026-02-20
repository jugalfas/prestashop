<div class="panel">
    <h3>{$module->l('Import Configuration', 'productpriceconfig')}</h3>

    {if isset($import_errors) && $import_errors}
        <div class="alert alert-danger">
            {foreach from=$import_errors item=err}<div>{$err}</div>{/foreach}
        </div>
    {/if}
    
    {if $import_step == 1}
        <form method="post" enctype="multipart/form-data" action="{$current}">
            <input type="hidden" name="token" value="{$token}" />
            <div class="form-group">
                <label>{$module->l('Upload exported JSON file')}</label>
                <input type="file" name="import_file" accept="application/json" />
            </div>
            <button type="submit" name="submitUploadImportFile"
                class="btn btn-primary">{$module->l('Upload & Analyze')}</button>
        </form>
    {/if}

    {if $import_step == 2}
        <h4>{$module->l('Analysis & Preview')}</h4>
        <ul>
            <li>✔ {$parsed_data.summary.variables} {$module->l('Variables')}</li>
            <li>✔ {$parsed_data.summary.variable_options} {$module->l('Variable options')}</li>
            <li>✔ {$parsed_data.summary.tooltips} {$module->l('Tooltips')}</li>
            <li>✔ {$parsed_data.summary.alerts} {$module->l('Alert messages')}</li>
            <li>✔ {$parsed_data.summary.products} {$module->l('Products with pricing configuration')}</li>
        </ul>

        <form method="post" action="{$current}">
            <input type="hidden" name="token" value="{$token}" />
            <input type="hidden" name="json_content" value="{$json_content|escape:'htmlall':'UTF-8'}" />

            <h5>{$module->l('Select what to import')}</h5>

            <h6>{$module->l('Variables & Options')}</h6>
            {foreach from=$parsed_data.variables key=code item=var}
                <div style="margin-bottom:8px;">
                    <strong>{$var.label} ({$code})</strong><br />
                    {foreach from=$var.options item=opt}
                        <label style="margin-right:8px;">
                            <input type="checkbox" name="variables[{$code}][]" value="{$opt|escape:'htmlall':'UTF-8'}" checked />
                            {$opt}
                        </label>
                    {/foreach}
                </div>
            {/foreach}

            <h6>{$module->l('Tooltips')}</h6>
            {foreach from=$parsed_data.tooltips key=code item=html}
                <label style="display:block;"><input type="checkbox" name="tooltips[]" value="{$code}" checked />
                    {$code}</label>
            {/foreach}

            <h6>{$module->l('Alert Messages')}</h6>
            {foreach from=$parsed_data.alerts key=ai item=al}
                <label style="display:block;"><input type="checkbox" name="alerts[]" value="{$ai}" checked />
                    {$al.product_ref}
                    — {$al.variable_code} — {$al.option_value}</label>
            {/foreach}

            <h6>{$module->l('Product Pricing Configuration')}</h6>
            {foreach from=$parsed_data.products key=pref item=pcfg}
                <div style="margin-bottom:8px;">
                    <strong>{$pref}</strong><br />
                    {foreach from=$pcfg key=k item=v}
                        <label style="margin-right:8px;"><input type="checkbox" name="products[{$pref}][]" value="{$k}" checked />
                            {$k}</label>
                    {/foreach}
                </div>
            {/foreach}

            <h6>{$module->l('Import Options')}</h6>
            <label><input type="checkbox" name="overwrite_existing" value="1" />
                {$module->l('Overwrite existing configuration')}</label><br />
            <label><input type="checkbox" name="dry_run" value="1" /> {$module->l('Dry run (no DB changes)')}</label>

            <div style="margin-top:12px;">
                <button type="submit" name="submitRunImport" class="btn btn-success">{$module->l('Run Import')}</button>
            </div>
        </form>
    {/if}

    {if $import_step == 5}
        <h4>{$module->l('Import Results')}</h4>
        {if isset($import_result)}
            <h5>{$module->l('Imported')}</h5>
            <ul>
                {foreach from=$import_result.imported item=i}<li>{$i}</li>{/foreach}
            </ul>
            <h5>{$module->l('Skipped')}</h5>
            <ul>{foreach from=$import_result.skipped item=s}<li>{$s}</li>{/foreach}</ul>
            <h5>{$module->l('Warnings')}</h5>
            <ul>{foreach from=$import_result.warnings item=w}<li>{$w}</li>{/foreach}</ul>
            <h5>{$module->l('Errors')}</h5>
            <ul>{foreach from=$import_result.errors item=e}<li>{$e}</li>{/foreach}</ul>
            <p>{$module->l('Import log id')}: {$import_result.log_id}</p>
        {/if}
    {/if}
</div>