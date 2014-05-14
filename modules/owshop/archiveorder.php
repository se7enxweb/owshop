<?php
/**
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://ez.no/Resources/Software/Licenses/eZ-Business-Use-License-Agreement-eZ-BUL-Version-2.1 eZ Business Use License Agreement eZ BUL Version 2.1
 * @version 5.2.0
 * @package kernel
 */

$Module = $Params['Module'];
$http = eZHTTPTool::instance();
$archiveIDArray = $http->sessionVariable( "OrderIDArray" );

$db = eZDB::instance();
$db->begin();
foreach ( $archiveIDArray as $archiveID )
{
    eZOrder::archiveOrder( $archiveID );
}
$db->commit();
$Module->redirectTo( '/shop/orderlist/' );
?>
