<?php
/**
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://ez.no/Resources/Software/Licenses/eZ-Business-Use-License-Agreement-eZ-BUL-Version-2.1 eZ Business Use License Agreement eZ BUL Version 2.1
 * @version 5.2.0
 * @package kernel
 */

$module = $Params['Module'];

if ( $module->isCurrentAction( 'Set' ) && $module->hasActionParameter( 'Country' ) )
{
    $country = $module->actionParameter( 'Country' );
}
elseif ( isset( $Params['Country'] ) )
{
    $country = $Params['Country'];
}
else
{
    $country = null;
}

if ( $country !== null )
{
    eZShopFunctions::setPreferredUserCountry( $country );
    eZDebug::writeNotice( "Set user country to <$country>" );
}
else
{
    eZDebug::writeWarning( "No country chosen to set." );
}

eZRedirectManager::redirectTo( $module, false );

?>
