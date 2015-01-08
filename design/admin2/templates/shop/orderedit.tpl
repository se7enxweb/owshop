<form action={concat("/owshop/orderedit")|ezurl} method="post" name="OrderEdit">

{if eq($error,'')|not()}
<div class="message-error">
    <h2>{$error}</h2>
</div>
{/if}

<div class="context-block">
    {* DESIGN: Header START *}<div class="box-header"><div class="box-ml">

    <h1 class="context-title">{'Order #%order_id'|i18n( 'design/admin/shop/orderedit',,
        hash( '%order_id', $order.order_nr ) )}</h1>

    <div class="box-ml">
        <b>{'Status'|i18n( 'design/admin/shop/orders' )} : </b>

        <select name="StatusOrder" class="span2">
            {foreach $status_list as $status}
                <option value="{$status.status_id}" {if eq($status.status_id,$order.status_id)}selected{/if}>{$status.name}</option>
            {/foreach}
        </select>
        <input type="submit" class="button" name="SaveOrderStatusButton" value="{'Change the status'|i18n( 'design/admin/shop/orderedit' )}">
    </div>
    {* DESIGN: Mainline *}<div class="header-mainline"></div>

    {* DESIGN: Header END *}</div></div>
</div>

<div class="context-block">
    {* DESIGN: Header START *}<div class="box-header"><div class="box-ml">

    <h1 class="context-title">{'Delivery Address'|i18n( 'design/admin/shop/orderedit',,
        hash( '%order_id', $order.order_nr ) )}</h1>

    <div class="box-ml">
        <table class="table" width="100%" cellspacing="0" cellpadding="0" border="0">
        {foreach $order.account_information['field_list']['all'] as $field}
            <tr>
                <td>
                    {$order.account_information['field_configuration'][$field]['name']}:{if $order.account_information['field_configuration'][$field]['required']}*{/if}
                </td>
                <td>
                {switch match=$order.account_information['field_configuration'][$field]['type']}
                {case match='country_list'}
                    {include uri='design:shop/country/edit.tpl' select_name=concat('DeliveryAddress_', $field) select_size=1 current_val=$order.account_information['account_info'][$field]}
                {/case}
                {case}
                    <input class="span3" type="text" name="DeliveryAddress_{$field}" size="20" value="{$order.account_information['account_info'][$field]|wash}" />
                {/case}
                {/switch}
                </td>
            </div>
        {/foreach}
            <tr>
                <td>{"Comment"|i18n("design/standard/shop")} : </td>
                <td><textarea name="Comment" cols="80" rows="5" class="span3" >{$order.account_information['account_info']['comment']|wash}</textarea></td>
            </tr>
        </table>

            <input type="reset" class="button" value="{'Cancel'|i18n( 'design/admin/shop/orderedit' )}">
            <input type="submit" class="button" name="SaveOrderUserInfoButton" value="{'Validate address'|i18n( 'design/admin/shop/orderedit' )}">
    </div>
    {* DESIGN: Mainline *}<div class="header-mainline"></div>

    {* DESIGN: Header END *}</div></div>
</div>

