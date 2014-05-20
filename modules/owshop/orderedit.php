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