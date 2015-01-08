<?php
/**
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://ez.no/Resources/Software/Licenses/eZ-Business-Use-License-Agreement-eZ-BUL-Version-2.1 eZ Business Use License Agreement eZ BUL Version 2.1
 * @version 5.2.0
 * @package kernel
 */

$Module = $Params['Module'];
$http = eZHTTPTool::instance();
$deleteIDArray = $http->sessionVariable( "DeleteOrderIDArray" );

if ( $http->hasPostVariable( "ConfirmButton" ) )
{
    $db = eZDB::instance();
    $db->begin();
    foreach ( $deleteIDArray as $deleteID )
    {
        eZOrder::cleanupOrder( $deleteID );
    }
    $db->commit();
    $Module->redirectTo( '/owshop/orderlist/' );
}
elseif ( $http->hasPostVariable( "CancelButton" ) )
{
    $Module->redirectTo( '/owshop/orderlist/' );
}
else // no action yet: just displaying the template
{
    $orderNumbersArray = array();
    foreach ( $deleteIDArray as $orderID )
    {
        $order = eZOrder::fetch( $orderID );
        if ( $order === null )
            continue;   // just to prevent possible fatal error below

        $orderNumbersArray[] = $order->attribute( 'order_nr' );
    }
    $orderNumbersString = implode( ', ', $orderNumbersArray );

    $Module->setTitle( ezpI18n::tr( 'owshop', 'Remove orders' ) );

    $tpl = eZTemplate::factory();
    $tpl->setVariable( "module", $Module );
    $tpl->setVariable( "delete_result", $orderNumbersString );
    $Result = array();

    $Result['path'] = array( array( 'text' => ezpI18n::tr( 'kernel/shop', 'Remove order' ),
                                    'url' => false ) );
    $Result['content'] = $tpl->fetch( "design:shop/removeorder.tpl" );
}
?>
