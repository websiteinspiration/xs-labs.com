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

class XS_Bitly_Url_Shortener
{
    const SHORTEN_API_HOST = 'api.bit.ly';
    const SHORTEN_API_URL  = '/shorten';
    const SHORTEN_API_PORT = 80;
    const HTTP_VERSION     = '1.1';
    const SOCK_TIMEOUT     = 2;
    
    protected $_login   = '';
    protected $_apiKey  = '';
    protected $_version = '2.0.1';
    protected $_format  = 'xml';
    
    public function __construct( $login, $apiKey, $version = '2.0.1', $format = 'xml' )
    {
        $this->_login   = ( string )$login;
        $this->_apiKey  = ( string )$apiKey;
        $this->_version = ( string )$version;
        $this->_format  = ( string )$format;
    }
    
    public function getVersion()
    {
        return $this->_version;
    }
    
    public function getFormat()
    {
        return $this->_format;
    }
    
    public function setVersion( $version )
    {
        $this->_version = ( string )$version;
    }
    
    public function setFormat( $format )
    {
        $this->_format = ( string )$format;
    }
    
    public function shorten( $url )
    {
        $shorten = self::SHORTEN_API_URL
                 . '?version='
                 . $this->_version
                 . '&apiKey='
                 . $this->_apiKey
                 . '&login='
                 . $this->_login
                 . '&format='
                 . $this->_format
                 . '&longUrl='
                 . urlencode( ( string )$url );
        
        $errno  = 0;
        $errstr = '';
        $nl     = chr( 13 ) . chr( 10 );
        $sock   = @fsockopen
        (
            self::SHORTEN_API_HOST,
            self::SHORTEN_API_PORT,
            $errno,
            $errstr,
            self::SOCK_TIMEOUT
        );
        
        if( !$sock )
        {
            return $url;
        }
        
        $req = 'GET ' . $shorten . ' HTTP/' . self::HTTP_VERSION . $nl
             . 'Host: ' . self::SHORTEN_API_HOST . $nl
             . 'Connection: close' . $nl . $nl;
        
        fwrite( $sock, $req );
        
        $response    = '';
        $headersSent = false;
        $status      = substr( fgets( $sock, 128 ), -8, 6 );
        
        if( $status !== '200 OK' )
        {
           return $url;
        }
        
        while( !feof( $sock ) )
        {
           $line = fgets( $sock, 128 );
        
           if( $headersSent )
           {
               $response .= $line;
           }
        
           if( $line === $nl )
           {
               $headersSent = true;
           }
        }
        
        if( !$response )
        {
            return $url;
        }
        
        try
        {
            $xml = @simplexml_load_string( $response );
            
            if( $xml->errorCode != 0 )
            {
                return $url;
            }
            
            return ( string )$xml->results->nodeKeyVal->shortUrl;
            
        }
        catch( Exception $e )
        {
            return $url;
        }
    }
}
