<?php
function owshop_ContentActionHandler( &$module, &$http, &$objectID )
{
    if( $http->hasPostVariable("ActionAddToOWBasket") )
    {
        $owShopModule = eZModule::exists( "owshop" );

        $result = $owShopModule->run( "basket", array() );
        if ( isset( $result['content'] ) && $result['content'] )
        {
            return $result;
        }
        else
        {
            $module->setExitStatus( $owShopModule->exitStatus() );
            $module->setRedirectURI( $owShopModule->redirectURI() );
        }
    }
}
?>