<div class="context-block">
    {* DESIGN: Header START *}<div class="box-header"><div class="box-ml">
            {* DESIGN: Header END *}</div></div>
        {* DESIGN: Conten START *}<div class="box-ml"><div class="box-mr"><div class="box-content">

                    <div class="context-attributes">
                        {*shop_account_view_gui view=html order=$order*}
                        {def $currency = fetch( 'owshop', 'currency', hash( 'code', $order.productcollection.currency_code ) )
                        $locale = false()
                        $symbol = false()}
                        {if $currency}
                            {set locale = $currency.locale
                            symbol = $currency.symbol}
                        {/if}

                        <b>{'Product items'|i18n( 'design/admin/shop/orderview' )}</b>
                        <table class="list" width="100%" cellspacing="0" cellpadding="0" border="0">
                            <tr>
                                <th></th>
                                <th>{'Product'|i18n( 'design/admin/shop/orderview' )}</th>
                                <th>{'Count'|i18n( 'design/admin/shop/orderview' )}</th>
                                <th>{'VAT'|i18n( 'design/admin/shop/orderview' )}</th>
                                <th>{'Price ex. VAT'|i18n( 'design/admin/shop/orderview' )}</th>
                                <th>{'Price inc. VAT'|i18n( 'design/admin/shop/orderview' )}</th>
                                <th>{'Discount'|i18n( 'design/admin/shop/orderview' )}</th>
                                <th>{'Total price ex. VAT'|i18n( 'design/admin/shop/orderview' )}</th>
                                <th>{'Total price inc. VAT'|i18n( 'design/admin/shop/orderview' )}</th>
                            </tr>
                            {section name=ProductItem loop=$order.product_items show=$order.product_items}
                                <tr>
                                    <td><input type="checkbox" name="ProductOrderArray[]" value="{$ProductItem:item.id}"></td>
                                    {if and( $ProductItem:item.item_object.contentobject, $ProductItem:item.item_object.contentobject.main_node )}
                                        {let node_url=$ProductItem:item.item_object.contentobject.main_node.url_alias}
                                            <td>{$ProductItem:item.item_object.contentobject.class_identifier|class_icon( small,$ProductItem:item.item_object.contentobject.class_name )}&nbsp;{if $:node_url}<a href={$:node_url|ezurl}>{/if}{$ProductItem:item.item_object.contentobject.name|wash}{if $:node_url}</a>{/if}</td>
                                        {/let}
                                    {else}
                                        <td>{false()|class_icon( small )}&nbsp;{$ProductItem:item.item_object.name|wash}</td>
                                    {/if}
                                    <td class="number" align="right"><input type="text" value="{$ProductItem:item.item_count}" name="CountProduct[{$ProductItem:item.id}]" class="span1"> </td>
                                    <td class="number" align="right">{$ProductItem:item.vat_value}&nbsp;%</td>
                                    <td class="number" align="right">{$ProductItem:item.price_ex_vat|l10n( 'currency', $locale, $symbol )}</td>
                                    <td class="number" align="right">{$ProductItem:item.price_inc_vat|l10n( 'currency', $locale, $symbol )}</td>
                                    <td class="number" align="right">{$ProductItem:item.discount_percent}&nbsp;%</td>
                                    <td class="number" align="right">{$ProductItem:item.total_price_ex_vat|l10n( 'currency', $locale, $symbol )}</td>
                                    <td class="number" align="right">{$ProductItem:item.total_price_inc_vat|l10n( 'currency', $locale, $symbol )}</td>
                                </tr>
                                {section show=$ProductItem:item.item_object.option_list}
                                    <tr>
                                        <td colspan='3'>
                                            <table border="0">
                                                <tr>
                                                    <td colspan='3'>{'Selected options'|i18n( 'design/admin/shop/orderview' )}</td>
                                                </tr>
                                                {section var=Options loop=$ProductItem:item.item_object.option_list}
                                                    <tr>
                                                        <td>{$:Options.item.name|wash}</td>
                                                        <td>{$:Options.item.value}</td>
                                                        <td class="number" align="right">{$:Options.item.price|l10n( 'currency', $locale, $symbol )}</td>
                                                    </tr>
                                                {/section}
                                            </table>
                                        </td>
                                        <td colspan='5'>
                                        </td>
                                    </tr>
                                {/section}
                            {/section}
                        </table>

                        <b>{'Order summary'|i18n( 'design/admin/shop/orderview' )}:</b><br />
                        <table class="list" cellspacing="0">
                            <tr>
                                <td>{'Subtotal of items'|i18n( 'design/admin/shop/orderview' )}:</td>
                                <td class="number" align="right">{$order.product_total_ex_vat|l10n( 'currency', $locale, $symbol )}</td>
                                <td class="number" align="right">{$order.product_total_inc_vat|l10n( 'currency', $locale, $symbol )}</td>
                            </tr>

                            {section name=OrderItem loop=$order.order_items show=$order.order_items}
                                <tr>
                                    <td>{$OrderItem:item.description}:</td>
                                    <td class="number" align="right">{$OrderItem:item.price_ex_vat|l10n( 'currency', $locale, $symbol )}</td>
                                    <td class="number" align="right">{$OrderItem:item.price_inc_vat|l10n( 'currency', $locale, $symbol )}</td>
                                </tr>
                            {/section}
                            <tr>
                                <td><b>{'Order total'|i18n( 'design/admin/shop/orderview' )}</b></td>
                                <td class="number" align="right"><b>{$order.total_ex_vat|l10n( 'currency', $locale, $symbol )}</b></td>
                                <td class="number" align="right"><b>{$order.total_inc_vat|l10n( 'currency', $locale, $symbol )}</b></td>
                            </tr>
                        </table>

                    </div>

                    {* DESIGN: Content END *}</div></div></div>

        <div class="controlbar">
            {* DESIGN: Control bar START *}<div class="box-bc"><div class="box-ml">
                    <div class="block">
                        <input type="hidden" name="OrderID" value="{$order.id}" />
                        <div  class="left">
                            <input class="button" type="submit" name="RemoveProductButton" value="{'Remove the selection'|i18n( 'design/admin/shop/orderedit' )}" onclick="confirmRemoveProduct()"/>

                            <input class="button" type="submit" name="BrowseAddProductButton" value="{'Add product'|i18n( 'design/admin/shop/orderedit' )}" />
                            <input class="button" type="submit" name="UpdateQtButton" value="{'Update product quantity'|i18n( 'design/admin/shop/orderedit' )}" />
                        </div>
                        <div class="right">
                            <input class="button" type="submit" name="saveAndExitButton" value="{'save and exit'|i18n( 'design/admin/shop/orderedit' )}" />
                        </div>
                    </div>
                    {* DESIGN: Control bar END *}</div></div>
        </div>

</div>
</form>

{literal}
<script type="text/javascript">
    function confirmRemoveProduct() {
        if (!confirm('{/literal}{'Do you confirm the suppression products'|i18n( 'design/admin/shop/orderedit' )}{literal}')) {
            return false;
        }
    }
</script>
{/literal}