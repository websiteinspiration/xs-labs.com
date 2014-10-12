<?php

################################################################################
# Copyright (c) 2010, Jean-David Gadina - XS-Labs                              #
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

namespace XS\OAuth;

class Authentication
{
    const CONNECTION_SOCKET   = 0x01;
    const CONNECTION_CURL     = 0x02;
    
    const SIGNATURE_PLAINTEXT = 'PLAINTEXT';
    const SIGNATURE_RSA_SHA1  = 'RSA-SHA1';
    const SIGNATURE_HMAC_SHA1 = 'HMAC-SHA1';
    
    protected $_connectionMethod = self::CONNECTION_SOCKET;
    protected $_signatureMethod  = self::SIGNATURE_HMAC_SHA1;
    
    protected $_requestUrl              = '';
    protected $_authorizationUrl        = '';
    protected $_accessUrl               = '';
    protected $_callbackUrl             = '';
    protected $_consumerKey             = '';
    protected $_consumerSecret          = '';
    protected $_tokenSecret             = '';
    protected $_oAuthVersion            = '1.0';
    protected $_requestToken            = '';
    protected $_accessToken             = '';
    protected $_urlParams               = array();
    protected $_additionnalResponseData = array();
    protected $_callbackConfirmed       = false;
    
    public function __construct( $consumerKey, $consumerSecret )
    {
        $this->_consumerKey    = ( string )$consumerKey;
        $this->_consumerSecret = ( string )$consumerSecret;
    }
    
    protected function _createSignature( $method, $url, array $params )
    {
        $paramsString = '';
        $params       = array_merge( $this->_urlParams, $params );
        
        ksort( $params );
        
        foreach( $params as $key => $value )
        {
            $paramsString .= $key . '=' . $this->_encode( $value ) . '&';
        }
        
        $paramsString = $this->_encode( substr( $paramsString, 0, -1 ) );
        
        $base = $method . '&' . $this->_encode( $url ) . '&' . $paramsString;
        
        switch( $this->_signatureMethod )
        {
            case self::SIGNATURE_PLAINTEXT:
                
                return $this->_createSignaturePlainText( $base );
            
            case self::SIGNATURE_RSA_SHA1:
                
                return $this->_createSignatureRsaSha1( $base );
            
            default:
                
                return $this->_createSignatureHmacSha1( $base );
        }
    }
    
    protected function _createSignaturePlainText( $base )
    {}
    
    protected function _createSignatureRsaSha1( $base )
    {}
    
    protected function _createSignatureHmacSha1( $base )
    {
        $key = rawurlencode( $this->_consumerSecret ) . '&' . rawurlencode( $this->_tokenSecret );
        
        return base64_encode( hash_hmac( 'sha1', $base, $key, true ) );
    }
    
    protected function _generateNonceParameter()
    {
        return md5( uniqid( microtime(), true ) );
    }
    
    protected function _encode( $value )
    {
        return rawurlencode( utf8_encode( $value ) );
    }
    
    protected function _getRequest( $url, array $params = array() )
    {
        if( $this->_connectionMethod === self::CONNECTION_CURL )
        {
            return $this->_getRequestCurl( $url, array_merge( $this->_urlParams, $params ) );
        }
        else
        {
            return $this->_getRequestSocket( $url, array_merge( $this->_urlParams, $params ) );
        }
    }
    
    protected function _queryString( array $params )
    {
        if( !count( $params ) )
        {
            return '';
        }
        
        $query = '?';
        
        foreach( $params as $key => $value )
        {
            $query .= $key . '=' . rawurlencode( $value ) . '&';
        }
        
        return substr( $query, 0, -1 );
    }
    
    protected function _getRequestSocket( $url, array $params )
    {
        $urlInfos = parse_url( $url );
        $connect  = fsockopen( $urlInfos[ 'host' ], 80 );
        
        if( !$connect )
        {
            throw new XS\OAuth\Authentication\Exception
            (
                'Unable to establish a socket connection',
                \XS\OAuth\Authentication\Exception::EXCEPTION_NO_SOCKET
            );
        }
        
        $nl   = chr( 13 ) . chr( 10 );
        $req  = 'GET ' . $urlInfos[ 'path' ] . $this->_queryString( $params ) . ' HTTP/1.1' . $nl
              . 'Host: ' . $urlInfos[ 'host' ] . $nl
              . 'Connection: Close' . $nl . $nl;
        
        fwrite( $connect, $req );
        
        $response    = '';
        $headersSent = false;
        $statusLine  = fgets( $connect, 128 );
        $status      = substr( $statusLine, -8, 6 );
        
        if( $status !== '200 OK' )
        {
            return '';
        }
        
        while( !feof( $connect ) )
        {
            $line = fgets( $connect, 128 );
            
            if( $headersSent )
            {
                $response .= $line;
            }
            
            if( $line === $nl )
            {
                $headersSent = true;
            }
        }
        
        return $response;
    }
    
