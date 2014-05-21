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

if ( $module->isCurrentAction( 'Cancel' ) ) {
    $module->redirectTo( '/owshop/basket/' );
    return;
}

$user = eZUser::currentUser();
$userObject = $user->attribute( 'contentobject' );
$userMap = $userObject->dataMap();

$orderList = eZOrder::activeByUserID( $user->attribute( 'contentobject_id' ) );
if ( count( $orderList ) > 0 and $user->isRegistered() ) {
    $previousOrderAccountInfo = $orderList[0]->accountInformation();
}

$deliveryAddressFieldList = array_flip( $shopIni->variable( 'DeliveryAddressSettings', 'AvailableFields' ) );
foreach ( $deliveryAddressFieldList as $field => $conf ) {
    $conf = array(
        'Name' => $field,
        'Required' => false,
        'Type' => 'string',
        'UserAccountFieldMapping' => false,
        'UserAccountValue' => false,
        'Autocomplete' => false,
        'DefaultValue' => false
    );
    $iniGroupname = "$field-FieldsDeliveryAddressSettings";
    if ( $shopIni->hasGroup( $iniGroupname ) ) {
        $fieldDeliveryAddressSettings = $shopIni->group( $iniGroupname );
        $conf = array_merge( $conf, $fieldDeliveryAddressSettings );
    }
    if ( $conf['UserAccountFieldMapping'] && $user->isRegistered() ) {
        if ( isset( $userMap[$conf['UserAccountFieldMapping']] ) ) {
            $content = $userMap[$conf['UserAccountFieldMapping']]->content();
            if ( $content instanceof eZUser ) {
                $content = $content->attribute( 'email' );
            }
            $conf['UserAccountValue'] = $content;
        }
    }
    if ( isset( $previousOrderAccountInfo ) && isset( $previousOrderAccountInfo[$field] ) ) {
        $conf['DefaultValue'] = $previousOrderAccountInfo[$field];
    } elseif ( $conf['Autocomplete'] ) {
        $conf['DefaultValue'] = $conf['UserAccountValue'];
    }
    $deliveryAddressFieldList[$field] = $conf;
}

$comment = false;

$tpl->setVariable( "input_error", false );
if ( $module->isCurrentAction( 'Store' ) ) {
    $inputIsValid = true;
    $deliveryAddressChoice = $http->postVariable( "DeliveryAddress" );
    $deliveryAddress = array();
    foreach ( $deliveryAddressFieldList as $field => $conf ) {
        switch ( $deliveryAddressChoice ) {
            case 'UserAccountAddress':
                $deliveryAddress[$field] = $conf['UserAccountValue'];
                break;
            case 'OtherAddress':
                if ( $http->hasPostVariable( "DeliveryAddress_$field" ) ) {
                    $deliveryAddress[$field] = $http->postVariable( "DeliveryAddress_$field" );
                }
            default:
                $deliveryAddress[$field] = false;
        }
        if ( $conf['Required'] && trim( $deliveryAddress[$field] ) == "" ) {
            $inputIsValid = false;
        }
        if ( $conf['Type'] == 'email' && !eZMail::validate( $deliveryAddress[$field] ) ) {
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

$tpl->setVariable( "delivery_address_field_list", $deliveryAddressFieldList );
$tpl->setVariable( "comment", $comment );

$Result = array();
$Result['content'] = $tpl->fetch( "design:shop/userregister.tpl" );
$Result['path'] = array( array( 'url' => false,
        'text' => ezpI18n::tr( 'kernel/shop', 'Enter account information' ) ) );

