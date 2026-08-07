{* 
, 
display_footer_product.tpl — THEME OVERRIDE 
, 
Place this file in: 
/html/prestashop/themes/theme_ecolife_marketplace2/modules/productpriceconfig/display_footer_product.tpl 
PrestaShop loads this version automatically in place of the module-version. 

WHAT DIT BESTAND WEL DOES: 
- Render the same $variables-list as that of the module, but 
in a 3-steppen cards-/chips-layout (.ppc-v2) i.p.v. a plate 
list dropdowns. 
- Each field retains the EXACT same underlying input/select-structure, 
data-attributen(data-formula-name, data-variable_id, data-id_option, 
data-price, data-weight, data-thickness) en class-name 
(input_for_url, btn-select-input, select_box_for_url, input-quantity-wanted) 
as the original. This is aware: front.js listens on the exact 
classes/attributes continue to work completely unintuitive. 

WHAT DIT BESTAND NOT DO: 
- No own pricing. calculate_totals() in front.js continues de 
Any place where the price is calculated. 
- No changes on front.js itself. 

VANGNET (see "More options" under step 2): 
- Any field whose formula_name does not appear in the following 
Defined $step1_fields / $step2_fields / $step3_fields wordt 
automatically shown in a generic "Overview options"-section, with 
the ORIGINAL rendering-style (usually the old dropdown/stepper-HTML). 
Zo dwijnit never stops a field, also not on a product 
with variables that we do not yet know. 
,
*}