    protected function _getRequestCurl( $url, array $params )
    {}
    
    protected function _postRequest( $url, array $params )
    {
        if( $this->_connectionMethod === self::CONNECTION_CURL )
        {
            return $this->_postRequestCurl( $url, array_merge( $this->_urlParams, $params ) );
        }
        else
        {
            return $this->_postRequestSocket( $url, array_merge( $this->_urlParams, $params ) );
        }
    }
    
    protected function _postRequestSocket( $url, array $params )
    {
        $urlInfos = parse_url( $url );
        $connect  = fsockopen( $urlInfos[ 'host' ], 80 );
        
        if( !$connect )
        {
            throw new XS\OAuth\Authentication\Exception
            (
                'Unable to establish a socket connection',
                XS\OAuth\Authentication\Exception::EXCEPTION_NO_SOCKET
            );
        }
        
        $data = $this->_queryString( $params );
        
        if( $data )
        {
            $data = substr( $data, 1 );
        }
        
        $nl   = chr( 13 ) . chr( 10 );
        $req  = 'POST ' . $urlInfos[ 'path' ] . ' HTTP/1.1' . $nl
              . 'Host: ' . $urlInfos[ 'host' ] . $nl
              . 'Content-Length: ' . strlen( $data ) . $nl
              . 'Content-Type: application/x-www-form-urlencoded' . $nl
              . 'Connection: Close' . $nl . $nl
              . $data . $nl . $nl;
        
        fwrite( $connect, $req );
        
        $response    = '';
        $headersSent = false;
        $statusLine  = fgets( $connect, 128 );
        $status      = substr( $statusLine, -8, 6 );
        
        if( $status !== '200 OK' )
        {
            return '';
        }
        
        while( !feof( $connect ) )
        {
            $line = fgets( $connect, 128 );
            
            if( $headersSent )
            {
                $response .= $line;
            }
            
            if( $line === $nl )
            {
                $headersSent = true;
            }
        }
        
        return $response;
    }
    
    protected function _postRequestCurl( $url, array $params )
    {}
    
    public function getRequestToken()
    {
        if( $this->_requestToken )
        {
            return $this->_requestToken;
        }
        
        $params = array
        (
            'oauth_consumer_key'     => $this->_consumerKey,
            'oauth_signature_method' => $this->_signatureMethod,
            'oauth_timestamp'        => time(),
            'oauth_nonce'            => $this->_generateNonceParameter(),
            'oauth_version'          => $this->_oAuthVersion,
            'oauth_callback'         => $this->_callbackUrl
        );
        
        $params[ 'oauth_signature' ] = $this->_createSignature( 'GET', $this->_requestUrl, $params );
        $response                    = $this->_getRequest( $this->_requestUrl, $params );
        $responseParts               = explode( '&', $response );
        $responseParameters          = array();
        
        foreach( $responseParts as $part )
        {
            $partSplit = explode( '=', $part );
            
            if( isset( $partSplit[ 0 ] ) && isset( $partSplit[ 1 ] ) )
            {
                $responseParameters[ $partSplit[ 0 ] ] = $partSplit[ 1 ];
            }
        }
        
        if( isset( $responseParameters[ 'oauth_token' ] ) )
        {
            $this->_requestToken = $responseParameters[ 'oauth_token' ];
        }
        
        if( isset( $responseParameters[ 'oauth_token_secret' ] ) )
        {
            $this->_tokenSecret = $responseParameters[ 'oauth_token_secret' ];
        }
        
        if( isset( $responseParameters[ 'oauth_callback_confirmed' ] ) && $responseParameters[ 'oauth_callback_confirmed' ] === 'true' )
        {
            $this->_callbackConfirmed = true;
        }
        
        unset( $responseParameters[ 'oauth_token' ] );
        unset( $responseParameters[ 'oauth_token_secret' ] );
        unset( $responseParameters[ 'oauth_callback_confirmed' ] );
        
        $this->_additionnalResponseData = $responseParameters;
        
        return $this->_requestToken;
    }
    
