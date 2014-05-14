<?php
/**
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://ez.no/Resources/Software/Licenses/eZ-Business-Use-License-Agreement-eZ-BUL-Version-2.1 eZ Business Use License Agreement eZ BUL Version 2.1
 * @version 5.2.0
 * @package kernel
 */

$CustomerID = $Params['CustomerID'];
$Email = $Params['Email'];
$module = $Params['Module'];


$http = eZHTTPTool::instance();

$tpl = eZTemplate::factory();

$Email = urldecode( $Email );
$productList = eZOrder::productList( $CustomerID, $Email );
$orderList = eZOrder::orderList( $CustomerID, $Email );

$tpl->setVariable( "product_list", $productList );

$tpl->setVariable( "order_list", $orderList );

$Result = array();
$Result['content'] = $tpl->fetch( "design:shop/customerorderview.tpl" );
$path = array();
$path[] = array( 'url' => '/owshop/orderlist',
                 'text' => ezpI18n::tr( 'kernel/shop', 'Order list' ) );
$path[] = array( 'url' => false,
                 'text' => ezpI18n::tr( 'kernel/shop', 'Customer order view' ) );
$Result['path'] = $path;

?>
