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

namespace XS\GitHub;

class Repository
{
    const CACHE_FILE            = 'tmp/github-cache-%s-%s.json';
    const CACHE_TTL             = 3600;
    const SOCKET_TIMEOUT        = 2;
    const GITHUB_HOST           = 'api.github.com';
    const GITHUB_PORT           = 443;
    const GITHUB_CLIENT_ID      = '0b623b567e994b6c500b';
    const GITHUB_CLIENT_SECRET  = 'dfb4f4dfef8bf64c72fa2b7a74ccd41af391e16a';
    const GITHUB_USER_AGENT     = 'XS-Labs';
    
    protected $_user  = '';
    protected $_repos = '';
    protected $_data  = array();
    protected $_lang  = NULL;
    
    public function __construct( $user, $repos )
    {
        $this->_user    = ( string )$user;
        $this->_repos   = ( string )$repos;
        $this->_lang    = \XS\Language\File::getInstance( get_class( $this ) );
    }
    
    public function commits()
    {
        $commits            = new \XS\XHTML\Tag( 'div' );
        $commits[ 'class' ] = 'xs-github-repository-commits';
        
        $this->_getData();
        
        if( count( $this->_data ) == 0 )
        {
            $alert              = $commits->div;
            $alert[ 'class' ]   = 'alert alert-info text-center';
            
            $alert->addTextData( $this->_lang->noData );
        }
        else
        {
            $i = 0;
            
            foreach( $this->_data as $key => $value )
            {
                if( $i == 0 )
                {
                    $row            = $commits->div;
                    $row[ 'class' ] = 'row';
                    
                    $i = 1;
                }
                else
                {
                    $i = 0;
                }
                
                $col                = $row->div;
                $col[ 'class' ]     = 'col-sm-6';
                
                $commit             = $col->div;
                $commit[ 'class' ]  = 'panel panel-default';
            
                $details            = $commit->div;
                $details[ 'class' ] = 'panel-heading';
                
                if( isset( $value->author->avatar_url ) )
                {
                    $div                = $details->div;
                    
                    $img                = $div->img;
                    $img[ 'src' ]       = $value->author->avatar_url;
                    $img[ 'alt' ]       = $value->commit->author->name;
                    $img[ 'width' ]     = 50;
                    $img[ 'height' ]    = 50;
                    $img[ 'class' ]     = 'pull-left img-circle';
                }
                
                $infos              = $details->div;
                $infos[ 'class' ]   = 'xs-github-repository-commit-infos';
            
                $div                = $infos->div;
                $div[ 'class' ]     = 'xs-github-repository-commit-author';
                
                if( isset( $value->author->html_url ) )
                {
                    $link               = $div->a;
                    $link[ 'href' ]     = $value->author->html_url;
            
                    $link->addTextData( $value->commit->author->name );
                }
                else
                {
                    $div->addTextData( $value->commit->author->name );
                }
                
                $div                = $infos->div;
                $div[ 'class' ]     = 'xs-github-repository-commit-date';
            
                $date = strftime( '%A, %e %B %Y - %I:%M %p', strtotime( $value->commit->author->date ) );
            
                $div->addTextData( $date );
            
                $div                = $infos->div;
                $div[ 'class' ]     = 'xs-github-repository-commit-sha';
                $link               = $div->a;
                $link[ 'href' ]     = $value->html_url;
            
                $link->addTextData( $value->sha );
            
                $div                = $commit->div;
                $div[ 'class' ]     = 'panel-body';
            
                $div->addTextData( $value->commit->message );
            }
        }
        
        return $commits;
    }
    
    protected function _getData()
    {
        $time     = time();
        $cache    = __ROOTDIR__ . DIRECTORY_SEPARATOR . self::CACHE_FILE;
        $cache    = sprintf( $cache, $this->_user, $this->_repos );
        $cacheDir = dirname( $cache );
        
        if( !file_exists( $cache ) && !is_writable( $cacheDir ) )
        {
            throw new \XS\GitHub\Repository\Exception
            (
                'The cache directory is not writeable (path: ' . $cacheDir . ')',
                \XS\GitHub\Repository\Exception::EXCEPTION_CACHE_DIR_NOT_WRITEABLE
            );
            
        }
        elseif( file_exists( $cache ) && !is_writable( $cache ) )
        {
            throw new \XS\GitHub\Repository\Exception
            (
                'The cache file is not writeable (path: ' . $cache . ')',
                \XS\GitHub\Repository\Exception::EXCEPTION_CACHE_FILE_NOT_WRITEABLE
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
              . '/repos/'
              . $this->_user
              . '/'
              . $this->_repos
              . '/commits?client_id='
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
