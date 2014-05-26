<?php

/**
 * File containing the OWUserShopAccountHandler class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://ez.no/Resources/Software/Licenses/eZ-Business-Use-License-Agreement-eZ-BUL-Version-2.1 eZ Business Use License Agreement eZ BUL Version 2.1
 * @version 5.2.0
 * @package kernel
 */
class OWUserShopAccountHandler {

    public $shopAccountINI;
    public $user;
    public $fieldList = array();
    public $fieldConfiguration = array();
    public $userAccountInfo = array();
    public $accountInfo = array();
    public $defaultValues = array();

    function __construct() {
        $this->shopAccountINI = eZINI::instance( 'shopaccount.ini' );
        $this->user = eZUser::currentUser();
        $userObject = $this->user->attribute( 'contentobject' );
        $userDataMap = $userObject->dataMap();
        /*
        $orderList = eZOrder::activeByUserID( $this->user->attribute( 'contentobject_id' ) );
        if ( count( $orderList ) > 0 and $this->user->isRegistered() ) {
            $previousOrderAccountInfo = $orderList[0]->accountInformation();
            $previousOrderAccountInfo = $previousOrderAccountInfo['account_info'];
        }
         * */
         
        $this->fieldList = array(
            'account_name' => array(),
            'email' => null,
            'customer' => array(),
            'delivery_address' => array(),
            'all' => array()
        );

        if ( $this->shopAccountINI->hasVariable( 'AccountSettings', 'AccountNameFields' ) ) {
            $this->fieldList['account_name'] = $this->shopAccountINI->variable( 'AccountSettings', 'AccountNameFields' );
        }
        if ( empty( $this->fieldList['account_name'] ) ) {
            eZDebug::writeError( "[AccountSettings]AccountNameFields in shopaccount.ini must be filled", "OWUserShopAccountHandler" );
        } else {
            $this->fieldList['customer'] = $this->fieldList['account_name'];
        }

        if ( $this->shopAccountINI->hasVariable( 'AccountSettings', 'AccountEmailField' ) ) {
            $this->fieldList['email'] = $this->shopAccountINI->variable( 'AccountSettings', 'AccountEmailField' );
            $this->fieldList['customer'][] = $this->fieldList['email'];
        } else {
            eZDebug::writeError( "[AccountSettings]AccountEmailField in shopaccount.ini must be filled", "OWUserShopAccountHandler" );
        }

        if ( $this->shopAccountINI->hasVariable( 'AccountSettings', 'CustomerFields' ) ) {
            $this->fieldList['customer'] = array_merge( $this->fieldList['customer'], $this->shopAccountINI->variable( 'AccountSettings', 'CustomerFields' ) );
        }

        if ( $this->shopAccountINI->hasVariable( 'AccountSettings', 'DeliveryAddressFields' ) ) {
            $this->fieldList['delivery_address'] = $this->shopAccountINI->variable( 'AccountSettings', 'DeliveryAddressFields' );
        }
        $this->fieldList['all'] = array_unique( array_merge( $this->fieldList['customer'], $this->fieldList['delivery_address'] ) );
        foreach ( $this->fieldList['all'] as $field ) {
            $conf = array(
                'name' => $field,
                'required' => false,
                'type' => 'string'
            );
            $iniGroupName = "$field-FieldsDeliveryAddressSettings";
            if ( $this->shopAccountINI->hasGroup( $iniGroupName ) ) {
                $fieldDeliveryAddressSettings = $this->shopAccountINI->group( $iniGroupName );
                if ( isset( $fieldDeliveryAddressSettings['Name'] ) ) {
                    $conf['name'] = $fieldDeliveryAddressSettings['Name'];
                }
                if ( isset( $fieldDeliveryAddressSettings['Required'] ) ) {
                    $conf['required'] = $fieldDeliveryAddressSettings['Required'] == 'true';
                }
                if ( isset( $fieldDeliveryAddressSettings['Type'] ) ) {
                    $conf['type'] = $fieldDeliveryAddressSettings['Type'];
                }
                $this->userAccountInfo[$field] = null;
                if ( $this->user->isRegistered() ) {
                    if ( isset( $fieldDeliveryAddressSettings['UserAccountFieldMapping'] ) ) {
                        if ( isset( $userDataMap[$fieldDeliveryAddressSettings['UserAccountFieldMapping']] ) ) {
                            $content = $userDataMap[$fieldDeliveryAddressSettings['UserAccountFieldMapping']]->content();
                            if ( $content instanceof eZUser ) {
                                if ( $conf['type'] == 'email' ) {
                                    $this->userAccountInfo[$field] = $content->attribute( 'email' );
                                } else {
                                    $this->userAccountInfo[$field] = $content->attribute( 'login' );
                                }
                            } else {
                                $this->userAccountInfo[$field] = $userDataMap[$fieldDeliveryAddressSettings['UserAccountFieldMapping']]->title();
                            }
                        }
                    }
                    if ( isset( $previousOrderAccountInfo ) && isset( $previousOrderAccountInfo[$field] ) ) {
                        $this->defaultValues[$field] = $previousOrderAccountInfo[$field];
                    } elseif ( isset( $fieldDeliveryAddressSettings['Autocomplete'] ) && $fieldDeliveryAddressSettings['Autocomplete'] ) {
                        $this->defaultValues[$field] = $this->userAccountInfo[$field];
                    }
                }
            }
            $this->fieldConfiguration[$field] = $conf;
        }
    }

