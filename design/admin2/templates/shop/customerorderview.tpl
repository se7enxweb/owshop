{* Customer information *}
<div class="context-block">
    <div class="box-header"><div class="box-ml">
            <h1 class="context-title">{'Customer information'|i18n( 'design/admin/shop/customerorderview' )}</h1>

            <div class="header-mainline"></div>

        </div></div>

    <div class="box-bc"><div class="box-ml"><div class="box-content">

                <div class="context-attributes">
                    {shop_account_view_gui view=html order=$order_list[0]}
                </div>

            </div></div></div>

</div>

{* Orders *}
<div class="context-block">
    <div class="box-header"><div class="box-ml">
            <h2 class="context-title">{'Orders (%order_count)'|i18n( 'design/admin/shop/customerorderview',, hash( '%order_count', $order_list|count ) )}</h2>
        </div>
    </div>

    <div class="box-bc"><div class="box-ml"><div class="box-content">

                {def $currency = false()
     $locale = false()
     $symbol = false()}

                {if $order_list}
                    <table class="list" cellspacing="0">
                        <tr>
                            <th>{'ID'|i18n( 'design/admin/shop/customerorderview' )}</th>
                            <th>{'Total (ex. VAT)'|i18n( 'design/admin/shop/customerorderview' )}</th>
                            <th>{'Total (inc. VAT)'|i18n( 'design/admin/shop/customerorderview' )}</th>
                            <th>{'Time'|i18n( 'design/admin/shop/customerorderview' )}</th>
                            <th>{'Status'|i18n( 'design/admin/shop/customerorderview' )}</th>
                        </tr>
                        {foreach $order_list as $order sequence=array(bglight,bgdark) as $background}
                            {set currency = fetch( 'shop', 'currency', hash( 'code', $order.productcollection.currency_code ) ) }
                            {if $currency}
                                {set $locale = $currency.locale
                                     $symbol = $currency.symbol}
                            {else}
                                {set $locale = false()
                                     $symbol = false()}
                            {/if}

                            <tr class="{$background}">
                                <td><a href={concat( '/owshop/orderview/', $order.id, '/' )|ezurl}>{$order.order_nr}</a></td>
                                <td class="number" align="right">{$order.total_ex_vat|l10n( 'currency', $locale, $symbol )}</td>
                                <td class="number" align="right">{$order.total_inc_vat|l10n( 'currency', $locale, $symbol )}</td>
                                <td>{$order.created|l10n( shortdatetime )}</td>
                                <td>{$order.status_name|wash}</td>
                            </tr>
                        {/foreach}
                    </table>
                {/if}

                {* DESIGN: Content END *}</div></div></div>

</div>


{* Purchased products *}
<div class="context-block">
    <div class="box-header"><div class="box-ml">
            <h2 class="context-title">{'Purchased products (%product_count)'|i18n( 'design/admin/shop/customerorderview',, hash( '%product_count', $product_list|count ) )}</h2>
        </div>
    </div>

    <div class="box-bc"><div class="box-ml"><div class="box-content">

                {if $product_list}
                    <table class="list" cellspacing="0">
                        <tr>
                            <th>{'Product'|i18n( 'design/admin/shop/customerorderview' )}</th>
                            <th>{'Quantity'|i18n( 'design/admin/shop/customerorderview' )}</th>
                            <th>{'Total (ex. VAT)'|i18n( 'design/admin/shop/customerorderview' )}</th>
                            <th>{'Total (inc. VAT)'|i18n( 'design/admin/shop/customerorderview' )}</th>
                        </tr>

                        {def $quantity_text = ''
                            $total_ex_vat_text = ''
                            $total_inc_vat_text = ''
                            $br_tag = ''}

                        {foreach $product_list as $product sequence=array(bglight,bgdark) as $background}

                            {set quantity_text = ''
                                    total_ex_vat_text = ''
                                    total_inc_vat_text = ''
                                    br_tag = ''}

                            {foreach $product.product_info as $currency_code => $info}
                                {if $currency_code}
                                    {set currency = fetch( 'shop', 'currency', hash( 'code', $currency_code ) ) }
                                {else}
                                    {set currency = false()}
                                {/if}
                                {if $currency}
                                    {set locale = $currency.locale
                                         symbol = $currency.symbol}
                                {else}
                                    {set locale = false()
                                         symbol = false()}
                                {/if}

                                {set quantity_text = concat( $quantity_text, $br_tag, $info.sum_count) }
                                {set total_ex_vat_text = concat($total_ex_vat_text, $br_tag, $info.sum_ex_vat|l10n( 'currency', $locale, $symbol )) }
                                {set total_inc_vat_text = concat($total_inc_vat_text, $br_tag, $info.sum_inc_vat|l10n( 'currency', $locale, $symbol )) }

                                {if $br_tag|not()}
                                    {set br_tag = '<br />'}
                                {/if}
                            {/foreach}

                            <tr class="{$background}">
                                {if and( $product.product, $product.product.main_node )}
                                    {let node_url=$product.product.main_node.url_alias}
                                    <td class="name">{$product.product.class_identifier|class_icon( small, $product.product.class_name )}&nbsp;{if $node_url}<a href={$node_url|ezurl}>{/if}{$product.product.name|wash}{if $node_url}</a>{/if}</td>
                                    {/let}
                                {else}
                                    <td class="name">{false()|class_icon( small )}&nbsp;{$Products.name|wash}</td>
                                {/if}
                                <td class="number" align="right">{$quantity_text}</td>
                                <td class="number" align="right">{$total_ex_vat_text}</td>
                                <td class="number" align="right">{$total_inc_vat_text}</td>
                            </tr>
                        {/foreach}
                    </table>
                {/if}

                {undef}

            </div>
        </div>
    </div>
</div>

