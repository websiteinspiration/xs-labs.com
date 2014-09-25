<?php

################################################################################
# Copyright (c) 2010, Jean-David Gadina <macmade@xs-labs.com>                  #
# All rights reserved.                                                         #
#                                                                              #
# Redistribution and use in source and binary forms, with or without           #
# modification, are permitted provided that the following conditions are met:  #
#                                                                              #
#  -   Redistributions of source code must retain the above copyright notice,  #
#      this list of conditions and the following disclaimer.                   #
#  -   Redistributions in binary form must reproduce the above copyright       #
#      notice, this list of conditions and the following disclaimer in the     #
#      documentation and/or other materials provided with the distribution.    #
#  -   Neither the name of 'Jean-David Gadina' nor the names of its            #
#      contributors may be used to endorse or promote products derived from    #
#      this software without specific prior written permission.                #
#                                                                              #
# THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"  #
# AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE    #
# IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE   #
# ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE    #
# LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR          #
# CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF         #
# SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS     #
# INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN      #
# CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)      #
# ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE   #
# POSSIBILITY OF SUCH DAMAGE.                                                  #
################################################################################

# $Id$

final class XS_Download_Utils
{
    const DATE_FORMAT = 'd.m.Y';
    
    private static $_instance  = NULL;
    private $_directory        = '';
    
    private function __construct()
    {
        $this->_directory = __ROOTDIR__
                          . DIRECTORY_SEPARATOR
                          . 'downloads'
                          . DIRECTORY_SEPARATOR;
    }
    
    public function __clone()
    {
        throw new XS_Singleton_Exception
        (
            'Class ' . __CLASS__ . ' cannot be cloned',
            XS_Singleton_Exception::EXCEPTION_CLONE
        );
    }
    
    public static function getInstance()
    {
        if( !is_object( self::$_instance ) )
        {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
    
    protected function _getSize( $size )
    {
        if( $size < 1000 )
        {
            $size = $size;
            $unit = 'B';
        }
        elseif( $size < ( 1000 * 1000 ) )
        {
            $size = ( $size / 1000 );
            $unit = 'KB';
        }
        elseif( $size < ( 1000 * 1000 * 1000 ) )
        {
            $size = ( ( $size / 1000 ) / 1000 );
            $unit = 'MB';
        }
        elseif( $size < ( 1000 * 1000 * 1000 * 1000 ) )
        {
            $size = ( ( ( $size / 1000 ) / 1000 ) / 1000 );
            $unit = 'GB';
        }
        else
        {
            return '';
        }
        
        return round( $size, 2 ) . ' ' . $unit;
    }
    
    public function getSimpleDownloadLink( $file, $label )
    {
        $span = new XS_Xhtml_Tag( 'span' );
        $path = $this->_directory . str_replace( '/', DIRECTORY_SEPARATOR, $file );
        
        if( !file_exists( $path ) )
        {
            $span->span       = $label . ' (File unavailable)<br />';
            
            return $span;
        }
        
        $link = $span->a;
        $href = '/downloads/' . $file;
        $size = filesize( $path );
        
        $link[ 'href' ]  = $href;
        $link[ 'title' ] = $label;
        
        $link->addTextData( $label );
        
        $span->span     = ' - ' . $this->_getSize( $size ) . ' (' . date( self::DATE_FORMAT, filemtime( $path ) ) . ')<br />';
        
        return $span;
    }
    
    public function getDownloadLink( $file, $label )
    {
        $path           = $this->_directory . str_replace( '/', DIRECTORY_SEPARATOR, $file );
        $div            = new XS_Xhtml_Tag( 'div' );
        $div[ 'class' ] = 'download';
        
        if( !file_exists( $path ) )
        {
            $div->span        = $label . '<br />';
            $error            = $div->span;
            $error[ 'class' ] = 'grey';
            
            $error->addTextData( 'File unavailable' );
            
            return $div;
        }
        
        $link = $div->a;
        $href = '/downloads/' . $file;
        $size = filesize( $path );
        $sum  = md5_file( $path );
        
        $link[ 'href' ]  = $href;
        $link[ 'title' ] = $label;
        
        $link->addTextData( $label );
        
        $div->span      = ' - ' . $this->_getSize( $size ) . ' (' . date( self::DATE_FORMAT, filemtime( $path ) ) . ')<br />';
        $md5            = $div->span;
        $md5[ 'class' ] = 'grey';
        
        $md5->addTextData( '(MD5: ' . $sum . ')' );
        
        return $div;
    }
}
