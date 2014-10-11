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

class XS_GitHub_Repository_List
{
    const CACHE_FILE            = 'tmp/github-cache-%s.json';
    const CACHE_TTL             = 3600;
    const SOCKET_TIMEOUT        = 2;
    const GITHUB_HOST           = 'api.github.com';
    const GITHUB_PORT           = 443;
    const GITHUB_CLIENT_ID      = '0b623b567e994b6c500b';
    const GITHUB_CLIENT_SECRET  = 'dfb4f4dfef8bf64c72fa2b7a74ccd41af391e16a';
    const GITHUB_USER_AGENT     = 'XS-Labs';
    
    protected $_user    = '';
    protected $_data    = array();
    protected $_lang    = NULL;
    protected $_ignored = array();
    
    public function __construct( $user, $ignored = array() )
    {
        $this->_user    = ( string )$user;
        $this->_lang    = XS_Language_File::getInstance( get_class( $this ) );
        $this->_ignored = $ignored;
    }
    
    public function __toString()
    {
        $list            = new XS_Xhtml_Tag( 'div' );
        $list[ 'class' ] = 'xs-github-repository-list';
        
        $this->_getData();
        
        if( count( $this->_data ) == 0 )
        {
            $alert              = $list->div;
            $alert[ 'class' ]   = 'alert alert-info text-center';
            
            $list->addTextData( $this->_lang->noData );
        }
        else
        {
            $i = 0;
            
            foreach( $this->_data as $key => $value )
            {
                if( $i % 3 == 0 )
                {
                    $group            = $list->div;
                    $group[ 'class' ] = 'row';
                }
                
                if( in_array( $value->name, $this->_ignored ) )
                {
                    continue;
                }
                
                $repos              = $group->div;
                $repos[ 'class' ]   = 'col-sm-4';
                
                $panel              = $repos->div;
                $panel[ 'class' ]   = 'panel panel-default';
                
                $heading            = $panel->div;
                $heading[ 'class' ] = 'panel-heading';
                
                $body               = $panel->div;
                $body[ 'class' ]    = 'panel-body';
                
                $link               = $heading->h3->a;
                $link[ 'href' ]     = $value->html_url;
                
                $link->addTextData( $value->name );
                
                $body->p            = ( strlen( $value->description ) ) ? $value->description : $this->_lang->noDesc;
                $button             = $body->p;
                $button[ 'class' ]  = 'text-center';
                $show               = $button->a;
                $show[ 'href' ]     = $value->html_url;
                $show[ 'class' ]    = 'btn btn-default';
                
                $show->addTextData( $this->_lang->showRepos );
                
                $i++;
            }
        }
        
        return ( string )$list;
    }
    
    protected function _getData()
    {
        $time     = time();
        $cache    = __ROOTDIR__ . DIRECTORY_SEPARATOR . self::CACHE_FILE;
        $cache    = sprintf( $cache, $this->_user );
        $cacheDir = dirname( $cache );
        
        if( !file_exists( $cache ) && !is_writable( $cacheDir ) )
        {
            throw new XS_GitHub_Repository_Exception
            (
                'The cache directory is not writeable (path: ' . $cacheDir . ')',
                XS_GitHub_Repository_Exception::EXCEPTION_CACHE_DIR_NOT_WRITEABLE
            );
            
        }
        elseif( file_exists( $cache ) && !is_writable( $cache ) )
        {
            throw new XS_GitHub_Repository_Exception
            (
                'The cache file is not writeable (path: ' . $cache . ')',
                XS_GitHub_Repository_Exception::EXCEPTION_CACHE_FILE_NOT_WRITEABLE
            );
        }
        
        if( file_exists( $cache ) && $time < filemtime( $cache ) + self::CACHE_TTL )
        {
            $this->_data = json_decode( file_get_contents( $cache ) );
            
            return;
        }
        
        $errNo       = 0;
        $errStr      = '';
        $connect     = @fsockopen( 'ssl://' . self::GITHUB_HOST, self::GITHUB_PORT, $errNo, $errStr, self::SOCKET_TIMEOUT );
        $this->_data = array();
        
        if( !$connect )
        {
            return;
        }
        
        $url  = 'https://'
              . self::GITHUB_HOST
              . '/users/'
              . $this->_user
              . '/repos?sort=pushed&direction=desc&per_page=100&client_id='
              . self::GITHUB_CLIENT_ID
              . '&client_secret='
              . self::GITHUB_CLIENT_SECRET;
        $nl   = chr( 13 ) . chr( 10 );
        $req  = 'GET ' . $url . ' HTTP/1.1' . $nl
              . 'Host: ' . self::GITHUB_HOST . $nl
              . 'User-Agent: ' . self::GITHUB_USER_AGENT . $nl
              . 'Connection: Close' . $nl . $nl;
        
        fwrite( $connect, $req );
        
        $response    = '';
        $headersSent = false;
        $status      = substr( fgets( $connect, 128 ), -8, 6 );
        
        if( $status !== '200 OK' )
        {
            return;
        }
        
        $data = '';
        
        while( !feof( $connect ) )
        {
           $line = fgets( $connect, 128 );
        
           if( $headersSent )
           {
               $data .= $line;
           }
        
           if( $line === $nl )
           {
               $headersSent = true;
           }
        }
        
        $this->_data = json_decode( $data );
        
        file_put_contents( $cache, $data );
    }
} 
