<div class="panel offer-admin-list">
    <div class="panel-heading">
        <i class="icon icon-list"></i> {l s='Quotations' mod='productpriceconfig'}
        <span class="badge badge-info pull-right">{$total}</span>
    </div>

    <div class="offer-filters" style="margin-bottom: 15px;">
        <div class="row">
            <div class="col-md-4">
                <input type="text" id="offer-search" class="form-control" placeholder="{l s='Search by reference, name, email...' mod='productpriceconfig'}" value="{$filters.search}" />
            </div>
            <div class="col-md-3">
                <select id="offer-status-filter" class="form-control">
                    <option value="">{l s='All Statuses' mod='productpriceconfig'}</option>
                    {foreach from=$statuses key=value item=label}
                        <option value="{$value}" {if $filters.status == $value}selected{/if}>{$label}</option>
                    {/foreach}
                </select>
            </div>
            <div class="col-md-2">
                <input type="text" id="offer-date-from" class="form-control datepicker" placeholder="{l s='From date' mod='productpriceconfig'}" value="{$filters.date_from}" />
            </div>
            <div class="col-md-2">
                <input type="text" id="offer-date-to" class="form-control datepicker" placeholder="{l s='To date' mod='productpriceconfig'}" value="{$filters.date_to}" />
            </div>
            <div class="col-md-1">
                <button type="button" id="btn-filter-offers" class="btn btn-primary btn-block">
                    <i class="icon icon-search"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table offer-list-table">
            <thead>
                <tr>
                    <th>{l s='ID' mod='productpriceconfig'}</th>
                    <th>{l s='Reference' mod='productpriceconfig'}</th>
                    <th>{l s='Title' mod='productpriceconfig'}</th>
                    <th>{l s='Customer' mod='productpriceconfig'}</th>
                    <th>{l s='Products' mod='productpriceconfig'}</th>
                    <th>{l s='Status' mod='productpriceconfig'}</th>
                    <th>{l s='Expiry' mod='productpriceconfig'}</th>
                    <th>{l s='Created' mod='productpriceconfig'}</th>
                    <th>{l s='Actions' mod='productpriceconfig'}</th>
                </tr>
            </thead>
            <tbody>
                {foreach from=$offers item=offer}
                    <tr class="{if $offer.is_expired}warning{/if}{if $offer.all_accepted}success{/if}">
                        <td>{$offer.id_offer}</td>
                        <td>
                            <a href="{$offer.detail_url}"><strong>{$offer.reference}</strong></a>
                        </td>
                        <td>{$offer.title|default:'-'}</td>
                        <td>
                            {$offer.customer_name}<br/>
                            <small class="text-muted">{$offer.customer_email}</small>
                        </td>
                        <td>
                            {if $offer.thumbnails}
                                {foreach from=$offer.thumbnails item=thumb}
                                    <img src="{$thumb}" alt="" style="width: 30px; height: 30px; object-fit: cover; border-radius: 3px; margin-right: 2px;" />
                                {/foreach}
                            {/if}
                            <span class="badge">{$offer.product_count}</span>
                        </td>
                        <td>
                            <span class="label label-{if $offer.status == 'accepted'}success{elseif $offer.status == 'rejected' || $offer.status == 'cancelled' || $offer.status == 'expired'}danger{elseif $offer.status == 'price_offered'}warning{else}info{/if}">
                                {$offer.status_label}
                            </span>
                        </td>
                        <td>
                            {if $offer.expire_date}
                                {$offer.expire_date}
                                {if $offer.is_expired}<span class="label label-danger">{l s='Expired' mod='productpriceconfig'}</span>{/if}
                            {else}
                                -
                            {/if}
                        </td>
                        <td><small>{$offer.date_add}</small></td>
                        <td>
                            <a href="{$offer.detail_url}" class="btn btn-default btn-sm" title="{l s='View' mod='productpriceconfig'}">
                                <i class="icon icon-eye-open"></i>
                            </a>
                            <button type="button" class="btn btn-danger btn-sm btn-delete-offer" data-id-offer="{$offer.id_offer}" title="{l s='Delete' mod='productpriceconfig'}">
                                <i class="icon icon-trash"></i>
                            </button>
                        </td>
                    </tr>
                {foreachelse}
                    <tr>
                        <td colspan="9" class="text-center text-muted">{l s='No quotations found.' mod='productpriceconfig'}</td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>

    {* Pagination *}
    {if $total_pages > 1}
        <div class="pagination-container">
            <ul class="pagination">
                {for $p=1 to $total_pages}
                    <li {if $p == $page}class="active"{/if}>
                        <a href="{$list_url}&page={$p}&search={$filters.search}&status={$filters.status}">{$p}</a>
                    </li>
                {/for}
            </ul>
        </div>
    {/if}

    <input type="hidden" id="ajax-url" value="{$ajax_url}" />
    <input type="hidden" id="list-url" value="{$list_url}" />
</div>
