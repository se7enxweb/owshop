<?php

class OWShopOrderExport {

    static function createDirectory( $dir ) {
        if ( !is_dir( $dir ) ) {
            mkdir( $dir, 0777, TRUE );
        }
    }

    static function removeFile( $file ) {
        if ( !file_exists( $file ) ) {
            return true;
        }
        if ( !is_dir( $file ) || is_link( $file ) ) {
            return unlink( $file );
        }
        foreach( scandir($file) as $item ) {
            if ( $item == '.' || $item == '..' ) {
                continue;
            }
            if( !self::removeFile( $file . "/" . $item ) ) {
                chmod( $file . "/" . $item, 0777 );
                if ( !self::removeFile( $file . "/" . $item ) ) {
                    return false;
                }
            }
        }
        return rmdir( $file );
    }

    static function getFile( $orderIDArray ) {
        $mainTmpDir = eZSys::cacheDirectory() . '/owshop/';
        $tmpDir = $mainTmpDir . time() . '/';
        OWShopOrderExport::createDirectory( $tmpDir );
        $filename = self::generateSafeFileName( 'orders.csv' );
        $filepath = $tmpDir . $filename;
        @unlink( $filepath );
        eZFile::create( $filepath, false, self::getDatas( $orderIDArray ) );
        return $filepath;
    }

    static function getDatas( $orderIDArray ) {
        $fh = fopen( 'php://temp', 'rw' );
        foreach ( $orderIDArray as $index => $orderID ) {
            $order = eZOrder::fetch( $orderID );
            $user = $order->attribute( 'user' );
            $orderDate = $order->attribute( 'created' );
            $orderRow = array(
                'id' => $orderID,
                'date' => date( 'Y-m-d H:i', $orderDate ),
                'login' => $user->attribute( 'login' )
            );
            $accountInformation = $order->attribute( 'account_information' );
            foreach ( $accountInformation['field_list']['all'] as $field ) {
                $orderRow[$field] = $accountInformation['account_info'][$field];
            }
            foreach ( $order->attribute( 'product_items' ) as $product ) {
                $row = $orderRow;
                $row['product_name'] = $product['object_name'];
                $row['quantity'] = $product['item_count'];
                $row['unit_price_inc_vat'] = $product['price_inc_vat'];
                $row['price_inc_vat'] = $product['total_price_inc_vat'];
                if ( $index == 0 ) {
                    fputcsv( $fh, array_keys( $row ) );
                }
                fputcsv( $fh, $row );
            }
        }
        rewind( $fh );
        $csv = stream_get_contents( $fh );
        fclose( $fh );
        return $csv;
    }

    static function generateSafeFileName( $name ) {
        $trans = eZCharTransform::instance();
        return $trans->transformByGroup( $name, 'filename' );
    }

}
