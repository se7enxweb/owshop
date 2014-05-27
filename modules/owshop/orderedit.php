<?php
$module = $Params['Module'];
$http = eZHTTPTool::instance();

$tpl = eZTemplate::factory();
$OrderID = $Params['OrderID'];

if ( $http->hasPostVariable( 'OrderID' ) ) {
    $OrderID = $http->postVariable( 'OrderID' );
}

$order = eZOrder::fetch( $OrderID );

if ( !$order )
{
    return $module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
}

if ( $http->hasPostVariable( 'SaveOrderUserInfoButton' ) )
{
    $accountHandler = $order->accountInformation();

    $userAccountFieldList = $accountHandler['field_list']['all'];
    $deliveryAddress = array();
    $inputIsValid = true;
    foreach ( $userAccountFieldList as $field )
    {
        if ( $http->hasPostVariable( "DeliveryAddress_$field" ) ) {
            $deliveryAddress[$field] = $http->postVariable( "DeliveryAddress_$field" );
            $accountHandler['default_values'][$field] = $deliveryAddress[$field];
        }
        if ( $accountHandler['field_configuration'][$field]['required'] && trim( $deliveryAddress[$field] ) == "" ) {
            $inputIsValid = false;
        }
        if ( $accountHandler['field_configuration'][$field]['type'] == 'email' && !eZMail::validate( $deliveryAddress[$field] ) ) {
            $inputIsValid = false;
        }
    }

    $comment = $http->postVariable( "Comment" );

    if ( $inputIsValid == true )
    {
        $db = eZDB::instance();
        $db->begin();
        $doc = new DOMDocument( '1.0', 'utf-8' );

        $root = $doc->createElement( "shop_account" );
        $doc->appendChild( $root );

        foreach ( $deliveryAddress as $field => $value ) {
            $fieldNode = $doc->createElement( $field, $value );
            $root->appendChild( $fieldNode );
        }

        $commentNode = $doc->createElement( "comment", $comment );
        $root->appendChild( $commentNode );

        $xmlString = $doc->saveXML();

        $order->setAttribute( 'data_text_1', $xmlString );
        $order->setAttribute( 'account_identifier', "ez" );

        $order->setAttribute( 'ignore_vat', 0 );

        $order->store();
        $db->commit();
    } else {
        $tpl->setVariable( "error", ezpI18n::tr( 'owshop/error', 'Input did not validate' ) . '.' );
    }
}

if ( $http->hasPostVariable( 'RemoveProductButton' ) )
{
    if ( $http->hasPostVariable( 'ProductOrderArray' ) )
    {
        $productOrderArray = $http->postVariable( 'ProductOrderArray' );
        if ( $productOrderArray !== null )
        {
            if ($order->countProductItems() - count($productOrderArray) >= 1) {
                foreach($productOrderArray as $productOrder) {
                    $order->removeItem($productOrder);
                }
            } else {
                $tpl->setVariable( "error", ezpI18n::tr( 'owshop/error', 'It takes at least one product in order' ) );
            }
        }
    }
}

if ( $module->isCurrentAction( 'OWShopSelectProduct' ) ) {
    $selectedNodeIDArray = eZContentBrowse::result( 'OWShopSelectProduct' );
    $nodeID = current( $selectedNodeIDArray );
    if ( is_numeric( $nodeID ) ) {
        $order->addItem( $nodeID );
    }
}elseif ( $module->isCurrentAction( 'BrowseAddProduct' ) ) {
        $importOptions = array();
        eZContentBrowse::browse( array( 'action_name' => 'OWShopSelectProduct',
            'description_template' => false,
            'from_page' => '/owshop/orderedit/' . $OrderID ), $module );

    return;
}


if ( $http->hasPostVariable( 'UpdateQtButton' ) ) {
    $itemCountProductArray = $http->postVariable( 'CountProduct' );
    if ( $itemCountProductArray !== null )
    {
        foreach($itemCountProductArray as $key => $itemCountProduct) {
            if($itemCountProduct > 0 && is_numeric($itemCountProduct)) {
                $order->updateItem($key, $itemCountProduct);
            } else {
                $tpl->setVariable( "error", ezpI18n::tr( 'owshop/error', 'The amount must be higher than zero' ) );
                break;
            }
        }
    }
}

if ( $http->hasPostVariable( 'SaveOrderStatusButton' ) )
{
    $statusID = $http->postVariable( 'StatusOrder' );

    $access = $order->canModifyStatus( $statusID );
    if ( $access and $order->attribute( 'status_id' ) != $statusID )
    {
        $order->modifyStatus( $statusID );
    }
}

$statusArray = eZOrderStatus::fetchList();

$tpl->setVariable( "order", $order );
$tpl->setVariable( 'status_list', $statusArray );

$Result = array();
$Result['path'] = array(
    array( 'text' => ezpI18n::tr( 'kernel/shop', 'Order list' ), 'url' => 'owshop/orderlist' ),
    array( 'text' => ezpI18n::tr( 'kernel/owshop', 'Order Edit' ),'url' => false )
);

$Result['content'] = $tpl->fetch( 'design:shop/orderedit.tpl' );

?>