    /* !
      Will verify that the user has supplied the correct user information.
      Returns true if we have all the information needed about the user.
     */

    function verifyAccountInformation() {
        return false;
    }

    /* !
      Redirectes to the user registration page.
     */

    function fetchAccountInformation( &$module ) {
        $module->redirectTo( '/owshop/userregister/' );
    }

    /* !
      \return the account information for the given order
     */

    function email( $order ) {
        $email = false;
        $xmlString = $order->attribute( 'data_text_1' );
        if ( $xmlString != null ) {
            $dom = new DOMDocument( '1.0', 'utf-8' );
            $dom->loadXML( $xmlString );
            $emailNode = $dom->getElementsByTagName( $this->fieldList['email'] )->item( 0 );
            if ( $emailNode ) {
                $email = $emailNode->textContent;
            }
        }

        return $email;
    }

    /* !
      \return the account information for the given order
     */

    function accountName( $order ) {
        $accountName = array();
        $xmlString = $order->attribute( 'data_text_1' );
        if ( $xmlString != null ) {
            $dom = new DOMDocument( '1.0', 'utf-8' );
            $dom->loadXML( $xmlString );
            foreach ( $this->fieldList['account_name'] as $field ) {
                $node = $dom->getElementsByTagName( $field )->item( 0 );
                if ( $node ) {
                    $accountName[] = $node->textContent;
                }
            }
        }

        return implode( ' ', $accountName );
    }

    function accountInformation( $order ) {
        if ( $order ) {
            $dom = new DOMDocument( '1.0', 'utf-8' );
            $xmlString = $order->attribute( 'data_text_1' );
            if ( $xmlString != null ) {
                $dom = new DOMDocument( '1.0', 'utf-8' );
                $dom->loadXML( $xmlString );
                foreach ( $this->fieldList['all'] as $field ) {
                    $this->accountInfo[$field] = null;
                    $node = $dom->getElementsByTagName( $field )->item( 0 );
                    if ( $node ) {
                        $this->accountInfo[$field] = $node->textContent;
                    }
                }
                $commentNode = $dom->getElementsByTagName( 'comment' )->item( 0 );
                if ( $commentNode ) {
                    $this->accountInfo['comment'] = $commentNode->textContent;
                }
            }
        }
        $accountInformation = array(
            'field_list' => $this->fieldList,
            'field_configuration' => $this->fieldConfiguration,
            'user_account_info' => $this->userAccountInfo,
            'account_info' => $this->accountInfo,
            'default_values' => $this->defaultValues
        );

        return $accountInformation;
    }
}