    public function getAccessToken()
    {
        if( $this->_requestToken )
        {
            return $this->_accessToken;
        }
        
        if( !isset( $_GET[ 'oauth_verifier' ] ) || !isset( $_GET[ 'oauth_token' ] ) )
        {
            return '';
        }
        
        $verifier = $_GET[ 'oauth_verifier' ];
        $token    = $_GET[ 'oauth_token' ];
        
        $params = array
        (
            'oauth_consumer_key'     => $this->_consumerKey,
            'oauth_token'            => $token,
            'oauth_signature_method' => $this->_signatureMethod,
            'oauth_timestamp'        => time(),
            'oauth_nonce'            => $this->_generateNonceParameter(),
            'oauth_version'          => $this->_oAuthVersion,
            'oauth_callback'         => $this->_callbackUrl,
            'oauth_verifier'         => $verifier
        );
        
        $params[ 'oauth_signature' ] = $this->_createSignature( 'GET', $this->_accessUrl, $params );
        $response                    = $this->_postRequest( $this->_accessUrl, $params );
        $responseParts               = explode( '&', $response );
        $responseParameters          = array();
        
        foreach( $responseParts as $part )
        {
            $partSplit = explode( '=', $part );
            
            if( isset( $partSplit[ 0 ] ) && isset( $partSplit[ 1 ] ) )
            {
                $responseParameters[ $partSplit[ 0 ] ] = $partSplit[ 1 ];
            }
        }
        
        if( isset( $responseParameters[ 'oauth_token' ] ) )
        {
            $this->_accessToken = $responseParameters[ 'oauth_token' ];
        }
        
        if( isset( $responseParameters[ 'oauth_token_secret' ] ) )
        {
            $this->_tokenSecret = $responseParameters[ 'oauth_token_secret' ];
        }
        
        unset( $responseParameters[ 'oauth_token' ] );
        unset( $responseParameters[ 'oauth_token_secret' ] );
        
        $this->_additionnalResponseData = $responseParameters;
        
        return $this->_accessToken;
    }
    
    public function getAuthorizationHref()
    {
        return $this->_authorizationUrl . '?oauth_token=' . $this->_requestToken;
    }
    
    public function getConnectionMethod()
    {
        return $this->_connectionMethod;
    }
    
    public function setConnectionMethod( $method )
    {
        $this->_connectionMethod = ( int )$method;
    }
    
    public function getSignatureMethod()
    {
        return $this->_signatureMethod;
    }
    
    public function setSignatureMethod( $method )
    {
        $this->_signatureMethod = ( string )$method;
    }
    
    public function getRequestUrl()
    {
        return $this->_requestUrl;
    }
    
    public function setRequestUrl( $url )
    {
        $this->_requestUrl = ( string )$url;
    }
    
    public function getAuthorizationUrl()
    {
        return $this->_authorizationUrl;
    }
    
    public function setAuthorizationUrl( $url )
    {
        $this->_authorizationUrl = ( string )$url;
    }
    
    public function getAccessUrl()
    {
        return $this->_accessUrl;
    }
    
    public function setAccessUrl( $url )
    {
        $this->_accessUrl = ( string )$url;
    }
    
    public function getCallbackUrl()
    {
        return $this->_callbackUrl;
    }
    
    public function setCallbackUrl( $url )
    {
        $this->_callbackUrl = ( string )$url;
    }
    
    public function getConsumerKey()
    {
        return $this->_consumerKey;
    }
    
    public function setConsumerKey( $key )
    {
        $this->_consumerKey = ( string )$key;
    }
    
    public function getConsumerSecret()
    {
        return $this->_consumerSecret;
    }
    
    public function setConsumerSecret( $secret )
    {
        $this->_consumerSecret = ( string )$secret;
    }
    
    public function getOAuthVersion()
    {
        return $this->_oAuthVersion;
    }
    
    public function setOAuthVersion( $version )
    {
        $this->_oAuthVersion = ( string )$version;
    }
    
    public function getUrlParam( $name )
    {
        return ( isset( $this->_urlParams[ ( string )$name ] ) ) ? $this->_urlParams[ ( string )$name ] : false;
    }
    
    public function setUrlParam( $name, $value )
    {
        $this->_urlParams[ ( string )$name ] = ( string )$value;
    }
    
    public function unsetUrlParam( $name )
    {
        unset( $this->_urlParams[ ( string )$name ] );
    }
    
    public function getAdditionnalResponseData()
    {
        return $this->_additionnalResponseData;
    }
}
