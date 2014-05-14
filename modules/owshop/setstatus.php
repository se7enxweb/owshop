<?php
/**
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://ez.no/Resources/Software/Licenses/eZ-Business-Use-License-Agreement-eZ-BUL-Version-2.1 eZ Business Use License Agreement eZ BUL Version 2.1
 * @version 5.2.0
 * @package kernel
 */

$module = $Params['Module'];
$http = eZHTTPTool::instance();
$user = eZUser::currentUser();

$order = eZOrder::fetch( $OrderID );
if ( !$order )
{
    return $module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
}

if ( $http->hasPostVariable( "OrderID" ) && $http->hasPostVariable( "StatusID" ) && $http->hasPostVariable( "SetOrderStatusButton" ) )
{
    $access = $order->canModifyStatus( $StatusID );

    if ( $access )
    {
        if ( $order->attribute( 'status_id' ) != $StatusID )
        {
            $order->modifyStatus( $StatusID );
        }

        if ( $http->hasPostVariable( 'RedirectURI' ) )
        {
            $uri = $http->postVariable( 'RedirectURI' );
            $module->redirectTo( $uri );
            return;
        }
        else
        {
            $module->redirectTo( '/shop/orderview/' . $orderID );
            return;
        }
    }
    else
    {
        return $module->handleError( eZError::KERNEL_ACCESS_DENIED, 'kernel' );
    }
}

return $module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );

?>
