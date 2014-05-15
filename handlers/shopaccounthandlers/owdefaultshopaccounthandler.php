<?php
/**
 * File containing the OWDefaultShopAccountHandler class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://ez.no/Resources/Software/Licenses/eZ-Business-Use-License-Agreement-eZ-BUL-Version-2.1 eZ Business Use License Agreement eZ BUL Version 2.1
 * @version 5.2.0
 * @package kernel
 */

class OWDefaultShopAccountHandler
{
    function OWDefaultShopAccountHandler()
    {

    }

    /*!
     Will verify that the user has supplied the correct user information.
     Returns true if we have all the information needed about the user.
    */
    function verifyAccountInformation()
    {
        return eZUser::isCurrentUserRegistered();
    }

    /*!
     Redirectes to the user registration page.
    */
    function email( $order = false )
    {
        if ( $order === false )
            $user = eZUser::currentUser();
        else
            $user = $order->attribute( 'user' );

        if ( is_object( $user ) )
            return $user->attribute( 'email' );
        else
            return null;
    }

    /*!
     \return the custom name for the given order
    */
    function accountName( $order = false )
    {
        if ( $order === false )
            $user = eZUser::currentUser();
        else
            $user = $order->attribute( 'user' );

        if ( is_object( $user ) )
        {
            $userObject = $user->attribute( 'contentobject' );
            $accountName = $userObject->attribute( 'name' );
        }
        else
            $accountName = null;
        return $accountName;
    }

    function fetchAccountInformation( &$module )
    {
        $http = eZHTTPTool::instance();
        $http->setSessionVariable( 'RedirectAfterLogin', '/owshop/basket/' );
        $http->setSessionVariable( 'DoCheckoutAutomatically', true );
        $module->redirectTo( '/user/login/' );
    }

    function accountInformation( $order )
    {
        $user = $order->user();
        $userObject = $user->attribute( "contentobject" );
        $dataMap = $userObject->dataMap();

        return array( 'first_name' => $dataMap['first_name']->content(),
                      'last_name' => $dataMap['last_name']->content(),
                      'email' => $user->attribute( "email" ) );
    }
}

?>
