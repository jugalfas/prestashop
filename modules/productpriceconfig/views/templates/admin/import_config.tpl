{literal}
    <style>
        .md-checkbox{position:relative;margin:0;margin:initial;text-align:left;display:block;margin-bottom:6px}
        .md-checkbox.md-checkbox-inline{display:inline-block;margin-bottom:0}
        .md-checkbox label{padding-left:28px;margin-bottom:0;display:inline-block;font-weight:400;line-height:20px}
        .md-checkbox label strong{font-weight:400}
        .md-checkbox .md-checkbox-control{cursor:pointer}
        .md-checkbox .md-checkbox-control::before,.md-checkbox .md-checkbox-control::after{position:absolute;top:0;left:0;content:""}
        .md-checkbox .md-checkbox-control::before{width:20px;height:20px;cursor:pointer;background:#fff;border:2px solid #b3c7cd;border-radius:2px;transition:background .3s}
        .md-checkbox [type="checkbox"]{display:none;outline:0}
        .md-checkbox [type="checkbox"]:disabled+.md-checkbox-control{cursor:not-allowed;opacity:.5}
        .md-checkbox [type="checkbox"]:checked+.md-checkbox-control::before{background:#25b9d7;border:none}
        .md-checkbox [type="checkbox"]:checked+.md-checkbox-control::after{top:4.5px;left:3px;width:14px;height:7px;border:2px solid #fff;border-top-style:none;border-right-style:none;transform:rotate(-45deg)}
        .import-wrap{padding:20px}
        .dropzone{position:relative;border:2px dashed #c8d7dc;border-radius:4px;background:#f7fbfc;min-height:140px;display:flex;align-items:center;justify-content:center;text-align:center;padding:16px;cursor:pointer}
        .dropzone:hover{background:#f2f8fa}
        .dropzone input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer}
        .dz-title{font-weight:600;margin-bottom:4px}
        .dz-meta{color:#6c868e;font-size:12px}
        .dz-file{margin-top:8px}
        .grid{display:flex;flex-wrap:wrap;gap:16px}
        .card{flex:1 1 48%;min-width:340px;background:#fff;border:1px solid #eaeef1;border-radius:4px;padding:12px}
        .card-body{margin-top:8px}
        .chips{display:flex;flex-wrap:wrap;gap:8px 16px}
        .chips > div{width:calc(50% - 16px)}
        @media (max-width:991px){.card{flex:1 1 100%;min-width:0}.chips > div{width:100%}}
    /* Import preview layout matching export */
    .tree-list{margin-top:10px}
    #imp-section-variables{display:flex;flex-wrap:wrap;gap:16px}
    #imp-section-variables > li{flex:1 1 48%;min-width:380px;background:#fff;border:1px solid #eaeef1;border-radius:4px;padding:10px 12px}
    #imp-section-variables > li > ul.list-unstyled{display:flex;flex-wrap:wrap;gap:8px 16px;margin-left:0 !important;border-left:0 !important;padding-left:0 !important}
    #imp-section-variables > li > ul.list-unstyled > li{width:calc(50% - 16px)}
    #imp-section-tooltips{display:flex;flex-wrap:wrap;gap:12px}
    #imp-section-tooltips > li{width:calc(33.333% - 12px);min-width:260px;margin:0;vertical-align:top}
    @media (min-width:1400px){#imp-section-variables > li > ul.list-unstyled > li{width:calc(33.333% - 16px)}}
    @media (max-width:991px){
      #imp-section-variables > li{flex:1 1 100%;min-width:0}
      #imp-section-variables > li > ul.list-unstyled > li{width:100%}
      #imp-section-tooltips > li{width:100%;min-width:0}
    }
    </style>
{/literal}

<div class="panel">
    <div class="panel-heading"><i class="icon-download"></i> {$module->l('Import Configuration', 'productpriceconfig')}
    </div>
    <div class="import-wrap">
        {if isset($import_errors) && $import_errors}
            <div class="alert alert-danger">{foreach from=$import_errors item=err}<div>{$err}</div>{/foreach}</div>
        {/if}

        <form method="post" enctype="multipart/form-data" action="{$current}" class="form-horizontal"
            id="import_config_form">
            <form method="post" enctype="multipart/form-data" action="{$current}" class="form-horizontal">
                <div class="panel">
                    <div class="panel-heading"><i class="icon-cloud-upload"></i> {$module->l('Upload JSON')}</div>
                    <div class="panel-body">
                        <div class="dropzone" id="import_dropzone">
                            <div>
                                <div class="dz-title">{$module->l('Drop JSON here or click to browse')}</div>
                                <div class="dz-meta">{$module->l('Only .json files')}</div>
                            </div>
                            <input type="file" name="import_file" accept="application/json" id="import_file">
                        </div>
                        <div class="dz-file" id="import_file_name" style="display:none;"></div>
                    </div>
                    <div class="panel-footer">
                        <button type="submit" name="submitUploadImportFile" id="btnUpload" class="btn btn-primary"
                            disabled>
                            <i class="process-icon-upload"></i> {$module->l('Upload & Analyze')}
                        </button>
                    </div>
                </div>
    </div>
    {literal}
        <script>
            (function() {
                var input = document.getElementById('import_file');
                var dz = document.getElementById('import_dropzone');
                var nameEl = document.getElementById('import_file_name');
                var btn = document.getElementById('btnUpload');

                function setFile(f) {
                    if (!f) return;
                    if(f.type!=='application/json' && !f.name.toLowerCase().endsWith('.json')){return;}
                    nameEl.style.display = 'block';
                    nameEl.textContent = f.name + ' (' + Math.round(f.size / 1024) + ' KB)';
                    btn.disabled = false;
                }
                dz.addEventListener('dragover',function(e){e.preventDefault();});
                dz.addEventListener('drop', function(e) {
                    e.preventDefault();
                    if (!e.dataTransfer || !e.dataTransfer.files || !e.dataTransfer.files[0]) return;
                    var dt = new DataTransfer();
                    dt.items.add(e.dataTransfer.files[0]);
                    input.files = dt.files;
                    setFile(input.files[0]);
                });
                input.addEventListener('change', function() {
                    setFile(input.files[0]);
                });
            })();
        </script>
    {/literal}
    </form>

    {if $import_step == 2}
        <div class="panel">
            <div class="panel-heading"><i class="icon-search"></i> {$module->l('Analysis & Preview')}</div>
            <div class="panel-body">
                <ul style="margin:0 0 10px 18px">
                    <li>{$parsed_data.summary.variables} {$module->l('Variables')}</li>
                    <li>{$parsed_data.summary.variable_options} {$module->l('Variable options')}</li>
                    <li>{$parsed_data.summary.tooltips} {$module->l('Tooltips')}</li>
                    <li>{$parsed_data.summary.alerts} {$module->l('Alert messages')}</li>
                    <li>{$parsed_data.summary.products} {$module->l('Products with pricing configuration')}</li>
                </ul>
            </div>
        </div>

        <form method="post" action="{$current}">
            <input type="hidden" name="token" value="{$token}" />
            <input type="hidden" name="json_content" value="{$json_content|escape:'htmlall':'UTF-8'}" />

            <div class="grid">
                    <div class="card">
                        <div class="card-title">
                            <div class="md-checkbox md-checkbox-inline">
                                <label>
                                    <input type="checkbox" class="select-all-section" data-target="imp-section-variables" />
                                    <i class="md-checkbox-control"></i>
                                </label>
                                <strong>{$module->l('Variables & Options')}</strong>
                            </div>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled tree-list" id="imp-section-variables">
                                {foreach from=$parsed_data.variables key=code item=var}
                                    <li>
                                        <div class="md-checkbox md-checkbox-inline">
                                            <label>
                                                <input type="checkbox" class="variable-parent-checkbox" data-code="{$var.code}">
                                                <i class="md-checkbox-control"></i>
                                                <strong>{$var.label} ({$var.code})</strong>
                                            </label>
                                        </div>
                                        <ul class="list-unstyled" style="margin-left:25px;border-left:1px solid #ccc;padding-left:10px;">
                                            {foreach from=$var.options item=opt}
                                                {assign var=optLabel value=$opt}
                                                {if isset($opt.value) || isset($opt.label)}
                                                    {assign var=optLabel value=$opt.value|default:$opt.label}
                                                {/if}
                                                <li>
                                                    <div class="md-checkbox md-checkbox-inline">
                                                        <label>
                                                            <input type="checkbox" class="option-child-checkbox" name="variables[{$var.code}][]" value="{$optLabel|escape:'htmlall':'UTF-8'}" data-parent="{$var.code}" checked />
                                                            <i class="md-checkbox-control"></i> {$optLabel}
                                                        </label>
                                                    </div>
                                                </li>
                                            {/foreach}
                                        </ul>
                                    </li>
                                {/foreach}
                            </ul>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-title">
                            <div class="md-checkbox md-checkbox-inline">
                                <label>
                                    <input type="checkbox" class="select-all-section" data-target="imp-section-tooltips" />
                                    <i class="md-checkbox-control"></i>
                                </label>
                                <strong>{$module->l('Tooltips')}</strong>
                            </div>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled" id="imp-section-tooltips">
                                {foreach from=$parsed_data.tooltips key=code item=html}
                                    <li>
                                        <div class="md-checkbox md-checkbox-inline">
                                            <label>
                                                <input type="checkbox" name="tooltips[]" value="{$code}" checked />
                                                <i class="md-checkbox-control"></i> <strong>{$code}</strong>
                                            </label>
                                        </div>
                                    </li>
                                {/foreach}
                            </ul>
                        </div>
                    </div>

                <div class="card">
                    <div class="card-title"><strong>{$module->l('Alert Messages')}</strong></div>
                    <div class="card-body">
                        {foreach from=$parsed_data.alerts key=ai item=al}
                            <div class="md-checkbox">
                                <label>
                                    <input type="checkbox" name="alerts[]" value="{$ai}" checked />
                                    <i class="md-checkbox-control"></i> {$al.product_ref} — {$al.variable_code} —
                                    {$al.option_value}
                                </label>
                            </div>
                        {/foreach}
                    </div>
                </div>

                <div class="card">
                    <div class="card-title"><strong>{$module->l('Product Pricing Configuration')}</strong></div>
                    <div class="card-body">
                        {foreach from=$parsed_data.products key=pref item=pcfg}
                            {assign var=prodLabel value=$parsed_data.product_labels[$pref]|default:$pref}
                            <div style="margin-bottom:10px;">
                                <div style="margin-bottom:6px;"><strong>{$prodLabel}</strong></div>
                                <div class="md-checkbox md-checkbox-inline">
                                    <label>
                                        <input type="checkbox" name="products[{$pref}][]" value="formula_price" checked />
                                        <i class="md-checkbox-control"></i> {$module->l('Formula price')}
                                    </label>
                                </div>
                                <div class="md-checkbox md-checkbox-inline">
                                    <label>
                                        <input type="checkbox" name="products[{$pref}][]" value="formula_weight" checked />
                                        <i class="md-checkbox-control"></i> {$module->l('Formula weight')}
                                    </label>
                                </div>
                                <div class="md-checkbox md-checkbox-inline">
                                    <label>
                                        <input type="checkbox" name="products[{$pref}][]" value="formula_thickness" checked />
                                        <i class="md-checkbox-control"></i> {$module->l('Formula thickness')}
                                    </label>
                                </div>
                                <div class="md-checkbox md-checkbox-inline">
                                    <label>
                                        <input type="checkbox" name="products[{$pref}][]" value="formula_shipping" checked />
                                        <i class="md-checkbox-control"></i> {$module->l('Formula shipping')}
                                    </label>
                                </div>
                                <div class="md-checkbox md-checkbox-inline">
                                    <label>
                                        <input type="checkbox" name="products[{$pref}][]" value="tiered_pricing_rules"
                                            checked />
                                        <i class="md-checkbox-control"></i> {$module->l('Tiered price')}
                                    </label>
                                </div>
                                <div class="md-checkbox md-checkbox-inline">
                                    <label>
                                        <input type="checkbox" name="products[{$pref}][]" value="baned_comb" checked />
                                        <i class="md-checkbox-control"></i> {$module->l('Banned combinations')}
                                    </label>
                                </div>
                                <div class="md-checkbox md-checkbox-inline">
                                    <label>
                                        <input type="checkbox" name="products[{$pref}][]" value="odd_quantity_percentage"
                                            checked />
                                        <i class="md-checkbox-control"></i> {$module->l('Odd quantity percentage')}
                                    </label>
                                </div>
                            </div>
                        {/foreach}
                    </div>
                </div>
            </div>

            <div class="panel" style="margin-top:16px;">
                <div class="panel-heading"><i class="icon-cogs"></i> {$module->l('Import Options')}</div>
                <div class="panel-body">
                    <div class="md-checkbox md-checkbox-inline" style="margin-right:16px;">
                        <label>
                            <input type="checkbox" name="overwrite_existing" value="1" />
                            <i class="md-checkbox-control"></i> {$module->l('Overwrite existing configuration')}
                        </label>
                    </div>
                    <div class="md-checkbox md-checkbox-inline">
                        <label>
                            <input type="checkbox" name="dry_run" value="1" />
                            <i class="md-checkbox-control"></i> {$module->l('Dry run (no DB changes)')}
                        </label>
                    </div>
                </div>
            </div>

            <div>
                <button type="submit" name="submitRunImport" class="btn btn-success">
                    <i class="process-icon-import"></i> {$module->l('Run Import')}
                </button>
            </div>
        </form>
    {/if}

    {if $import_step == 5}
        <div class="panel">
            <div class="panel-heading"><i class="icon-check"></i> {$module->l('Import Results')}</div>
            <div class="panel-body">
                {if isset($import_result)}
                    <h5>{$module->l('Imported')}</h5>
                    <ul>{foreach from=$import_result.imported item=i}<li>{$i}</li>{/foreach}</ul>
                    <h5>{$module->l('Skipped')}</h5>
                    <ul>{foreach from=$import_result.skipped item=s}<li>{$s}</li>{/foreach}</ul>
                    <h5>{$module->l('Warnings')}</h5>
                    <ul>{foreach from=$import_result.warnings item=w}<li>{$w}</li>{/foreach}</ul>
                    <h5>{$module->l('Errors')}</h5>
                    <ul>{foreach from=$import_result.errors item=e}<li>{$e}</li>{/foreach}</ul>
                    <p>{$module->l('Import log id')}: {$import_result.log_id}</p>
                {/if}
            </div>
        </div>
{literal}
<script>
  (function(){
    function bindParentChildLogic(parentClass, childClass, parentAttr, childParentAttr){
      $(document).on('change', parentClass, function(){
        var id=$(this).attr(parentAttr);
        var on=$(this).prop('checked');
        $(childClass+'['+childParentAttr+'="'+id+'"]').each(function(){$(this).prop('checked', on)});
      });
      $(document).on('change', childClass, function(){
        var id=$(this).attr(childParentAttr);
        var $p=$(parentClass+'['+parentAttr+'="'+id+'"]');
        var total=$(childClass+'['+childParentAttr+'="'+id+'"]').length;
        var checked=$(childClass+'['+childParentAttr+'="'+id+'"]:checked').length;
        if(checked===0){$p.prop('checked', false).prop('indeterminate', false);}
        else if(checked===total){$p.prop('checked', true).prop('indeterminate', false);}
        else{$p.prop('checked', false).prop('indeterminate', true);}
      });
    }
    $(function(){
      bindParentChildLogic('.variable-parent-checkbox','.option-child-checkbox','data-code','data-parent');
      $('.select-all-section').on('change', function(){
        var tid=$(this).data('target'); var on=$(this).prop('checked');
        $('#'+tid).find('input[type="checkbox"]').prop('checked', on).prop('indeterminate', false);
      });
    });
  })();
</script>
{/literal}
    {/if}
</div>
</div>
