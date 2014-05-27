<?php

/**
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://ez.no/Resources/Software/Licenses/eZ-Business-Use-License-Agreement-eZ-BUL-Version-2.1 eZ Business Use License Agreement eZ BUL Version 2.1
 * @version 5.2.0
 * @package kernel
 */
$module = $Params['Module'];

$tpl = eZTemplate::factory();

$offset = $Params['Offset'];
$limit = 15;


if ( eZPreferences::value( 'admin_orderlist_sortfield' ) ) {
    $sortField = eZPreferences::value( 'admin_orderlist_sortfield' );
}

if ( !isset( $sortField ) || ( ( $sortField != 'created' ) && ( $sortField != 'user_name' ) ) ) {
    $sortField = 'created';
}

if ( eZPreferences::value( 'admin_orderlist_sortorder' ) ) {
    $sortOrder = eZPreferences::value( 'admin_orderlist_sortorder' );
}

if ( !isset( $sortOrder ) || ( ( $sortOrder != 'asc' ) && ( $sortOrder != 'desc' ) ) ) {
    $sortOrder = 'asc';
}

$http = eZHTTPTool::instance();

// The RemoveButton is not present in the orderlist, but is here for backwards
// compatibility. Simply replace the ArchiveButton for the RemoveButton will
// do the trick.
//
// Note that removing order can cause wrong order numbers (order_nr are
// reused).  See eZOrder::activate.
// Remove Order
if ( $http->hasPostVariable( 'RemoveButton' ) ) {
    if ( $http->hasPostVariable( 'OrderIDArray' ) ) {
        $orderIDArray = $http->postVariable( 'OrderIDArray' );
        if ( $orderIDArray !== null ) {
            $http->setSessionVariable( 'DeleteOrderIDArray', $orderIDArray );
            $Module->redirectTo( $Module->functionURI( 'removeorder' ) . '/' );
        }
    }
}

// Archive options.
if ( $http->hasPostVariable( 'ArchiveButton' ) ) {
    if ( $http->hasPostVariable( 'OrderIDArray' ) ) {
        $orderIDArray = $http->postVariable( 'OrderIDArray' );
        if ( $orderIDArray !== null ) {
            $http->setSessionVariable( 'OrderIDArray', $orderIDArray );
            $Module->redirectTo( $Module->functionURI( 'archiveorder' ) . '/' );
        }
    }
}

// Save Status Order
if ( $http->hasPostVariable( 'SaveOrderStatusButton' ) ) {
    if ( $http->hasPostVariable( 'StatusList' ) ) {
        foreach ( $http->postVariable( 'StatusList' ) as $orderID => $statusID ) {
            $order = eZOrder::fetch( $orderID );
            $access = $order->canModifyStatus( $statusID );
            if ( $access and $order->attribute( 'status_id' ) != $statusID ) {
                $order->modifyStatus( $statusID );
            }
        }
    }
}

// Filter Order
$filterOrder = '';
if ( $http->hasPostVariable( 'FilterOrderButton' ) ) {

    if ( $http->hasPostVariable( 'FromDateOrder' ) && $http->postVariable( 'FromDateOrder' ) != '' ) {
        $filterOrder .= ' ezorder.created >= ' . OWShopFunctions::dateToTimestamp( $http->postVariable( 'FromDateOrder' ) );
        $tpl->setVariable( 'FromDateOrder', $http->postVariable( 'FromDateOrder' ) );
    }

    if ( $http->hasPostVariable( 'ToDateOrder' ) && $http->postVariable( 'ToDateOrder' ) != '' ) {
        $tpl->setVariable( 'ToDateOrder', $http->postVariable( 'ToDateOrder' ) );
        $filterOrder .= ($filterOrder != '') ? ' AND ' : '';
        $filterOrder .= ' ezorder.created <= ' . OWShopFunctions::dateToTimestamp( $http->postVariable( 'ToDateOrder' ) );
    }

    if ( $http->hasPostVariable( 'StatusOrder' ) && $http->postVariable( 'StatusOrder' ) != '' ) {
        $tpl->setVariable( 'StatusOrder', $http->postVariable( 'StatusOrder' ) );
        $filterOrder .= ($filterOrder != '') ? ' AND ' : '';
        $filterOrder .= ' ezorder.status_id = ' . $http->postVariable( 'StatusOrder' );
    }

    if ( $http->hasPostVariable( 'SearchOrder' ) && $http->postVariable( 'SearchOrder' ) != '' ) {
        $tpl->setVariable( 'SearchOrder', $http->postVariable( 'SearchOrder' ) );
        $filterOrder .= ($filterOrder != '') ? ' AND ' : '';
        $filterOrder .= ' ezorder.data_text_1 like \'%' . $http->postVariable( 'SearchOrder' ) . '%\'';
    }
}

// Export CSV Order
if ( $http->hasPostVariable( 'ExportCSVButton' ) ) {
    if ( $http->hasPostVariable( 'OrderIDArray' ) ) {
        $orderIDArray = $http->postVariable( 'OrderIDArray' );
        if ( $orderIDArray !== null ) {
            $shopINI = eZINI::instance( 'shop.ini' );
            $handler = 'OWShopOrderExport';
            if ( $shopINI->hasVariable( 'ExportSettings', 'Handler' ) ) {
                $handler = $shopINI->variable( 'ExportSettings', 'Handler' );
                if ( !is_callable( "$handler::getFile" ) ) {
                    $handler = 'OWShopOrderExport';
                }
            }
            $filepath = call_user_func( "$handler::getFile", $orderIDArray );
            $file = pathinfo( $filepath, PATHINFO_BASENAME );
            eZFile::download( $filepath, true, $file );
            call_user_func( "$handler::removeFile", $filepath );
            eZExecution::cleanExit();
        }
    }
}

$orderArray = eZOrder::active( true, $offset, $limit, $sortField, $sortOrder, eZOrder::SHOW_NORMAL, $filterOrder );
$orderCount = eZOrder::activeCount();
$statusArray = eZOrderStatus::fetchList();

$tpl->setVariable( 'order_list', $orderArray );
$tpl->setVariable( 'order_list_count', $orderCount );
$tpl->setVariable( 'limit', $limit );
$tpl->setVariable( 'status_list', $statusArray );

$viewParameters = array( 'offset' => $offset );
$tpl->setVariable( 'view_parameters', $viewParameters );
$tpl->setVariable( 'sort_field', $sortField );
$tpl->setVariable( 'sort_order', $sortOrder );

$Result = array();
$Result['path'] = array( array( 'text' => ezpI18n::tr( 'kernel/shop', 'Order list' ),
        'url' => false ) );

$Result['content'] = $tpl->fetch( 'design:shop/orderlist.tpl' );
?>
