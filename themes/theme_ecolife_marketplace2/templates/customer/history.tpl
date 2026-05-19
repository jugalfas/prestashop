{**
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2018 PrestaShop SA
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}
{extends file='customer/page.tpl'}

{block name='page_title'}
  {l s='Order history' d='Shop.Theme.Customeraccount'}
{/block}

{block name='page_content'}
  <h6>{l s='Here are the orders you\'ve placed since your account was created.' d='Shop.Theme.Customeraccount'}</h6>

  {if $orders}
    <table class="table table-striped table-bordered table-labeled hidden-sm-down">
      <thead class="thead-default">
        <tr>
          <th>{l s='Order ID' d='Shop.Theme.Checkout'}</th>
          <th>{l s='Order reference' d='Shop.Theme.Checkout'}</th>
          <th>{l s='Date' d='Shop.Theme.Checkout'}</th>
          <th>{l s='Total price' d='Shop.Theme.Checkout'}</th>
          <th class="hidden-md-down">{l s='Payment' d='Shop.Theme.Checkout'}</th>
          <th class="hidden-md-down">{l s='Status' d='Shop.Theme.Checkout'}</th>
          <th>{l s='Invoice' d='Shop.Theme.Checkout'}</th>
          <th>&nbsp;</th>
        </tr>
      </thead>
      <tbody>
        {foreach from=$orders item=order}
          <tr>
            <th scope="row">{$order.details.id}</th>
            <th scope="row">{$order.details.reference}</th>
            <td>{$order.details.order_date}</td>
            <td class="text-xs-right">{$order.totals.total.value}</td>
            <td class="hidden-md-down">{$order.details.payment}</td>
            <td>
              <span
                class="label label-pill {$order.history.current.contrast}"
                style="background-color:{$order.history.current.color}"
              >
                {$order.history.current.ostate_name}
              </span>
            </td>
            <td class="text-sm-center hidden-md-down">
              {if $order.details.invoice_url}
                <a href="{$order.details.invoice_url}"><i class="material-icons">&#xE415;</i></a>
              {else}
                -
              {/if}
            </td>
            <td class="text-sm-center order-actions">
              <a href="{$order.details.details_url}" data-link-action="view-order-details">
                {l s='Details' d='Shop.Theme.Customeraccount'}
              </a>
              {if $order.details.reorder_url}
                <a href="#"
                   class="js-reorder-trigger"
                   data-reorder-url="{$order.details.reorder_url}"
                   data-toggle="modal"
                   data-target="#reorderWarningModal">
                  {l s='Reorder' d='Shop.Theme.Actions'}
                </a>
              {/if}
            </td>
          </tr>
        {/foreach}
      </tbody>
    </table>

    <div class="orders hidden-md-up">
      {foreach from=$orders item=order}
        <div class="order">
          <div class="row">
            <div class="col-xs-10">
              <a href="{$order.details.details_url}"><h3>{$order.details.reference}</h3></a>
              <div class="date">{$order.details.order_date}</div>
              <div class="total">{$order.totals.total.value}</div>
              <div class="status">
                <span
                  class="label label-pill {$order.history.current.contrast}"
                  style="background-color:{$order.history.current.color}"
                >
                  {$order.history.current.ostate_name}
                </span>
              </div>
            </div>
            <div class="col-xs-2 text-xs-right">
                <div>
                  <a href="{$order.details.details_url}" data-link-action="view-order-details" title="{l s='Details' d='Shop.Theme.Customeraccount'}">
                    <i class="material-icons">&#xE8B6;</i>
                  </a>
                </div>
                {if $order.details.reorder_url}
                  <div>
                    <a href="#"
                       class="js-reorder-trigger"
                       data-reorder-url="{$order.details.reorder_url}"
                       data-toggle="modal"
                       data-target="#reorderWarningModal"
                       title="{l s='Reorder' d='Shop.Theme.Actions'}">
                      <i class="material-icons">&#xE863;</i>
                    </a>
                  </div>
                {/if}
            </div>
          </div>
        </div>
      {/foreach}
    </div>

    {* --- Reorder warning modal: informs the customer that print files are not stored --- *}
    <div class="modal fade"
         id="reorderWarningModal"
         tabindex="-1"
         role="dialog"
         aria-labelledby="reorderWarningModalLabel"
         aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="reorderWarningModalLabel">
              <i class="material-icons" style="vertical-align: middle; color: #f0ad4e;">&#xE002;</i>
              {l s='Let op: drukbestanden opnieuw uploaden' d='Shop.Theme.Customeraccount'}
            </h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="{l s='Close' d='Shop.Theme.Global'}">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <p>
              {l s='Wij bewaren je drukbestanden na verwerking van je bestelling niet op onze servers.' d='Shop.Theme.Customeraccount'}
            </p>
            <p>
              <strong>{l s='Dit betekent dat je bij het opnieuw bestellen de drukbestanden opnieuw moet uploaden.' d='Shop.Theme.Customeraccount'}</strong>
            </p>
            <p class="text-muted small mb-0">
              {l s='De productinstellingen en het aantal worden automatisch overgenomen — alleen het uploaden van de bestanden is nog nodig.' d='Shop.Theme.Customeraccount'}
            </p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">
              {l s='Annuleren' d='Shop.Theme.Actions'}
            </button>
            <a href="#" id="reorderConfirmBtn" class="btn btn-primary">
              {l s='Doorgaan en bestanden uploaden' d='Shop.Theme.Actions'}
            </a>
          </div>
        </div>
      </div>
    </div>

    <script>
      (function () {
        document.addEventListener('DOMContentLoaded', function () {
          var confirmBtn = document.getElementById('reorderConfirmBtn');
          var triggers = document.querySelectorAll('.js-reorder-trigger');

          triggers.forEach(function (trigger) {
            trigger.addEventListener('click', function (e) {
              e.preventDefault();
              var url = this.getAttribute('data-reorder-url');
              if (url && confirmBtn) {
                confirmBtn.setAttribute('href', url);
              }
            });
          });
        });
      })();
    </script>

  {/if}
{/block}
