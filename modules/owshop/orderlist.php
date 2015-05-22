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

$limit = 20;
$viewParameters = array( 'offset' => $offset);

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
if ( $http->hasVariable( 'RemoveButton' ) ) {
    if ( $http->hasVariable( 'OrderID' ) ) {
        $orderID = $http->variable( 'OrderID' );
        if ( $orderID !== null ) {
            $http->setSessionVariable( 'DeleteOrderIDArray', array($orderID) );
            $Module->redirectTo( $Module->functionURI( 'removeorder' ) . '/' );
        }
    }
}

// Archive options.
if ( $http->hasVariable( 'ArchiveButton' ) ) {
    if ( $http->hasVariable( 'OrderID' ) ) {
        $orderID = $http->variable( 'OrderID' );
        if ( $orderID !== null ) {
            $http->setSessionVariable( 'OrderIDArray', array($orderID) );
            $Module->redirectTo( $Module->functionURI( 'archiveorder' ) . '/' );
        }
    }
}

// Save Status Order
if ( $http->hasVariable( 'SaveOrderStatusButton' ) ) {
    if ( $http->hasVariable( 'StatusList' ) ) {
        foreach ( $http->variable( 'StatusList' ) as $orderID => $statusID ) {
            $order = eZOrder::fetch( $orderID );
            $access = $order->canModifyStatus( $statusID );
            if ( $access and $order->attribute( 'status_id' ) != $statusID ) {
                $order->modifyStatus( $statusID );
            }
        }
    }
}

// Validate command (change status tu validate)
if ( $http->hasVariable( 'ValidateOrderButton' ) ) {
    if ( $http->hasVariable( 'OrderID' ) ) {
        $statusID = 2;
        $orderID = $http->variable('OrderID');
        if ($orderID !== NULL) {
            $order = eZOrder::fetch($orderID);
            $access = $order->canModifyStatus($statusID);
            if ($access and $order->attribute('status_id') != $statusID) {
                $order->modifyStatus($statusID);
            }
            $Module->redirectTo( $Module->functionURI( 'orderview' ) . '/' . $orderID);
        }
    }
}

// Filter Order
$filterOrder = '';

if($http->hasVariable( 'FilterOrderButton' )) {
    $viewParameters['offset'] = $offset = '0';
}
if ( $http->hasVariable( 'fromDateOrder' ) || isset( $Params['fromDateOrder'] ) ) {
    if($http->variable( 'fromDateOrder' ) != '') {
        $viewParameters['fromDateOrder'] =  $http->variable( 'fromDateOrder' );
    } elseif($Params['fromDateOrder'] != '') {
        $viewParameters['fromDateOrder'] =  $Params['fromDateOrder'];
    }
    if(isset($viewParameters['fromDateOrder']) &&  $viewParameters['fromDateOrder'] != '') {
        $filterOrder .= ' ezorder.created >= ' . OWShopFunctions::dateToTimestamp($viewParameters['fromDateOrder'], '00:00:01');
        $tpl->setVariable('fromDateOrder', $viewParameters['fromDateOrder']);
    }
}

if ( $http->hasVariable( 'toDateOrder' ) || isset( $Params['toDateOrder'] ) ) {
    if($http->variable( 'toDateOrder' ) != '') {
        $viewParameters['toDateOrder'] =  $http->variable( 'toDateOrder' );
    } elseif($Params['toDateOrder'] != '') {
        $viewParameters['toDateOrder'] =  $Params['toDateOrder'];
    }
    if(isset($viewParameters['toDateOrder']) && $viewParameters['toDateOrder'] != '') {
        $tpl->setVariable( 'toDateOrder', $viewParameters['toDateOrder'] );
        $filterOrder .= ($filterOrder != '') ? ' AND ' : '';
        $filterOrder .= ' ezorder.created <= ' . OWShopFunctions::dateToTimestamp( $viewParameters['toDateOrder'], '23:59:59' );
    }
}

if ( $http->hasVariable( 'statusOrder' ) || isset( $Params['statusOrder'] )) {
    if($http->variable( 'statusOrder' ) != '') {
        $viewParameters['statusOrder'] =  $http->variable( 'statusOrder' );
    } elseif($Params['statusOrder'] != '') {
        $viewParameters['statusOrder'] =  $Params['statusOrder'];
    }
    if(isset($viewParameters['statusOrder']) && $viewParameters['statusOrder'] != '') {
        $tpl->setVariable( 'statusOrder', $viewParameters['statusOrder'] );
        $filterOrder .= ($filterOrder != '') ? ' AND ' : '';
        $filterOrder .= ' ezorder.status_id = ' . $viewParameters['statusOrder'];
    }
}

if ( $http->hasVariable( 'searchOrder' ) || isset( $Params['searchOrder']) ) {
    if($http->variable( 'searchOrder' ) != '') {
        $viewParameters['searchOrder'] =  $http->variable( 'searchOrder' );
    } elseif($Params['searchOrder'] != '') {
        $viewParameters['searchOrder'] =  $Params['searchOrder'];
    }
    if(isset($viewParameters['searchOrder']) && $viewParameters['searchOrder'] != '') {
        $tpl->setVariable( 'searchOrder', $viewParameters['searchOrder'] );
        $filterOrder .= ($filterOrder != '') ? ' AND ' : '';
        $filterOrder .= ' ezorder.data_text_1 like \'%' . $viewParameters['searchOrder'] . '%\'';
    }
}

// Export CSV Order
if ( $http->hasVariable( 'ExportCSVButton' ) ) {
    if ( $http->hasVariable( 'OrderIDArray' ) ) {
        $orderIDArray = $http->variable( 'OrderIDArray' );
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
$orderCount = eZOrder::activeCount( eZOrder::SHOW_NORMAL, $filterOrder );
$statusArray = eZOrderStatus::fetchList();
$tpl->setVariable( 'order_list', $orderArray );
$tpl->setVariable( 'order_list_count', $orderCount );
$tpl->setVariable( 'limit', $limit );
$tpl->setVariable( 'status_list', $statusArray );

$tpl->setVariable( 'view_parameters', $viewParameters );
$tpl->setVariable( 'sort_field', $sortField );
$tpl->setVariable( 'sort_order', $sortOrder );

$Result = array();
$Result['path'] = array( array( 'text' => ezpI18n::tr( 'kernel/shop', 'Order list' ),
        'url' => false ) );

$Result['content'] = $tpl->fetch( 'design:shop/orderlist.tpl' );
?>
