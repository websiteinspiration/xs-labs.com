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

class XS_Twitter_Feed
{
    const CACHE_FILE       = 'tmp/twitter-cache.xml';
    const CACHE_TTL        = 3600;
    const SOCKET_TIMEOUT   = 2;
    const TWITTER_HOST     = 'twitter.com';
    const TWITTER_PORT     = 80;
    const TWITTER_FEED_URL = '/statuses/user_timeline.xml';
    
    protected $_screenName = '';
    protected $_data       = '';
    protected $_xml        = NULL;
    protected $_error      = false;
    protected $_limit      = 0;
    protected $_lang       = NULL;
    
    public function __construct( $screenName, $limit = 10 )
    {
        $this->_lang       = XS_Language_File::getInstance( __CLASS__ );
        $this->_screenName = ( string )$screenName;
        $this->_limit      = ( int )$limit;
        
        $this->_getXmlData();
        
        if( $this->_data === '' ) {
            
            $this->_error = true;
            return;
        }
        
        try {
            
            $this->_xml = simplexml_load_string( $this->_data );
            
        } catch( Exception $e ) {
            
            $this->_error = true;
            return;
        }
    }
    
    public function __toString()
    {
        if( $this->_error === true ) {
            
            $link             = new XS_Xhtml_Tag( 'a' );
            $link[ 'href' ]   = 'http://' . self::TWITTER_HOST . '/' . $this->_screenName;
            $link[ 'title' ]  = $this->_lang->twitter . ': ' . $this->_screenName;
            
            $link->addTextData( $link[ 'href' ] );
            
            $error            = new XS_Xhtml_Tag( 'div' );
            $error[ 'class' ] = 'tweet-error';
            $error->div       = sprintf( $this->_lang->notAvailable, $this->_screenName );
            $error->div       = sprintf( $this->_lang->tryLater, $this->_screenName ) . '<br />' . $link;
            $error->div       = $this->_lang->sorry;
            
            return ( string )$error;
        }
        
        $i       = 0;
        $content = new XS_Xhtml_Tag( 'div' );
        
        foreach( $this->_xml as $status ) {
            
            if( $i === $this->_limit ) {
                
                break;
            }
            
            $tweet    = $content->div;
            $text     = $tweet->div;
            $infos    = $tweet->div;
            $user     = $infos->span;
            $sep      = $infos->span;
            $date     = $infos->span;
            $userLink = $user->a;
            
            $tweet[ 'class' ]    = ( $i % 2 ) ? 'tweet-alt' : 'tweet';
            $text[ 'class'  ]    = 'tweet-text';
            $infos[ 'class' ]    = 'tweet-infos';
            $user[ 'class' ]     = 'tweet-infos-user';
            $date[ 'class' ]     = 'tweet-infos-date';
            $userLink[ 'href' ]  = 'http://' . self::TWITTER_HOST . '/' . $status->user->screen_name;
            $userLink[ 'title' ] = $this->_lang->twitter . ': ' . $status->user->screen_name;
            
            $statusText = $status->text;
            $statusText = preg_replace_callback( '/(http|ftp)+(s?)?:(\/\/)((\w|\.)+)(\/)?(\S+)?/i', array( $this, '_replaceLinks' ), $statusText );
            $statusText = preg_replace_callback( '/@([^ ]+)/', array( $this, '_replaceTwitterNames' ), $statusText );
            $statusText = preg_replace_callback( '/#([^ ]+)/', array( $this, '_replaceTwitterTags' ),  $statusText );
            
            $text->addTextData( $statusText );
            $userLink->addTextData( $status->user->screen_name );
            $sep->addTextData( ' - ' );
            $date->addTextData( date( 'd.m.Y / H:i', strtotime( $status->created_at ) ) );
            
            $i++;
        }
        
        return ( string )$content;
    }
    
    protected function _replaceLinks( array $matches )
    {
        $link            = new XS_Xhtml_Tag( 'a' );
        $link[ 'href' ]  = $matches[ 0 ];
        $link[ 'title' ] = $matches[ 0 ];
        
        $link->addTextData( $matches[ 0 ] );
        
        return ( string )$link;
    }
    
    protected function _replaceTwitterNames( array $matches )
    {
        $link            = new XS_Xhtml_Tag( 'a' );
        $link[ 'href' ]  = 'http://' . self::TWITTER_HOST . '/' . $matches[ 1 ];
        $link[ 'title' ] = $this->_lang->twitter . ': ' . $matches[ 1 ];
        
        $link->addTextData( $matches[ 0 ] );
        
        return ( string )$link;
    }
    
    protected function _replaceTwitterTags( array $matches )
    {
        $link            = new XS_Xhtml_Tag( 'a' );
        $link[ 'href' ]  = 'http://' . self::TWITTER_HOST . '/search?q=%23' . $matches[ 1 ];
        $link[ 'title' ] = $this->_lang->twitter . ': ' . $matches[ 1 ];
        
        $link->addTextData( $matches[ 0 ] );
        
        return ( string )$link;
    }
    
    protected function _getXmlData()
    {
        $time     = time();
        $cache    = __ROOTDIR__ . DIRECTORY_SEPARATOR .self::CACHE_FILE;
        $cacheDir = dirname( $cache );
        
        if( !file_exists( $cache ) && !is_writable( $cacheDir ) ) {
            
            throw new XS_Twitter_Feed_Exception(
                'The cache directory is not writeable (path: ' . $cacheDir . ')',
                XS_Twitter_Feed_Exception::EXCEPTION_CACHE_DIR_NOT_WRITEABLE
            );
            
        } elseif( file_exists( $cache ) && !is_writable( $cache ) ) {
            
            throw new XS_Twitter_Feed_Exception(
                'The cache file is not writeable (path: ' . $cache . ')',
                XS_Twitter_Feed_Exception::EXCEPTION_CACHE_FILE_NOT_WRITEABLE
            );
        }
        
        if( file_exists( $cache ) && $time < filemtime( $cache ) + self::CACHE_TTL ) {
            
            $this->_data = file_get_contents( $cache );
            
            return;
        }
        
        $errNo   = 0;
        $errStr  = '';
        $connect = @fsockopen( self::TWITTER_HOST, self::TWITTER_PORT, $errNo, $errStr, self::SOCKET_TIMEOUT );
        
        if( !$connect ) {
            
            if( file_exists( $cache ) ) {
                
                $this->_data = file_get_contents( $cache );
            }
            
            return;
        }
        
        $url  = 'http://'
              . self::TWITTER_HOST
              . self::TWITTER_FEED_URL
              . '?screen_name='
              . urlencode( $this->_screenName );
        $nl   = chr( 13 ) . chr( 10 );
        $req  = 'GET ' . $url . ' HTTP/1.1' . $nl
             . 'Host: ' . self::TWITTER_HOST . $nl
             . 'Connection: Close' . $nl . $nl;
        
        fwrite( $connect, $req );
        
        $response    = '';
        $headersSent = false;
        $status      = substr( fgets( $connect, 128 ), -8, 6 );
        
        if( $status !== '200 OK' ) {
            
            return;
        }
        
        while( !feof( $connect ) ) {
        
           $line = fgets( $connect, 128 );
        
           if( $headersSent ) {
        
               $this->_data .= $line;
           }
        
           if( $line === $nl ) {
        
               $headersSent = true;
           }
        }
        
        file_put_contents( $cache, $this->_data );
    }
}
