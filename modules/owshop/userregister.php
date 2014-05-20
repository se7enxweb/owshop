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

$firstName = '';
$lastName = '';
$email = '';
if ( $user->isRegistered() ) {
    $userObject = $user->attribute( 'contentobject' );
    $userMap = $userObject->dataMap();
    $fieldsMapping = $shopIni->variable( 'DeliveryAddressSettings', 'UserAccountFieldsMapping' );
    $userAccountFirstName = $userAccountLastName = $userAccountEmail = $userAccountStreet1 = $userAccountStreet2 = $userAccountZip = $userAccountPlace = $userAccountState = $userAccountCountry = '';
    if ( isset( $userMap[$fieldsMapping['FirstName']] ) ) {
        $userAccountFirstName = $firstName = $userMap[$fieldsMapping['FirstName']]->content();
    }
    if ( isset( $userMap[$fieldsMapping['LastName']] ) ) {
        $userAccountLastName = $lastName = $userMap[$fieldsMapping['LastName']]->content();
    }
    $userAccountEmail = $email = $user->attribute( 'email' );
    if ( isset( $userMap[$fieldsMapping['Street1']] ) ) {
        $userAccountStreet1 = $userMap[$fieldsMapping['Street1']]->content();
    }
    if ( isset( $userMap[$fieldsMapping['Street2']] ) ) {
        $userAccountStreet2 = $userMap[$fieldsMapping['Street2']]->content();
    }
    if ( isset( $userMap[$fieldsMapping['Zip']] ) ) {
        $userAccountZip = $userMap[$fieldsMapping['Zip']]->content();
    }
    if ( isset( $userMap[$fieldsMapping['Place']] ) ) {
        $userAccountPlace = $userMap[$fieldsMapping['Place']]->content();
    }
    if ( isset( $userMap[$fieldsMapping['State']] ) ) {
        $userAccountState = $userMap[$fieldsMapping['State']]->content();
    }
    if ( isset( $userMap[$fieldsMapping['Country']] ) ) {
        $userAccountCountry = $userMap[$fieldsMapping['Country']]->content();
    }
    $tpl->setVariable( "user_account_first_name", $userAccountFirstName );
    $tpl->setVariable( "user_account_last_name", $userAccountLastName );
    $tpl->setVariable( "user_account_email", $userAccountEmail );
    $tpl->setVariable( "user_account_street1", $userAccountStreet1 );
    $tpl->setVariable( "user_account_street2", $userAccountStreet2 );
    $tpl->setVariable( "user_account_zip", $userAccountZip );
    $tpl->setVariable( "user_account_place", $userAccountPlace );
    $tpl->setVariable( "user_account_state", $userAccountState );
    $tpl->setVariable( "user_account_country", $userAccountCountry );
}

// Initialize variables
$street1 = $street2 = $zip = $place = $state = $country = $comment = '';


// Check if user has an earlier order, copy order info from that one
$orderList = eZOrder::activeByUserID( $user->attribute( 'contentobject_id' ) );
if ( count( $orderList ) > 0 and $user->isRegistered() ) {
    $accountInfo = $orderList[0]->accountInformation();
    $street1 = $accountInfo['street1'];
    $street2 = $accountInfo['street2'];
    $zip = $accountInfo['zip'];
    $place = $accountInfo['place'];
    $state = $accountInfo['state'];
    $country = $accountInfo['country'];
}

$tpl->setVariable( "input_error", false );
if ( $module->isCurrentAction( 'Store' ) ) {
    $inputIsValid = true;
    $deliveryAddress = $http->postVariable( "DeliveryAddress" );
    switch ( $deliveryAddress ) {
        case 'UserAccountAddress':
            $firstName = $userAccountFirstName;
            $lastName = $userAccountLastName;
            $email = $userAccountEmail;
            $street1 = $userAccountStreet1;
            $street2 = $userAccountStreet2;
            $zip = $userAccountZip;
            $place = $userAccountPlace;
            $state = $userAccountState;
            $country = $userAccountCountry;
            break;
        case 'OtherAddress':

            $firstName = $http->postVariable( "FirstName" );
            if ( trim( $firstName ) == "" ) {
                $inputIsValid = false;
            }
            $lastName = $http->postVariable( "LastName" );
            if ( trim( $lastName ) == "" ) {
                $inputIsValid = false;
            }
            $email = $http->postVariable( "EMail" );
            if ( !eZMail::validate( $email ) ) {
                $inputIsValid = false;
            }

            $street1 = $http->postVariable( "Street1" );
            $street2 = $http->postVariable( "Street2" );
            if ( trim( $street2 ) == "" ) {
                $inputIsValid = false;
            }

            $zip = $http->postVariable( "Zip" );
            if ( trim( $zip ) == "" ) {
                $inputIsValid = false;
            }
            $place = $http->postVariable( "Place" );
            if ( trim( $place ) == "" ) {
                $inputIsValid = false;
            }
            $state = $http->postVariable( "State" );
            $country = $http->postVariable( "Country" );
            if ( trim( $country ) == "" ) {
                $inputIsValid = false;
            }
            break;
        default:
            $inputIsValid = false;
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

        $firstNameNode = $doc->createElement( "first-name", $firstName );
        $root->appendChild( $firstNameNode );

        $lastNameNode = $doc->createElement( "last-name", $lastName );
        $root->appendChild( $lastNameNode );

        $emailNode = $doc->createElement( "email", $email );
        $root->appendChild( $emailNode );

        $street1Node = $doc->createElement( "street1", $street1 );
        $root->appendChild( $street1Node );

        $street2Node = $doc->createElement( "street2", $street2 );
        $root->appendChild( $street2Node );

        $zipNode = $doc->createElement( "zip", $zip );
        $root->appendChild( $zipNode );

        $placeNode = $doc->createElement( "place", $place );
        $root->appendChild( $placeNode );

        $stateNode = $doc->createElement( "state", $state );
        $root->appendChild( $stateNode );

        $countryNode = $doc->createElement( "country", $country );
        $root->appendChild( $countryNode );

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

$tpl->setVariable( "first_name", $firstName );
$tpl->setVariable( "last_name", $lastName );
$tpl->setVariable( "email", $email );

$tpl->setVariable( "street1", $street1 );
$tpl->setVariable( "street2", $street2 );
$tpl->setVariable( "zip", $zip );
$tpl->setVariable( "place", $place );
$tpl->setVariable( "state", $state );
$tpl->setVariable( "country", $country );
$tpl->setVariable( "comment", $comment );

$Result = array();
$Result['content'] = $tpl->fetch( "design:shop/userregister.tpl" );
$Result['path'] = array( array( 'url' => false,
        'text' => ezpI18n::tr( 'kernel/shop', 'Enter account information' ) ) );