{* ── Classification: which formula_name's are shown by which step ── 
This overview is full documentation — of actual {if}-conditions 
Stand by renderblock below. Using Printing Color/Paper/Using 
PATROON-matching (stripos), due to 1 rule shown below automatically 
Wrap/Binnenwerk-variant of that product (printed color wrap, papier binnenwerk, 
etc.) without that we have to add a new rule per product. *}
{* Geverified against actually rendered live HTML (losbladig printed, 
24-6-2026) + backoffice-Formula-name-field van Brochure/Softcover/Wire-O. 
LET OP capital letter "Orientation" — that's the real value. *}
{assign var="step1_fields" value="formaat,Orientation,[patroon: drukkleur,uitvoering]"}
{assign var="step2_fields" value="paginas,boorgaten,nieten,vouwen,scheidingsblad,[patroon: papier]"}
{assign var="step3_fields" value="oplage,documentcontrole,referentie"}
{* Exacte-match velden (geen Omslag/Binnenwerk-variatie te verwachten). *}
{assign var="known_fields" value="formaat,Orientation,orientation,wireobindplaats,paginas,boorgaten,nieten,vouwen,scheidingsblad,oplage,documentcontrole,referentie,Kenmerk,envelop,portokosten,hoogte,breedte,shipping_cost,uploadmethod"}
{* Patroon-basis velden: matcht ALS SUBSTRING, dus vangt ook drukkleuromslag,
   papiersoortomslag, papierbinnenwerk, uitvoeringbinnenwerk, etc. Gebruikt
   door zowel de render-secties hierboven als de vangnet-detectie hieronder,
   zodat een veld nooit tegelijk in stap 1/2 ÉN in "Overige opties" verschijnt. *}
{assign var="known_patterns" value="drukkleur,papier,uitvoering"}

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="/themes/theme_ecolife_marketplace2/modules/productpriceconfig/views/css/ppc-v2.css">

<div class="" id="loader" onclick="$(this).hide()" style="display: none;">
    <div class="modal-backdrop-loader" style="position:absolute;top:0;right:0;bottom:0;left:0;z-index:1000;background-color:#000;opacity:0.5;">
        <i class="fa fa-spinner fa-spin text-white font-30" style="position:absolute;right:50%;top:50%;font-size:26px;"></i>
    </div>
</div>

<div class="configure-area ppc-v2">
    <div class="ppc-conf">

        <div class="ppc-conf-header">
            <div class="ppc-conf-header-title">{l s='Product configureren' mod='productpriceconfig'}</div>
            <div class="ppc-step-label" id="ppcStepLabel">{l s='Stap' mod='productpriceconfig'} 1 {l s='van' mod='productpriceconfig'} 3</div>
        </div>

        <div class="ppc-step-nav">
            <button type="button" class="ppc-step-btn active" id="ppcTab1" onclick="ppcGoStep(1)">
                <span class="ppc-step-num">1</span>{l s='Formaat & kleur' mod='productpriceconfig'}
            </button>
            <button type="button" class="ppc-step-btn" id="ppcTab2" onclick="ppcGoStep(2)">
                <span class="ppc-step-num">2</span>{l s='Papier & opties' mod='productpriceconfig'}
            </button>
            <button type="button" class="ppc-step-btn" id="ppcTab3" onclick="ppcGoStep(3)">
                <span class="ppc-step-num">3</span>{l s='Oplage & prijs' mod='productpriceconfig'}
            </button>
        </div>

        <form action="#" id="kd_form">
            <span class="hidden_inputs">
                <input type="hidden" id="kd_id_product" name="id_product" value="{$id_product}">
                <input type="hidden" id="kd_id_product_attribute" name="id_product_attribute" value="0">
                <input type="hidden" id="kd_id_product_setting" name="id_product_setting" value="{$product_setting->id}">
                <input type="hidden" id="kd_baned_comb" name="baned_comb" value="{$product_setting->baned_comb}">
                <input type="hidden" id="kd_id_customization" name="id_customization" value="{$id_customization}">
                <input type="hidden" id="add-to-cart-or-refresh" value="{$urls.pages.cart}">
            </span>

            {* ── Type 7 fields (bv. "Shipping option") — ALTIJD als verbergen 
                  form-veld renderen, ongecht step-indelineation of vangnet-logica. 
                  BELANGRIJK: this is not a visual choice but a functional one 
                  Necessity. processAddToCart() in de module-PHP controleert 
                  voor ELK active variable-field of the product of one 
                  The corresponding variable_X-value in the POST-data is missing. 
                  die for even one field, and the whole addition is at fault 
                  "Please select all options", also as all desired fields 
                  they are filled correctly. The original render of this field 
                  That's why we always (met CSS-class 'hide' to avoid verbiage, 
                  we don't let the markup go away) — we do it here 
                  the same, only functional without visible superficial 
                  wrapper. Verified against live "Shipping option"-bug, 
                  26-6-2026. *}
            {foreach $variables as $t}
              {if $t.type == 7}
                <input type="hidden" name="variable_{$t.id_product_variable|intval}"
                    class="{$t.variable_name} input_for_url" value="{$t.default_option}"
                    data-variable_id="{$t.id_variable}" data-variable-name="{$t.variable_name}"
                    data-formula-name="{$t.formula_name}"
                    data-url_variable="{str_replace(' ', '_', strtolower($t.variable_name))}" data-type="{$t.type}">
              {/if}
            {/foreach}

            {* ═══════════════ STAP 1: Formaat & kleur ═══════════════ *}
            <div class="ppc-panel active" id="ppcPanel1">
              <div class="ppc-body">

                {* — Formaat: render als kaarten-grid in plaats van dropdown — *}
                {foreach $variables as $t}
                  {if $t.formula_name == 'formaat' && $t.active != 0 && $t.type == 2 && count($t.options_data)}
                    <div>
                      <div class="ppc-label">{$t.name|escape:'html':'UTF-8'}
                        {if $t.id_variable_tooltip}<span class="ppc-tip" data-tip="{$t.tooltip_text|strip_tags|escape:'html':'UTF-8'}">i</span>{/if}
                      </div>
                      <div class="ppc-fmt-grid" data-id="{$t.id_product_variable|intval}">
                        <input type="hidden" class="btn-select-input input_for_url {$t.variable_name}" id="{$t.variable_name}"
                            name="variable_{$t.id_product_variable|intval}" value="{$t.default_option}"
                            data-default_value="{$t.default_option}" data-variable_id="{$t.id_variable}"
                            data-variable-name="{$t.variable_name}" data-formula-name="{$t.formula_name}"
                            data-url_variable="{str_replace(' ', '_', strtolower($t.variable_name))}" data-type="{$t.type}">
                        {* Verbogen ECHTE select — front.js's change-listener op .select_box_for_url 
                          is the actual active trigger for calculate_totals() (verified 
                          against the live theme-override front.js, not the hidden-input-listener 
                          there is disabled). Our cards are placed programmatically above 
                          value + change, exactly as a click on the original <li> would do. *}
                        <select class="btn btn-select btn-select-value select_box_for_url" style="display:none"
                            data-variable_id="{$t.id_variable}" data-variable-name="{$t.variable_name}"
                            data-formula-name="{$t.formula_name}"
                            data-url_variable="{str_replace(' ', '_', strtolower($t.variable_name))}">
                          <option value="0">{l s='Kies een optie' mod='productpriceconfig'}</option>
                          {foreach $t.options_data as $option}
                            <option data-id_option="{$option.id}" data-price="{$option.price}"
                                data-weight="{$option.weight}" data-thickness="{$option.thickness}"
                                {if $option.id==$t.default_option} selected{/if}
                                value="{$option.id}">{$option.name|escape:'html':'UTF-8'}</option>
                          {/foreach}
                        </select>
                        {foreach $t.options_data as $option}
                          <div class="ppc-fmt-card{if $option.id == $t.default_option} on{/if}"
                               data-id_option="{$option.id}"
                               onclick="ppcSelectDropdownOption(this, '{$t.variable_name|escape:'javascript'}')">
                            <div class="ppc-fmt-page-wrap" data-fmt-name="{$option.name|escape:'html':'UTF-8'}"></div>
                            <div class="ppc-fmt-card-name">{$option.name|escape:'html':'UTF-8'}</div>
                            <div class="ppc-fmt-card-mm" data-fmt-name="{$option.name|escape:'html':'UTF-8'}"></div>
                          </div>
                        {/foreach}
                      </div>
                    </div>
                  {/if}
                {/foreach}

                {* — Hoogte/breedte: custom afmetingen voor afwijkende formaten — *}
                {foreach $variables as $t}
                  {if ($t.formula_name == 'hoogte' || $t.formula_name == 'breedte') && $t.active != 0 && ($t.type == 1 || $t.type == 4)}
                    <div>
                      <div class="ppc-label">{$t.name|escape:'html':'UTF-8'}
                        {if $t.id_variable_tooltip}<span class="ppc-tip" data-tip="{$t.tooltip_text|strip_tags|escape:'html':'UTF-8'}">i</span>{/if}
                      </div>
                      <div class="ppc-qty quantity-label">
                        <button type="button" class="ppc-q-btn minas qty-down">−</button>
                        <input type="number" class="ppc-q-inp form-control form-control-sm number-area input-quantity-wanted {$t.variable_name} input_for_url"
                            name="variable_{$t.id_product_variable|intval}"
                            value="{$t.p_minimum}" min="{$t.p_minimum}" max="{$t.p_maximum}"
                            data-formula-name="{$t.formula_name}" data-variable_id="{$t.id_variable}"
                            data-variable-name="{$t.variable_name}"
                            step="{if $t.multiplier != 0}{$t.multiplier}{else}{1}{/if}"
                            data-url_variable="{str_replace(' ', '_', strtolower($t.variable_name))}" data-type="{$t.type}">
                        <button type="button" class="ppc-q-btn plus qty-up">+</button>
                      </div>
                      <div class="custom_input_error"></div>
                    </div>
                    <div class="ppc-divider"></div>
                  {/if}
                {/foreach}

               {* — Orientation / Print color / Output: render as chips — 
Patroon-matching i.p.v. exact string: vangt also 
Variantes als 'drukkleuromslag', 'displayingbinnenwerk' 
(verified against Brochure/Softcover/Wire-O backoffice- 
warden, 24-6-2026). No hardcoded per-product-name. *}
                {foreach $variables as $t}
                  {if (stripos($t.formula_name, 'orientation') !== false
                       || stripos($t.formula_name, 'drukkleur') !== false
                       || stripos($t.formula_name, 'uitvoering') !== false
                       || $t.formula_name == 'envelop')
                      && $t.active != 0
                      && $t.type == 2 && count($t.options_data)}
                    <div class="ppc-divider"></div>
                    <div>
                      <div class="ppc-label">{$t.name|escape:'html':'UTF-8'}
                        {if $t.id_variable_tooltip}<span class="ppc-tip" data-tip="{$t.tooltip_text|strip_tags|escape:'html':'UTF-8'}">i</span>{/if}
                      </div>
                      <div class="ppc-chips" data-id="{$t.id_product_variable|intval}">
                        <input type="hidden" class="btn-select-input input_for_url {$t.variable_name}" id="{$t.variable_name}"
                            name="variable_{$t.id_product_variable|intval}" value="{$t.default_option}"
                            data-default_value="{$t.default_option}" data-variable_id="{$t.id_variable}"
                            data-variable-name="{$t.variable_name}" data-formula-name="{$t.formula_name}"
                            data-url_variable="{str_replace(' ', '_', strtolower($t.variable_name))}" data-type="{$t.type}">
                        <select class="btn btn-select btn-select-value select_box_for_url" style="display:none"
                            data-variable_id="{$t.id_variable}" data-variable-name="{$t.variable_name}"
                            data-formula-name="{$t.formula_name}"
                            data-url_variable="{str_replace(' ', '_', strtolower($t.variable_name))}">
                          <option value="0">{l s='Kies een optie' mod='productpriceconfig'}</option>
                          {foreach $t.options_data as $option}
                            <option data-id_option="{$option.id}" data-price="{$option.price}"
                                data-weight="{$option.weight}" data-thickness="{$option.thickness}"
                                {if $option.id==$t.default_option} selected{/if}
                                value="{$option.id}">{$option.name|escape:'html':'UTF-8'}</option>
                          {/foreach}
                        </select>
                        {foreach $t.options_data as $option}
                          <div class="ppc-chip{if $option.id == $t.default_option} on{/if}"
                               data-id_option="{$option.id}"
                               onclick="ppcSelectDropdownOption(this, '{$t.variable_name|escape:'javascript'}')">
                            {if stripos($t.formula_name, 'drukkleur') !== false}
                              {* Detecting generics: contains the option name a black-white- 
                                abdication? And the zw/w-icon, and the CMYK-white. 
                                Works on each variant ("Volledig zw/w", "Zwart-wit", 
                                "Black & White", etc.) without hardcoded product names. *}
                              {if stripos($option.name, 'zw/w') !== false || stripos($option.name, 'zwart') !== false || stripos($option.name, 'black') !== false}
                                <svg class="ppc-chip-color-icon" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                                  <circle cx="8" cy="8" r="7" fill="none" stroke="currentColor" stroke-width="1.3"/>
                                  <path d="M8 1a7 7 0 0 1 0 14z" fill="currentColor"/>
                                </svg>
                              {else}
                                <svg class="ppc-chip-color-icon" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                                  <circle cx="8" cy="8" r="7" fill="none" stroke="currentColor" stroke-width="1.3"/>
                                  <path d="M8 1a7 7 0 0 1 6.06 10.5L8 8z" fill="#00AEEF"/>
                                  <path d="M14.06 11.5A7 7 0 0 1 8 15l0-7z" fill="#EC008C"/>
                                  <path d="M8 15a7 7 0 0 1-6.06-10.5L8 8z" fill="#FFF200"/>
                                  <path d="M1.94 4.5A7 7 0 0 1 8 1l0 7z" fill="#1A1A1A"/>
                                </svg>
                              {/if}
                            {elseif stripos($t.formula_name, 'orientation') !== false}
                              {* Generieke detectie op de optienaam: "liggend"/"landscape"
                                 → bredere rechthoek, anders (staand/portrait/default) →
                                 smallere, hogere rechthoek. *}
                              {if stripos($option.name, 'liggend') !== false || stripos($option.name, 'landscape') !== false}
                                <svg class="ppc-chip-color-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                  <rect x="2" y="6" width="20" height="12" rx="1.5" fill="none" stroke="currentColor" stroke-width="2.5"/>
                                </svg>
                              {else}
                                <svg class="ppc-chip-color-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                  <rect x="6" y="2" width="12" height="20" rx="1.5" fill="none" stroke="currentColor" stroke-width="2.5"/>
                                </svg>
                              {/if}
                            {elseif stripos($t.formula_name, 'uitvoering') !== false}
                              {* Generieke detectie op de optienaam: "dubbelzijdig"/"double"
                                 → twee verschoven vellen, anders (enkelzijdig/default) →
                                 1 vel met lijntjes. *}
                              {if stripos($option.name, 'dubbelzijdig') !== false || stripos($option.name, 'double') !== false}
                                <svg class="ppc-chip-color-icon" viewBox="0 0 24 20" xmlns="http://www.w3.org/2000/svg">
                                  <rect x="1" y="3" width="14" height="16" rx="1" fill="none" stroke="currentColor" stroke-width="1.4" opacity="0.45"/>
                                  <rect x="9" y="1" width="14" height="16" rx="1" fill="none" stroke="currentColor" stroke-width="2"/>
                                  <line x1="12" y1="6" x2="19" y2="6" stroke="currentColor" stroke-width="1.3"/>
                                  <line x1="12" y1="9.5" x2="19" y2="9.5" stroke="currentColor" stroke-width="1.3"/>
                                </svg>
                              {else}
                                <svg class="ppc-chip-color-icon" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                  <rect x="3" y="1" width="14" height="18" rx="1" fill="none" stroke="currentColor" stroke-width="2"/>
                                  <line x1="6" y1="6" x2="14" y2="6" stroke="currentColor" stroke-width="1.3"/>
                                  <line x1="6" y1="9.5" x2="14" y2="9.5" stroke="currentColor" stroke-width="1.3"/>
                                </svg>
                              {/if}
                            {/if}
                            {$option.name|escape:'html':'UTF-8'}
                          </div>
                        {/foreach}
                      </div>
                    </div>
                  {/if}
                {/foreach}

              </div>
              <div class="ppc-step-nav-btns">
                <button type="button" class="ppc-btn-next" style="width:100%" onclick="ppcGoStep(2)">
                  {l s='Volgende: Papier & opties' mod='productpriceconfig'} →
                </button>
              </div>
            </div>

            {* ═══════════════ STAP 2: Papier & opties ═══════════════ *}
            <div class="ppc-panel" id="ppcPanel2">
              <div class="ppc-body">

                {* — Pagina's: render als stepper (type 1 of 4) — *}
                {foreach $variables as $t}
                  {if $t.formula_name == 'paginas' && $t.active != 0 && ($t.type == 1 || $t.type == 4)}
                    <div>
                      <div class="ppc-label">{$t.name|escape:'html':'UTF-8'}
                        {if $t.id_variable_tooltip}<span class="ppc-tip" data-tip="{$t.tooltip_text|strip_tags|escape:'html':'UTF-8'}">i</span>{/if}
                      </div>
                      <div class="ppc-qty quantity-label">
                        <button type="button" class="ppc-q-btn minas qty-down">−</button>
                        <input type="number" class="ppc-q-inp form-control form-control-sm number-area input-quantity-wanted {$t.variable_name} input_for_url"
                            name="variable_{$t.id_product_variable|intval}"
                            value="{$t.p_minimum}" min="{$t.p_minimum}" max="{$t.p_maximum}"
                            data-formula-name="{$t.formula_name}" data-variable_id="{$t.id_variable}"
                            data-variable-name="{$t.variable_name}"
                            step="{if $t.multiplier != 0}{$t.multiplier}{else}{1}{/if}"
                            data-url_variable="{str_replace(' ', '_', strtolower($t.variable_name))}" data-type="{$t.type}">
                        <button type="button" class="ppc-q-btn plus qty-up">+</button>
                      </div>
                      <div class="custom_input_error"></div>
                    </div>
                    <div class="ppc-divider"></div>
                  {/if}
                {/foreach}

                {* — Bindplaces (Wire-O): chips in step 2, ná formatkeuze — 
                      State aware in step 2 so that the format key (step 1) is already known 
                      When the customer places a bind place. De banned-combination-logica 
                      (rulesForbannedComb in front.js) set disabled-attributen op de 
                      hidden <option>-elements — ppc-bridge.js's syncDisabledChips() 
                      Pictured above via a MutationObserver to uncover irrelevant chips. *}
               {foreach $variables as $t}
                  {if $t.formula_name == 'wireobindplaats' && $t.active != 0 && $t.type == 2 && count($t.options_data)}
                    <div>
                      <div class="ppc-label">{$t.name|escape:'html':'UTF-8'}
                        {if $t.id_variable_tooltip}<span class="ppc-tip" data-tip="{$t.tooltip_text|strip_tags|escape:'html':'UTF-8'}">i</span>{/if}
                      </div>
                      <div class="ppc-chips ppc-chips-bannedcomb" data-id="{$t.id_product_variable|intval}">
                        <input type="hidden" class="btn-select-input input_for_url {$t.variable_name}" id="{$t.variable_name}"
                            name="variable_{$t.id_product_variable|intval}" value="{$t.default_option}"
                            data-default_value="{$t.default_option}" data-variable_id="{$t.id_variable}"
                            data-variable-name="{$t.variable_name}" data-formula-name="{$t.formula_name}"
                            data-url_variable="{str_replace(' ', '_', strtolower($t.variable_name))}" data-type="{$t.type}">
                        <select class="btn btn-select btn-select-value select_box_for_url" style="display:none"
                            data-variable_id="{$t.id_variable}" data-variable-name="{$t.variable_name}"
                            data-formula-name="{$t.formula_name}"
                            data-url_variable="{str_replace(' ', '_', strtolower($t.variable_name))}">
                          <option value="0">{l s='Kies een optie' mod='productpriceconfig'}</option>
                          {foreach $t.options_data as $option}
                            <option data-id_option="{$option.id}" data-price="{$option.price}"
                                data-weight="{$option.weight}" data-thickness="{$option.thickness}"
                                {if $option.id==$t.default_option} selected{/if}
                                value="{$option.id}">{$option.name|escape:'html':'UTF-8'}</option>
                          {/foreach}
                        </select>
                        {foreach $t.options_data as $option}
                          <div class="ppc-chip{if $option.id == $t.default_option} on{/if}"
                               data-id_option="{$option.id}"
                               onclick="ppcSelectDropdownOption(this, '{$t.variable_name|escape:'javascript'}')">
                            {$option.name|escape:'html':'UTF-8'}
                          </div>
                        {/foreach}
                      </div>
                    </div>
                    <div class="ppc-divider"></div>
                  {/if}
                {/foreach}

                {* — Papiersort: render as type→grammage cards (ie ppc-paper.js) — 
                      Patroon-matching: vangt 'papiersoort', 'papiersoortomslag', 
                      'papierbinnenwerk', etc. Also 'comslag' and 'binnenwerk' also 
                      EXACTE key (see Wire-O: a cover-weld can also 
                      pure 'amslag' heten, without 'paper' erin — geverified 
                      24-6-2026). Close print color/display as print, 
                      want 'Drukkleuromslag'/'Drukcoloromslag' horen bij de 
                      chips-section above, not here (others double render). 
                      Each matching field gets its EIGEN independent 
                      type→grammage-component (zie ppc-paper.js, dat per 
                      #ppcPaperRoot_<variable_name> apart initialisert). *}
                {foreach $variables as $t}
                  {if (stripos($t.formula_name, 'papier') !== false || $t.formula_name == 'omslag' || $t.formula_name == 'binnenwerk')
                      && stripos($t.formula_name, 'drukkleur') === false
                      && stripos($t.formula_name, 'uitvoering') === false
                      && $t.active != 0
                      && $t.type == 2 && count($t.options_data)}
                    <div>
                      <div class="ppc-label">{$t.name|escape:'html':'UTF-8'}
                        {if $t.id_variable_tooltip}<span class="ppc-tip" data-tip="{$t.tooltip_text|strip_tags|escape:'html':'UTF-8'}">i</span>{/if}
                      </div>

                      {* De ECHTE hidden input — exact zoals het origineel. *}
                      <input type="hidden" class="btn-select-input input_for_url {$t.variable_name}" id="{$t.variable_name}"
                          name="variable_{$t.id_product_variable|intval}" value="{$t.default_option}"
                          data-default_value="{$t.default_option}" data-variable_id="{$t.id_variable}"
                          data-variable-name="{$t.variable_name}" data-formula-name="{$t.formula_name}"
                          data-url_variable="{str_replace(' ', '_', strtolower($t.variable_name))}" data-type="{$t.type}">

                      {* Verborgen ECHTE select — dit is het element dat ppc-paper.js
                         vult + change triggert (niet de hidden input rechtstreeks),
                         exact zoals front.js's actieve .select_box_for_url-listener
                         verwacht. ppc-paper.js leest de opties hieronder over via
                         de data-island, niet vanuit deze <select> zelf. *}
                      <select class="btn btn-select btn-select-value select_box_for_url" style="display:none"
                          data-variable_id="{$t.id_variable}" data-variable-name="{$t.variable_name}"
                          data-formula-name="{$t.formula_name}"
                          data-url_variable="{str_replace(' ', '_', strtolower($t.variable_name))}">
                        <option value="0">{l s='Kies een optie' mod='productpriceconfig'}</option>
                        {foreach $t.options_data as $option}
                          <option data-id_option="{$option.id}" data-price="{$option.price}"
                              data-weight="{$option.weight}" data-thickness="{$option.thickness}"
                              {if $option.id==$t.default_option} selected{/if}
                              value="{$option.id}">{$option.name|escape:'html':'UTF-8'}</option>
                        {/foreach}
                      </select>

                      {* Data-island: de ruwe optielijst als JSON, voor ppc-paper.js.
                         Dit IS de data — geen aparte AJAX-call nodig, geen aanname
                         over de inhoud; wat hier staat is precies wat de module
                         al aanlevert via $t.options_data. *}
                      <script type="application/json" id="ppcPaperData_{$t.variable_name}">
                        [{foreach $t.options_data as $option}{literal}{{/literal}"id":{$option.id},"name":"{$option.name|escape:'javascript'}","price":{$option.price},"weight":"{$option.weight|escape:'javascript'}","thickness":"{$option.thickness|escape:'javascript'}"{literal}}{/literal}{if !$option@last},{/if}{/foreach}]
                      </script>

                      <div id="ppcPaperRoot_{$t.variable_name}" data-target="{$t.variable_name}"></div>
                    </div>
                    <div class="ppc-divider"></div>
                  {/if}
                {/foreach}

                {* — Boorgaten / Nieten / Vouwen / Scheidingsblad: chips in accordeon — *}
                {assign var="has_finishing" value=0}
                {foreach $variables as $t}
                  {if ($t.formula_name == 'boorgaten' || $t.formula_name == 'nieten' || $t.formula_name == 'vouwen' || $t.formula_name == 'scheidingsblad') && $t.active != 0 && $t.type == 2 && count($t.options_data)}
                    {assign var="has_finishing" value=1}
                  {/if}
                {/foreach}

                {if $has_finishing}
                <div class="ppc-accord-toggle" id="ppcAccordToggle" onclick="ppcToggleAccord()">
                  <span><span class="ppc-accord-plus">+</span>{l s='Afwerking toevoegen' mod='productpriceconfig'} ({l s='nieten, boorgaten, vouwen...' mod='productpriceconfig'})</span>
                  <span class="ppc-accord-arrow">⌄</span>
                </div>
                <div class="ppc-accord-body" id="ppcAccordBody">
                  {foreach $variables as $t}
                    {if ($t.formula_name == 'boorgaten' || $t.formula_name == 'nieten' || $t.formula_name == 'vouwen' || $t.formula_name == 'scheidingsblad') && $t.active != 0 && $t.type == 2 && count($t.options_data)}
                      <div>
                        <div class="ppc-label">{$t.name|escape:'html':'UTF-8'}
                          {if $t.id_variable_tooltip}<span class="ppc-tip" data-tip="{$t.tooltip_text|strip_tags|escape:'html':'UTF-8'}">i</span>{/if}
                        </div>
                        <div class="ppc-chips" data-id="{$t.id_product_variable|intval}">
                          <input type="hidden" class="btn-select-input input_for_url {$t.variable_name}" id="{$t.variable_name}"
                              name="variable_{$t.id_product_variable|intval}" value="{$t.default_option}"
                              data-default_value="{$t.default_option}" data-variable_id="{$t.id_variable}"
                              data-variable-name="{$t.variable_name}" data-formula-name="{$t.formula_name}"
                              data-url_variable="{str_replace(' ', '_', strtolower($t.variable_name))}" data-type="{$t.type}">
                          <select class="btn btn-select btn-select-value select_box_for_url" style="display:none"
                              data-variable_id="{$t.id_variable}" data-variable-name="{$t.variable_name}"
                              data-formula-name="{$t.formula_name}"
                              data-url_variable="{str_replace(' ', '_', strtolower($t.variable_name))}">
                            <option value="0">{l s='Kies een optie' mod='productpriceconfig'}</option>
                            {foreach $t.options_data as $option}
                              <option data-id_option="{$option.id}" data-price="{$option.price}"
                                  data-weight="{$option.weight}" data-thickness="{$option.thickness}"
                                  {if $option.id==$t.default_option} selected{/if}
                                  value="{$option.id}">{$option.name|escape:'html':'UTF-8'}</option>
                            {/foreach}
                          </select>
                          {foreach $t.options_data as $option}
                            <div class="ppc-chip{if $option.id == $t.default_option} on{/if}"
                                 data-id_option="{$option.id}"
                                 onclick="ppcSelectDropdownOption(this, '{$t.variable_name|escape:'javascript'}')">
                              {$option.name|escape:'html':'UTF-8'}
                            </div>
                          {/foreach}
                        </div>
                      </div>
                    {/if}
                  {/foreach}
                </div>
                {/if}

                {* ── VANGNET: elk veld met een onbekende formula_name ──
                     Gebruikt de ORIGINELE rendering (functioneel identiek aan
                     de oude configurator), zodat niets ooit onzichtbaar wordt.
                     Een veld is "bekend" (dus NIET in het vangnet) als het
                     ofwel exact in $known_fields staat, OFWEL een van de
                     $known_patterns als substring bevat (vangt drukkleuromslag,
                     papierbinnenwerk, uitvoeringomslag, etc. — zie boven). *}
                {assign var="has_unknown" value=0}
                {foreach $variables as $t}
                  {if !in_array($t.formula_name, explode(',', $known_fields))
                      && stripos($t.formula_name, 'orientation') === false
                      && stripos($t.formula_name, 'drukkleur') === false
                      && stripos($t.formula_name, 'papier') === false
                      && stripos($t.formula_name, 'uitvoering') === false
                      && $t.formula_name != 'omslag'
                      && $t.formula_name != 'binnenwerk'
                      && $t.formula_name != 'wireobindplaats'
                      && $t.formula_name != 'envelop'
                      && $t.formula_name != 'portokosten'
                      && $t.formula_name != 'hoogte'
                      && $t.formula_name != 'breedte'
                      && $t.formula_name != 'Kenmerk'
                      && $t.active != 0
                      && $t.type != 7}
                    {assign var="has_unknown" value=1}
                  {/if}
                {/foreach}

                {if $has_unknown}
                <div class="ppc-accord-toggle" id="ppcAccordToggleExtra" onclick="ppcToggleAccordExtra()">
                  <span><span class="ppc-accord-plus">+</span>{l s='Overige opties' mod='productpriceconfig'}</span>
                  <span class="ppc-accord-arrow">⌄</span>
                </div>
                <div class="ppc-accord-body" id="ppcAccordBodyExtra">
                {foreach $variables as $t}
                  {if !in_array($t.formula_name, explode(',', $known_fields))
                      && stripos($t.formula_name, 'orientation') === false
                      && stripos($t.formula_name, 'drukkleur') === false
                      && stripos($t.formula_name, 'papier') === false
                      && stripos($t.formula_name, 'uitvoering') === false
                      && $t.formula_name != 'omslag'
                      && $t.formula_name != 'binnenwerk'
                      && $t.formula_name != 'wireobindplaats'
                      && $t.formula_name != 'envelop'
                      && $t.formula_name != 'portokosten'
                      && $t.formula_name != 'hoogte'
                      && $t.formula_name != 'breedte'
                      && $t.formula_name != 'Kenmerk'
                      && $t.active != 0
                      && $t.type != 7}
                    {if $t.type == 2 && count($t.options_data)}
                      {* — Type 2 (dropdown) binnen het vangnet krijgt nu dezelfde
                           chips-weergave als de bekende velden (Boorgaten/Nieten/etc).
                           Generiek: werkt op ELK onbekend dropdown-veld, geen
                           hardcoded naam nodig. Velden met veel opties (zoals
                           Formaat/Papiersoort) hebben al hun eigen, herkende
                           component hierboven — dit pad is voor de overige,
                           meestal 2-3-optie velden zoals Tabs, Transparant,
                           Numbering, etc. — *}
                      <div>
                        <div class="ppc-label">{$t.name|escape:'html':'UTF-8'}
                          {if $t.id_variable_tooltip}<span class="ppc-tip" data-tip="{$t.tooltip_text|strip_tags|escape:'html':'UTF-8'}">i</span>{/if}
                        </div>
                        <div class="ppc-chips" data-id="{$t.id_product_variable|intval}">
                          <input type="hidden" class="btn-select-input input_for_url {$t.variable_name}" id="{$t.variable_name}"
                              name="variable_{$t.id_product_variable|intval}" value="{$t.default_option}"
                              data-default_value="{$t.default_option}" data-variable_id="{$t.id_variable}"
                              data-variable-name="{$t.variable_name}" data-formula-name="{$t.formula_name}"
                              data-url_variable="{str_replace(' ', '_', strtolower($t.variable_name))}" data-type="{$t.type}">
                          <select class="btn btn-select btn-select-value select_box_for_url" style="display:none"
                              data-variable_id="{$t.id_variable}" data-variable-name="{$t.variable_name}"
                              data-formula-name="{$t.formula_name}"
                              data-url_variable="{str_replace(' ', '_', strtolower($t.variable_name))}">
                            <option value="0">{l s='Kies een optie' mod='productpriceconfig'}</option>
                            {foreach $t.options_data as $option}
                              <option data-id_option="{$option.id}" data-price="{$option.price}"
                                  data-weight="{$option.weight}" data-thickness="{$option.thickness}"
                                  {if $option.id==$t.default_option} selected{/if}
                                  value="{$option.id}">{$option.name|escape:'html':'UTF-8'}</option>
                            {/foreach}
                          </select>
                          {foreach $t.options_data as $option}
                            <div class="ppc-chip{if $option.id == $t.default_option} on{/if}"
                                 data-id_option="{$option.id}"
                                 onclick="ppcSelectDropdownOption(this, '{$t.variable_name|escape:'javascript'}')">
                              {$option.name|escape:'html':'UTF-8'}
                            </div>
                          {/foreach}
                        </div>
                      </div>
                    {else}
                      {* — Overige types (1/4 stepper, 3 vast bedrag, 5 tekst, 6 readonly):
                           behouden de originele rendering, functioneel ongewijzigd. — *}
                      <div class="frm-section" data-id="{$t.id_product_variable|intval}">
                        <div class="left-frm-section-label">{$t.name|escape:'html':'UTF-8'}
                          {if $t.id_variable_tooltip}
                            <div class="tooltip"><i class="fa fa-info-circle" aria-hidden="true"></i>
                              <span class="tooltiptext">{$t.tooltip_text nofilter}</span>
                            </div>
                          {/if}
                        </div>
                        <div class="right-frm-section">
                          {if $t.type == 1 || $t.type == 4}
                            <div class="quantity-label">
                              <div class="input-group-prepend"><a class="minas qty-down" id="minus-btn"><i class="fa fa-minus"></i></a></div>
                              <input type="number" name="variable_{$t.id_product_variable|intval}"
                                  class="form-control form-control-sm number-area input-quantity-wanted {$t.variable_name} input_for_url"
                                  value="{$t.p_minimum}" min="{$t.p_minimum}" max="{$t.p_maximum}"
                                  data-formula-name="{$t.formula_name}" data-variable_id="{$t.id_variable}"
                                  data-variable-name="{$t.variable_name}"
                                  step="{if $t.multiplier != 0}{$t.multiplier}{else}{1}{/if}"
                                  data-url_variable="{str_replace(' ', '_', strtolower($t.variable_name))}" data-type="{$t.type}">
                              <div class="input-group-prepend"><a class="plus qty-up" id="plus-btn"><i class="fa fa-plus"></i></a></div>
                            </div>
                            <div class="custom_input_error"></div>
                          {elseif $t.type == 3}
                            <div class="fixed-label">
                              <div class="input-group-prepend"><button class="minas" id="minus-btn">{$t.fixed_price}</button></div>
                              <input type="hidden" name="variable_{$t.id_product_variable|intval}"
                                  class="{$t.variable_name} input_for_url" value="{$t.fixed_price}"
                                  data-variable_id="{$t.id_variable}" data-variable-name="{$t.variable_name}"
                                  data-formula-name="{$t.formula_name}"
                                  data-url_variable="{str_replace(' ', '_', strtolower($t.variable_name))}" data-type="{$t.type}">
                            </div>
                          {elseif $t.type == 5}
                            <div class="text-label">
                              <input type="text" name="variable_{$t.id_product_variable|intval}"
                                  class="form-control form-control-sm ppc-text-input {$t.variable_name} input_for_url" value=""
                                  data-variable_id="{$t.id_variable}" data-variable-name="{$t.variable_name}"
                                  data-formula-name="{$t.formula_name}"
                                  data-url_variable="{str_replace(' ', '_', strtolower($t.variable_name))}" data-type="{$t.type}">
                            </div>
                          {elseif $t.type == 6}
                            <div class="text-label">
                              {* id="thickness_input" is VERPLICHT — front.js's calculate_totals()
                                 zoekt specifiek op dit ID (niet op class) om de berekende
                                 rugdikte/total_thickness in te vullen. Zonder dit ID blijft
                                 het veld voor altijd leeg, ook al werkt de AJAX-call zelf
                                 prima (geverifieerd tegen het origineel, 25-6-2026). *}
                              <input type="text" id="thickness_input" readonly name="variable_{$t.id_product_variable|intval}"
                                  class="form-control form-control-sm {$t.variable_name} input_for_url" value=""
                                  data-variable_id="{$t.id_variable}" data-variable-name="{$t.variable_name}"
                                  data-formula-name="{$t.formula_name}"
                                  data-url_variable="{str_replace(' ', '_', strtolower($t.variable_name))}" data-type="{$t.type}">
                            </div>
                          {/if}
                        </div>
                      </div>
                    {/if}
                  {/if}
                {/foreach}
                </div>{* sluit ppc-accord-body Extra *}
                {/if}

              </div>
              <div class="ppc-step-nav-btns">
                <button type="button" class="ppc-btn-back" onclick="ppcGoStep(1)">← {l s='Terug' mod='productpriceconfig'}</button>
                <button type="button" class="ppc-btn-next" onclick="ppcGoStep(3)">{l s='Volgende: Oplage & prijs' mod='productpriceconfig'} →</button>
              </div>
            </div>

            {* ═══════════════ STAP 3: Oplage & prijs ═══════════════ *}
            <div class="ppc-panel" id="ppcPanel3">
              <div class="ppc-body">

                {* — Oplage: stepper — *}
                {foreach $variables as $t}
                  {if $t.formula_name == 'oplage' && $t.active != 0 && ($t.type == 1 || $t.type == 4)}
                    <div>
                      <div class="ppc-label">{$t.name|escape:'html':'UTF-8'}
                        {if $t.id_variable_tooltip}<span class="ppc-tip" data-tip="{$t.tooltip_text|strip_tags|escape:'html':'UTF-8'}">i</span>{/if}
                      </div>
                      <div class="ppc-qty quantity-label">
                        <button type="button" class="ppc-q-btn minas qty-down">−</button>
                        <input type="number" id="qty_input" class="ppc-q-inp form-control form-control-sm number-area input-quantity-wanted {$t.variable_name} input_for_url"
                            name="variable_{$t.id_product_variable|intval}"
                            value="{$t.p_minimum}" min="{$t.p_minimum}" max="{$t.p_maximum}"
                            data-formula-name="{$t.formula_name}" data-variable_id="{$t.id_variable}"
                            data-variable-name="{$t.variable_name}"
                            step="{if $t.multiplier != 0}{$t.multiplier}{else}{1}{/if}"
                            data-url_variable="{str_replace(' ', '_', strtolower($t.variable_name))}" data-type="{$t.type}">
                        <button type="button" class="ppc-q-btn plus qty-up">+</button>
                      </div>
                      <div class="custom_input_error"></div>
                    </div>
                    <div class="ppc-divider"></div>
                  {/if}
                {/foreach}

                {* — Document controle: render als kaarten — *}
                {foreach $variables as $t}
                  {if $t.formula_name == 'documentcontrole' && $t.active != 0 && $t.type == 2 && count($t.options_data)}
                    <div>
                      <div class="ppc-label">{$t.name|escape:'html':'UTF-8'}
                        {if $t.id_variable_tooltip}<span class="ppc-tip" data-tip="{$t.tooltip_text|strip_tags|escape:'html':'UTF-8'}">i</span>{/if}
                      </div>
                      <div style="display:flex;flex-direction:column;gap:7px" data-id="{$t.id_product_variable|intval}">
                        <input type="hidden" class="btn-select-input input_for_url {$t.variable_name}" id="{$t.variable_name}"
                            name="variable_{$t.id_product_variable|intval}" value="{$t.default_option}"
                            data-default_value="{$t.default_option}" data-variable_id="{$t.id_variable}"
                            data-variable-name="{$t.variable_name}" data-formula-name="{$t.formula_name}"
                            data-url_variable="{str_replace(' ', '_', strtolower($t.variable_name))}" data-type="{$t.type}">
                        <select class="btn btn-select btn-select-value select_box_for_url" style="display:none"
                            data-variable_id="{$t.id_variable}" data-variable-name="{$t.variable_name}"
                            data-formula-name="{$t.formula_name}"
                            data-url_variable="{str_replace(' ', '_', strtolower($t.variable_name))}">
                          <option value="0">{l s='Kies een optie' mod='productpriceconfig'}</option>
                          {foreach $t.options_data as $option}
                            <option data-id_option="{$option.id}" data-price="{$option.price}"
                                data-weight="{$option.weight}" data-thickness="{$option.thickness}"
                                {if $option.id==$t.default_option} selected{/if}
                                value="{$option.id}">{$option.name|escape:'html':'UTF-8'}</option>
                          {/foreach}
                        </select>
                        {foreach $t.options_data as $option}
                          <div class="ppc-doc-card{if $option.id == $t.default_option} on{/if}"
                               data-id_option="{$option.id}"
                               onclick="ppcSelectDropdownOption(this, '{$t.variable_name|escape:'javascript'}')">
                            <div class="ppc-doc-top">
                              <span class="ppc-doc-name">{$option.name|escape:'html':'UTF-8'}</span>
                            </div>
                          </div>
                        {/foreach}
                      </div>
                    </div>
                    <div class="ppc-divider"></div>
                  {/if}
                {/foreach}

                {* — Postverzending (direct mail): chips in stap 3 — *}
                {foreach $variables as $t}
                  {if $t.formula_name == 'portokosten' && $t.active != 0 && $t.type == 2 && count($t.options_data)}
                    <div class="ppc-divider"></div>
                    <div>
                      <div class="ppc-label">{$t.name|escape:'html':'UTF-8'}
                        {if $t.id_variable_tooltip}<span class="ppc-tip" data-tip="{$t.tooltip_text|strip_tags|escape:'html':'UTF-8'}">i</span>{/if}
                      </div>
                      <div class="ppc-chips" data-id="{$t.id_product_variable|intval}">
                        <input type="hidden" class="btn-select-input input_for_url {$t.variable_name}" id="{$t.variable_name}"
                            name="variable_{$t.id_product_variable|intval}" value="{$t.default_option}"
                            data-default_value="{$t.default_option}" data-variable_id="{$t.id_variable}"
                            data-variable-name="{$t.variable_name}" data-formula-name="{$t.formula_name}"
                            data-url_variable="{str_replace(' ', '_', strtolower($t.variable_name))}" data-type="{$t.type}">
                        <select class="btn btn-select btn-select-value select_box_for_url" style="display:none"
                            data-variable_id="{$t.id_variable}" data-variable-name="{$t.variable_name}"
                            data-formula-name="{$t.formula_name}"
                            data-url_variable="{str_replace(' ', '_', strtolower($t.variable_name))}">
                          <option value="0">{l s='Kies een optie' mod='productpriceconfig'}</option>
                          {foreach $t.options_data as $option}
                            <option data-id_option="{$option.id}" data-price="{$option.price}"
                                data-weight="{$option.weight}" data-thickness="{$option.thickness}"
                                {if $option.id==$t.default_option} selected{/if}
                                value="{$option.id}">{$option.name|escape:'html':'UTF-8'}</option>
                          {/foreach}
                        </select>
                        {foreach $t.options_data as $option}
                          <div class="ppc-chip{if $option.id == $t.default_option} on{/if}"
                               data-id_option="{$option.id}"
                               onclick="ppcSelectDropdownOption(this, '{$t.variable_name|escape:'javascript'}')">
                            {$option.name|escape:'html':'UTF-8'}
                          </div>
                        {/foreach}
                      </div>
                    </div>
                  {/if}
                {/foreach}

                {* — Kenmerk / bestandsnaam: tekstveld —
                     formula_name is 'referentie' (Losbladig) OF 'Kenmerk' met hoofdletter (Direct mail). *}
                {foreach $variables as $t}
                  {if ($t.formula_name == 'referentie' || $t.formula_name == 'Kenmerk') && $t.active != 0 && $t.type == 5}
                    <div>
                      <div class="ppc-label">{$t.name|escape:'html':'UTF-8'}
                        {if $t.id_variable_tooltip}<span class="ppc-tip" data-tip="{$t.tooltip_text|strip_tags|escape:'html':'UTF-8'}">i</span>{/if}
                      </div>
                      <input type="text" class="ppc-text-input {$t.variable_name} input_for_url"
                          name="variable_{$t.id_product_variable|intval}" value=""
                          data-variable_id="{$t.id_variable}" data-variable-name="{$t.variable_name}"
                          data-formula-name="{$t.formula_name}"
                          data-url_variable="{str_replace(' ', '_', strtolower($t.variable_name))}" data-type="{$t.type}">
                    </div>
                  {/if}
                {/foreach}

                {* — Upload method: render als chips (hoort bij de bestelafronding,
                     niet bij de drukwerk-configuratie zelf — daarom bewust in
                     stap 3, los van de Formaat/Papier-classificatie hierboven). — *}
                {foreach $variables as $t}
                  {if $t.formula_name == 'uploadmethod' && $t.active != 0 && $t.type == 2 && count($t.options_data)}
                    <div class="ppc-divider"></div>
                    <div>
                      <div class="ppc-label">{$t.name|escape:'html':'UTF-8'}
                        {if $t.id_variable_tooltip}<span class="ppc-tip" data-tip="{$t.tooltip_text|strip_tags|escape:'html':'UTF-8'}">i</span>{/if}
                      </div>
                      <div class="ppc-chips" data-id="{$t.id_product_variable|intval}">
                        <input type="hidden" class="btn-select-input input_for_url {$t.variable_name}" id="{$t.variable_name}"
                            name="variable_{$t.id_product_variable|intval}" value="{$t.default_option}"
                            data-default_value="{$t.default_option}" data-variable_id="{$t.id_variable}"
                            data-variable-name="{$t.variable_name}" data-formula-name="{$t.formula_name}"
                            data-url_variable="{str_replace(' ', '_', strtolower($t.variable_name))}" data-type="{$t.type}">
                        <select class="btn btn-select btn-select-value select_box_for_url" style="display:none"
                            data-variable_id="{$t.id_variable}" data-variable-name="{$t.variable_name}"
                            data-formula-name="{$t.formula_name}"
                            data-url_variable="{str_replace(' ', '_', strtolower($t.variable_name))}">
                          <option value="0">{l s='Kies een optie' mod='productpriceconfig'}</option>
                          {foreach $t.options_data as $option}
                            <option data-id_option="{$option.id}" data-price="{$option.price}"
                                data-weight="{$option.weight}" data-thickness="{$option.thickness}"
                                {if $option.id==$t.default_option} selected{/if}
                                value="{$option.id}">{$option.name|escape:'html':'UTF-8'}</option>
                          {/foreach}
                        </select>
                        {foreach $t.options_data as $option}
                          <div class="ppc-chip{if $option.id == $t.default_option} on{/if}"
                               data-id_option="{$option.id}"
                               onclick="ppcSelectDropdownOption(this, '{$t.variable_name|escape:'javascript'}')">
                            {$option.name|escape:'html':'UTF-8'}
                          </div>
                        {/foreach}
                      </div>
                    </div>
                  {/if}
                {/foreach}

              </div>

              {* ── Prijssectie: EXACT dezelfde classes als origineel (.price_wot, .tax, .total),
                   want calculate_totals() in front.js schrijft hier rechtstreeks naartoe. ── *}
              <div class="ppc-price-section">
                <div class="frm-section single_unit_prices">
                  <div class="single_unit_price">
                    <div class="left-frm-section unit_price_label">{l s='Price divided by edition' mod='productpriceconfig'} <span class="single_unit_price_span">: 0 / 0</span></div>
                    <div class="right-frm-section-price unit_price_right single_unit_price_right">€ 0</div>
                  </div>
                </div>
                <div class="ppc-price-row">
                  <span>{l s='Total Package' mod='productpriceconfig'}</span>
                  <span class="package_count">1</span>
                </div>
                <div class="ppc-price-row">
                  <span>{l s='excl. BTW' mod='productpriceconfig'}</span>
                  <span class="price_wot">$0.0000</span>
                </div>
                <div class="ppc-price-row">
                  <span>{l s='BTW' mod='productpriceconfig'} ({$product.tax_name})</span>
                  <span class="tax">$0.0000</span>
                </div>
                <div class="ppc-price-row total">
                  <span>{l s='incl. BTW' mod='productpriceconfig'}</span>
                  <span class="total">$0.0000</span>
                </div>

                {* The Add To Quote Cart button lives in create.tpl (#btn-add-to-quote).
                   The configurator's own CTA is hidden on the Offer page. *}
                <button type="button" class="ppc-cta-main" style="display:none"></button>
                <span class="text-danger alert_message_text" id="offer-config-error-inline" style="display:none"></span>
              </div>

              <div class="ppc-step-nav-btns">
                <button type="button" class="ppc-btn-back" onclick="ppcGoStep(2)">← {l s='Terug' mod='productpriceconfig'}</button>
              </div>
            </div>

            {* ── Verborgen "calculate_totals" trigger: front.js verwacht dat dit element
                 bestaat (zie $table.on("click", ".calculate_totals", ...)). We roepen 'm
                 zelf programmatisch aan in ppc-bridge.js i.p.v.'m zichtbaar te tonen. ── *}
            <div class="calculate-price" style="display:none"><a href="#" class="calculate_totals"><span>{l s='prijs berekenen' mod='productpriceconfig'}</span></a></div>

        </form>

        {$trusted_badge nofilter}
        {$html_content nofilter}
    </div>
</div>

<div class="modal fade" id="alert_message" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title pull-left" id="exampleModalLabel">{l s='Warning' mod='productpriceconfig'}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body" id="message"></div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button></div>
        </div>
    </div>
</div>

<script>
  var errorSpineThicknessZero = '{l s="Spine thickness cannot be 0" mod='productpriceconfig'}';
  var errorValueNotValid = '{l s="Value is not valid." mod='productpriceconfig'}';
  var errorMinNumber = '{l s="Minimum number is %min%" mod='productpriceconfig'}';
  var errorMaxNumber = '{l s="Maximum number is %max%" mod='productpriceconfig'}';
  var errorAmountMultiple = '{l s="Amount must be a multiple of %step%" mod='productpriceconfig'}';
  var kd_ajax_path = '{$kd_ajax_path}';
  var ppc_validate_url = '{$ppc_validate_url}';
</script>

{$js nofilter}

<script src="/themes/theme_ecolife_marketplace2/modules/productpriceconfig/views/js/front.js"></script>

{* ── Nieuwe, LOSSE JS-bestanden. front.js blijft volledig ongewijzigd. ── *}
<script src="/themes/theme_ecolife_marketplace2/modules/productpriceconfig/views/js/ppc-bridge.js"></script>
<script src="/themes/theme_ecolife_marketplace2/modules/productpriceconfig/views/js/ppc-paper.js"></script>

<script>
    $(document).ready(function() {
        rulesForbannedComb();
        $(document).on('change', '.btn-select-value, .input_for_url', function() {
            rulesForbannedComb();
        });

        
        $(document).on(
            "click",
            ".quantity-label .qty-up, .quantity-label .qty-down",
            function(event) {
                var $btn = $(event.target);
                var $td = $btn.closest(".quantity-label");
                var $input = $td.find(".input-quantity-wanted");
                //console.log($input.attr("data-variable_id"));

                var qty = parseInt($input.val()) || 0;
                var minQty = "";
                var maxQty = "";
                if ($input.attr("max")) {
                    maxQty = parseInt($input.attr("max"));
                }
                if ($input.attr("min")) {
                    minQty = parseInt($input.attr("min"));
                }
                var multiplier = parseInt($input.attr("step"));

                var multiplyByMinQty = 1 === $input.data("multiply-by-min-qty");

                var up = $btn.hasClass("qty-up") || $btn.parent().hasClass("qty-up");
                $total_qty = 0;
                if (up) {
                    if (maxQty != "" && minQty != "") {
                        if (qty < minQty) {
                            qty = minQty;
                            $total_qty = $total_qty;
                        } else if (qty > maxQty) {
                            qty = maxQty;
                            $(".custom_input_error").html("");
                        } else if (qty < maxQty) {
                            if (multiplyByMinQty && minQty > 1) {
                                if (qty + minQty <= maxQty) {
                                    qty += minQty;
                                }
                            } else {
                                qty += multiplier;
                            }
                            $total_qty = $total_qty + multiplier;
                            $(".custom_input_error").html("");
                        }
                    } else {
                        qty++;
                    }
                } else {
                    if (maxQty != "" && minQty != "") {
                        if (qty < minQty) {
                            qty = minQty;
                            $total_qty = $total_qty;
                        } else if (qty > maxQty) {
                            qty = maxQty;
                            $(".custom_input_error").html("");
                        } else if (qty <= minQty) {
                            qty = minQty;
                            $(".custom_input_error").html("");
                        } else if (qty > 0) {
                            if (multiplyByMinQty && minQty > 1) {
                                if (qty - minQty >= 0) {
                                    qty -= minQty;
                                }
                            } else {
                                qty -= multiplier;
                            }
                            $total_qty = $total_qty - multiplier;
                            $(".custom_input_error").html("");
                        }
                    } else {
                        qty--;
                    }
                }

                //console.log(qty);

                if ($total_qty < 0) {
                    $total_qty = 0;
                }

                $(".product-quantity .total_qty").text($total_qty);

                $input.prop("value", qty);
                $input.change();
                conditionsForBanedComb();
            }
        );
    })
</script>