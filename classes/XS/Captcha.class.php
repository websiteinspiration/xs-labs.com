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

final class XS_Captcha
{
    const RECAPTCHA_API_SERVER          = 'http://www.google.com/recaptcha/api';
    const RECAPTCHA_API_SECURE_SERVER   = 'https://www.google.com/recaptcha/api';
    const RECAPTCHA_VERIFY_SERVER       = 'www.google.com';
    
    private static $_instance   = NULL;
    private        $_publicKey  = '6LeXgPsSAAAAAM55inPekSBO0-zwo88JX6ZprgSi';
    
    public static function getInstance()
    {
        if( !is_object( self::$_instance ) )
        {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
    
    private function __construct()
    {
        
    }
    
    public function __clone()
    {
        throw new Exception( 'Class ' . __CLASS__ . ' cannot be cloned' );
    }
    
    public function __toString()
    {
        return ( string )( $this->getCapchta() );
    }
    
    public function getCapchta()
    {
        $div = new XS_Xhtml_Tag( 'div' );
        
        if( empty( $this->_publicKey ) )
        {
            return $div;
        }
        
        $script     = $div->script;
        $noscript   = $div->noscript;
        $iframe     = $noscript->iframe;
        
        $noscript->br;
        
        $textarea   = $noscript->textarea;
        
        $noscript->br;
        
        $input      = $noscript->input;
        
        $script[ 'type' ]   = 'text/javascript';
        $script[ 'src' ]    = self::RECAPTCHA_API_SERVER . '/challenge?k=' . $this->_publicKey;
        
        $iframe[ 'src' ]            = self::RECAPTCHA_API_SERVER . '/noscript?k=' . $this->_publicKey;
        $iframe[ 'width' ]          = 500;
        $iframe[ 'height' ]         = 300;
        $iframe[ 'frameborder' ]    = 0;
        
        $textarea[ 'name' ]     = 'recaptcha_challenge_field';
        $textarea[ 'rows' ]     = 3;
        $textarea[ 'cols' ]     = 40;
        
        $textarea[ 'type' ]     = 'hidden';
        $textarea[ 'name' ]     = 'recaptcha_response_field';
        $textarea[ 'value' ]    = 'manual_challenge';
        
        return $div;
    }
    
    private function _encodeQueryString( $data )
    {
        $req = '';
        
        foreach( $data as $key => $value )
        {
            $req .= $key . '=' . urlencode( stripslashes( $value ) ) . '&';
        }
        
        $req = substr( $req, 0, strlen( $req ) - 1 );
        
        return $req;
    }
    
    private function _sendHTTPPost( $host, $path, $data, $port = 80 )
    {
        $req        = $this->_encodeQueryString( $data );
        $nl         = chr( 10 ) . chr( 13 );
        $request    = 'POST ' . $path . ' HTTP/1.0' . $nl
                    . 'Host: ' . $host . $nl
                    . 'Content-Type: application/x-www-form-urlencoded;' . $nl
                    . 'Content-Length: ' . strlen( $req ) . $nl
                    . 'User-Agent: reCAPTCHA/PHP' . $nl
                    . $nl
                    . $req;
        
        if( $fs = @fsockopen( $host, $port, $errno, $errstr, 10 ) ) === false )
        {
            return array();
        }
        
        fwrite( $fs, $request );
        
        while( !feof( $fs ) )
        {
            $response .= fgets( $fs, 1160 );
        }
        
        fclose( $fs );
        
        return explode( "\r\n\r\n", $response, 2 );
}
}
