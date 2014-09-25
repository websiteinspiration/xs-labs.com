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

final class XS_String_Utils
{
    private static $_instance = NULL;
    private $_asciiTable      = array();
    private $_asciiName       = array(
        'NUL', 'SOH', 'STX', 'ETX', 'EOT', 'ENQ', 'ACK', 'BEL', 'BS',  'TAB',
        'LF',  'VT',  'FF',  'CR',  'SO',  'SI',  'DLE', 'DC1', 'DC2', 'DC3',
        'DC4', 'NAK', 'SYN', 'ETB', 'CAN', 'EM',  'SUB', 'ESC', 'FS',  'GS',
        'RS',  'US',  'SPC'
    );
    
    private function __construct()
    {
        for( $i = 0; $i < 33; $i++ ) {
            
            $this->_asciiTable[ $this->_asciiName[ $i ] ] = chr( $i );
        }
        
        $this->_asciiTable[ 'NL' ] = $this->_asciiTable[ 'LF' ];
    }
    
    public function __get( $name )
    {
        return ( $this->_asciiTable[ $name ] ) ? $this->_asciiTable[ $name ] : '';
    }
    
    public function __clone()
    {
        throw new XS_Singleton_Exception( 'Class ' . __CLASS__ . ' cannot be cloned' );
    }
    
    public static function getInstance()
    {
        if( !is_object( self::$_instance ) ) {
            
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
    
    public function unifyLineBreaks( $text, $stripNull = true )
    {
        if( $stripNull ) {
            
            // Erases ASCII null characters
            $text = str_replace( $this->_asciiTable[ 'NUL' ], '', $text );
        }
        
        $text = str_replace(
            $this->_asciiTable[ 'CR' ] . $this->_asciiTable[ 'LF' ],
            $this->_asciiTable[ 'LF' ],
            $text
        );
        
        $text = str_replace(    
            $this->_asciiTable[ 'LF' ] . $this->_asciiTable[ 'CR' ],
            $this->_asciiTable[ 'LF' ],
            $text
        );
        
        $text = str_replace(
            $this->_asciiTable[ 'CR' ],
            $this->_asciiTable[ 'LF' ],
            $text
        );
        
        return $text;
    }
    
    public function strToList( $str, $sep = ',', $listType = 'ul' )
    {
        $items = explode( $sep, $str );
        $list  = new tx_oop_Xhtml_Tag( $listType );
        
        foreach( $items as $item ) {
            
            $list->li = trim( $item );
        }
        
        return $list;
    }
    
    function crop( $str, $chars, $endString = '...', $crop2space = true, $stripTags = true )
    {
        if( strlen( $str ) < $chars ) {
            
            return $str;
        }
        
        if( $stripTags ) {
            
            $str = strip_tags( $str );
        }
        
        if( strlen( $str ) < $chars ) {
            
            return $str;
            
        } else {
            
            $str = substr( $str, 0, $chars );
            
            if( $crop2space && strstr( $str, ' ' ) ) {
                
                $cropPos = strrpos( $str, ' ' );
                $str     = substr( $str, 0, $cropPos );
            }
            
            return $str . $endString;
        }
    }
}
