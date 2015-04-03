{* Name. *}
<div class="block">
    <label>{'Name'|i18n( 'design/admin/shop/accounthandlers/html/ez' )}:</label>
    {let customer_user=fetch( content, object, hash( object_id, $order.user_id ) )}
    <a href={$customer_user.main_node.url_alias|ezurl}>{$order.account_name|wash}</a>
    {/let}
</div>

{* Email. *}
<div class="block">
    {foreach $order.account_information.field_list.customer as $field}
        {$order.account_information.field_configuration.$field.name}: {$order.account_information.account_info.$field|wash}<br />
    {/foreach}
</div>
{* Address. *}
<div class="block">
    <fieldset>
        <legend>{'Address'|i18n( 'design/admin/shop/accounthandlers/html/ez' )}</legend>
        <table class="list" cellspacing="0">
            {foreach $order.account_information.field_list.delivery_address as $field}
                <tr>
                    <td>{$order.account_information.field_configuration.$field.name|wash}</td>
                    <td>{$order.account_information.account_info.$field|wash}</td>
                </tr>
            {/foreach}
        </table>
    </fieldset>
</div>

{* Comment *}
{if $order.account_information.account_info.comment}
    <div class="block">
        <label>{'Comment'|i18n( 'design/admin/shop/accounthandlers/html/ez' )}:</label>
        <p>{$order.account_information.account_info.comment|wash|nl2br}</p>
    </div>
{/if}
