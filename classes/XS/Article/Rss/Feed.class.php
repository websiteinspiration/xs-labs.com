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

class XS_Article_Rss_Feed extends XS_Rss_Feed
{
    protected $_layout = NULL;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->_layout = XS_Layout::getInstance();
    }
    
    public function getPages( $path )
    {
        
        $path    = ( string )$path;
        $path    = ( substr( $path, 0, 1 )  === '/' ) ? $path : '/' . $path;
        $path    = ( substr( $path, -1, 1 ) === '/' ) ? $path : $path . '/';
        $absPath = __ROOTDIR__ . DIRECTORY_SEPARATOR . $this->_lang . str_replace( '/', DIRECTORY_SEPARATOR, $path );
        
        if( !file_exists( $absPath ) || !is_dir( $absPath ) )
        {
            return;
        }
        
        $pathInfos = explode( '/', $_SERVER[ 'REQUEST_URI' ] );
        
        array_shift( $pathInfos );
        array_pop( $pathInfos );
        
        $lang     = ( isset( $pathInfos[ 0 ] ) ) ? $pathInfos[ 0 ] : 'en';
        $menuPath = __ROOTDIR__ . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'menu.' . $lang . '.xml';
        
        if( !file_exists( $menuPath ) ) {
            
            $lang     = 'en';
            $menuPath = __ROOTDIR__ . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'menu.' . $lang . '.xml';
        }
        
        $menu = simplexml_load_file( $menuPath );
        
        $sections = explode( '/', $path );
        
        array_shift( $sections );
        array_pop( $sections );
        
        foreach( $sections as $section )
        {
            if( !isset( $menu->$section ) )
            {
                return;
            }
            
            $menu = $menu->$section;
            
            if( !isset( $menu->sub ) )
            {
                return;
            }
            
            $menu = $menu->sub;
        }
        
        $items    = array();
        $iterator = new DirectoryIterator( $absPath );
        
        foreach( $iterator as $file )
        {
            if( ( string )$file === '.' || ( string )$file === '..' )
            {
                continue;
            }
            
            if( !isset( $menu->$file ) )
            {
                continue;
            }
            
            $menuItem = $menu->$file;
            
            if( isset( $menuItem[ 'preview' ] ) )
            {
                continue;
            }
            
            $index = $absPath . $file . DIRECTORY_SEPARATOR . 'index.php';
            
            if( file_exists( $index ) )
            {
                $content = $this->_layout->getContent( $index );
                $matches = array();
                
                preg_match( '/<!-- RSS_ABSTRACT_BEGIN -->(.+)<!-- RSS_ABSTRACT_END -->/s', $content, $matches );
                
                if( !isset( $matches[ 1 ] ) )
                {
                    continue;
                }
                
                $mDate                 = filemtime( $index );
                $key                   = $mDate . '-' . md5( uniqid( time(), true ) );
                $item                  = array();
                $item[ 'title' ]       = ( string )$this->_menu->getPageTitle( $path . $file . '/', 10 );
                $item[ 'link' ]        = $this->_menu->getPageUrl( $path . $file . '/' );
                $item[ 'pubDate' ]     = date( 'r', $mDate );
                $item[ 'description' ] = $matches[ 1 ];
                $items[ $key ]         = $item;
            }
        }
        
        krsort( $items );
        
        foreach( $items as $itemProperties )
        {
            $item = $this->_rss->channel->addChild( 'item' );
            
            foreach( $itemProperties as $key => $value )
            {
                $item->$key = ( string )$value;
            }
        }
    }
}
