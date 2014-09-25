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

final class XS_Utils
{
    private static $_instance = NULL;
    
    private function __construct()
    {}
    
    public function __clone()
    {
        throw new Exception( 'Class ' . __CLASS__ . ' cannot be cloned' );
    }
    
    public static function getInstance()
    {
        if( !is_object( self::$_instance ) ) {
            
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
    
    private function _cryptCharCode( $charCode, $start, $end, $offset )
    {
        $charCode += $offset;
        
        if( $offset > 0 && $charCode > $end ) {
            
            $charCode = $start + ( $charCode - $end - 1 );
            
        } else if ( $offset < 0 && $charCode < $start ) {
            
            $charCode = $end - ( $start - $charCode - 1 );
        }
        
        return $charCode;
    }
    
    public function cryptEmail( $email, $reverse = false )
    {
        $crypt = '';
        
        for( $i = 0; $i < strlen( $email ); $i++ ) {
            
            $charValue = substr( $email, $i, $i + 1 );
            $charCode  = ord( $charValue );
            
            if( $charCode >= 33 && $charCode <= 126 ) {
                    
                $offset    = ( $reverse ) ? -10 : 10;
                $charValue = chr( $this->_cryptCharCode( $charCode, 33, 126, $offset ) );
            }
            
            $crypt .= $charValue;
        }
        
        return $crypt;
    }
    
    public function mailTo( $email )
    {
        return '<a href="javascript:decryptEmail(\'' . $this->cryptEmail( $email ) . '\')">' . str_replace( '@', '(at)', $email ) . '</a>';
    }
    
    public function mailToLink( $email )
    {
        return 'javascript:decryptEmail(\'' . $this->cryptEmail( $email ) . '\')';
    }
}
