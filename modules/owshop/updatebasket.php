<?php
/**
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://ez.no/Resources/Software/Licenses/eZ-Business-Use-License-Agreement-eZ-BUL-Version-2.1 eZ Business Use License Agreement eZ BUL Version 2.1
 * @version 5.2.0
 * @package kernel
 */

$http = eZHTTPTool::instance();
$basket = eZBasket::currentBasket();
$module = $Params['Module'];

$itemCountList = $http->sessionVariable( 'ProductItemCountList' );
$itemIDList = $http->sessionVariable( 'ProductItemIDList' );

$operationResult = eZOperationHandler::execute( 'owshop', 'updatebasket', array( 'item_count_list' => $itemCountList,
                                                                               'item_id_list' => $itemIDList ) );

switch( $operationResult['status'] )
{
    case eZModuleOperationInfo::STATUS_HALTED:
    {
        if ( isset( $operationResult['redirect_url'] ) )
        {
            $module->redirectTo( $operationResult['redirect_url'] );
            return;
        }
        else if ( isset( $operationResult['result'] ) )
        {
            $result = $operationResult['result'];
            $resultContent = false;
            if ( is_array( $result ) )
            {
                if ( isset( $result['content'] ) )
                {
                    $resultContent = $result['content'];
                }
                if ( isset( $result['path'] ) )
                {
                    $Result['path'] = $result['path'];
                }
            }
            else
            {
                $resultContent = $result;
            }
            $Result['content'] = $resultContent;
            return $Result;
       }
    }break;
}

$module->redirectTo( '/owshop/basket/' );

?>
