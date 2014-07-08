<?php

/**
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://ez.no/Resources/Software/Licenses/eZ-Business-Use-License-Agreement-eZ-BUL-Version-2.1 eZ Business Use License Agreement eZ BUL Version 2.1
 * @version 5.2.0
 * @package kernel
 */
$http = eZHTTPTool::instance();
$module = $Params['Module'];
$shopIni = eZINI::instance( 'shop.ini' );

$tpl = eZTemplate::factory();

if ($module->isCurrentAction('Return')) {
    $module->redirectTo('/owshop/basket/');
    return;
}

if ( $module->isCurrentAction( 'Cancel' ) ) {
    if($shopIni->hasVariable('ShopSettings', 'CancelUserregisterNodeId')) {
        $node = eZFunctionHandler::execute('content', 'node', array(
            'node_id' => $shopIni->variable('ShopSettings', 'CancelUserregisterNodeId')
        ));
        eZBasket::cleanupCurrentBasket(false);
        $module->redirectTo($node->url());
    } else {
        $module->redirectTo('/owshop/basket/');
    }
    return;
}

$accountHandler = eZShopAccountHandler::instance();
$userAccountFieldList = $accountHandler->fieldList['all'];
$comment = false;
$deliveryAddressChoice = 'UserAccountAddress';

$tpl->setVariable( "input_error", false );
if ( $module->isCurrentAction( 'Store' ) ) {
    $inputIsValid = true;
    $deliveryAddressChoice = $http->postVariable( "DeliveryAddress" );
    $deliveryAddress = array();
    foreach ( $userAccountFieldList as $field ) {
        switch ( $deliveryAddressChoice ) {
            case 'UserAccountAddress':
                $deliveryAddress = $accountHandler->userAccountInfo;
                break;
            case 'OtherAddress':
                $deliveryAddress[$field] = null;
                if ( $http->hasPostVariable( "DeliveryAddress_$field" ) ) {
                    $deliveryAddress[$field] = $http->postVariable( "DeliveryAddress_$field" );
                    $accountHandler->defaultValues[$field] = $deliveryAddress[$field];
                }
                break;
            default:
                $deliveryAddress[$field] = false;
        }
        if ( $accountHandler->fieldConfiguration[$field]['required'] && trim( $deliveryAddress[$field] ) == "" ) {
            $inputIsValid = false;
        }
        if ( $accountHandler->fieldConfiguration[$field]['type'] == 'email' && !eZMail::validate( $deliveryAddress[$field] ) ) {
            $inputIsValid = false;
        }
    }

    $comment = $http->postVariable( "Comment" );

    if ( $inputIsValid == true ) {
        // Check for validation
        $basket = eZBasket::currentBasket();

        $db = eZDB::instance();
        $db->begin();
        $order = $basket->createOrder();

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
        OWShopFunctions::setPreferredUserCountry( $country );
        $http->setSessionVariable( 'MyTemporaryOrderID', $order->attribute( 'id' ) );

        $module->redirectTo( '/owshop/confirmorder/' );
        return;
    } else {
        $tpl->setVariable( "input_error", true );
    }
}

$tpl->setVariable( "user_shop_account", $accountHandler->accountInformation( null ) );
$tpl->setVariable( "delivery_address_choice", $deliveryAddressChoice );
$tpl->setVariable( "comment", $comment );

$Result = array();
$Result['content'] = $tpl->fetch( "design:shop/userregister.tpl" );
$Result['path'] = array( array( 'url' => false,
        'text' => ezpI18n::tr( 'kernel/shop', 'Enter account information' ) ) );

