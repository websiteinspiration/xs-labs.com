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

final class XS_Blog
{
    private static $_instance   = NULL;
    
    protected $_lang    = NULL;
    protected $_posts   = NULL;
    
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
        $this->_lang = XS_Language_File::getInstance( get_class( $this ) );
        $menuPath    = __ROOTDIR__ . DIRECTORY_SEPARATOR . 'blog' . DIRECTORY_SEPARATOR . 'posts.xml';
        
        if( file_exists( $menuPath ) )
        {
            $this->_posts = simplexml_load_file( $menuPath );
        }
    }
    
    public function __clone()
    {
        throw new Exception( 'Class ' . __CLASS__ . ' cannot be cloned' );
    }
    
    public function __toString()
    {
        if( $this->_posts == NULL || !isset( $this->_posts->post ) )
        {
            return '';
        }
        
        $post = $this->_getPostContent();
        
        if( !empty( $post ) )
        {
            return $post;
        }
        else
        {
            return ( string )( $this->getPosts() );
        }
    }
    
    public function getPosts()
    {
        $i = 0;
        
        $posts              = new XS_Xhtml_Tag( 'div' );
        $posts[ 'class' ]   = 'marketing';
        
        foreach( $this->_posts->post as $post )
        {
            if( !isset( $post->title ) || !isset( $post->name ) || !isset( $post->date ) )
            {
                continue;
            }
            
            $path = __ROOTDIR__ . DIRECTORY_SEPARATOR . 'blog' . DIRECTORY_SEPARATOR . str_replace( '/', DIRECTORY_SEPARATOR, $post->date ) . DIRECTORY_SEPARATOR . $post->name . DIRECTORY_SEPARATOR;
            
            if( !file_exists( $path ) || !is_dir( $path ) || !file_exists( $path . 'index.html' ) )
            {
                continue;
            }
            
            if( $i % 3 == 0 )
            {
                $row            = $posts->div;
                $row[ 'class' ] = 'row';
            }
            
            $col            = $row->div;
            $col[ 'class' ] = 'col-sm-4';
            
            if( file_exists( $path . 'image.png' ) )
            {
                $imgLink            = $col->div->a;
                $imgLink[ 'href' ]  = $this->_getPostUrl( $post );
                $imgLink[ 'title' ] = $post->title;
                $img                = $imgLink->img;
                $img[ 'class' ]     = 'img-circle';
                $img[ 'width' ]     = 140;
                $img[ 'height' ]    = 140;
                $img[ 'alt' ]       = $post->title;
                $img[ 'src' ]       = '/blog/' . $post->date . '/' . $post->name . '/image.png';
            }
            
            $col->h3 = $post->title;
            $col->p  = $this->_getPostAbstract( $post );
            
            $btn            = $col->p->a;
            $btn[ 'class' ] = 'btn btn-default';
            $btn[ 'href' ]  = $this->_getPostUrl( $post );;
            
            $btn->addTextData( $this->_lang->readArticle );
            
            $i++;
        }
        
        return ( string )$posts;
    }
    
    protected function _getPostUrl( SimpleXMLElement $post )
    {
        $time = strtotime( $post->date );
        $url  = XS_Menu::getInstance()->getPageURL( 'blog' );
        
        return $url . strftime( '%Y/%m/%d', $time ) . '/' . $post->name . '/';
    }
    
    protected function _getPostAbstract( SimpleXMLElement $post )
    {
        $path = __ROOTDIR__ . DIRECTORY_SEPARATOR . 'blog' . DIRECTORY_SEPARATOR . str_replace( '/', DIRECTORY_SEPARATOR, $post->date ) . DIRECTORY_SEPARATOR . $post->name . DIRECTORY_SEPARATOR;
        $text = file_get_contents( $path . 'index.html' );
        $text = strip_tags( $text );
        
        if( strlen( $text ) > 400 )
        {
            $text = substr( $text, 0, 400 ) . ' [...]';
        }
        
        return $text;
    }
    
    protected function _getPostContent( SimpleXMLElement $post = NULL )
    {
        $pathInfo = explode( '/', $_SERVER[ 'REQUEST_URI' ] );
    
        if( count( $pathInfo ) < 7 )
        {
            return '';
        }
    
        $year   = $pathInfo[ 3 ];
        $month  = $pathInfo[ 4 ];
        $day    = $pathInfo[ 5 ];
        $name   = $pathInfo[ 6 ];
    
        $path = __ROOTDIR__
              . DIRECTORY_SEPARATOR
              . 'blog'
              . DIRECTORY_SEPARATOR
              .$year
              . DIRECTORY_SEPARATOR
              .$month
              . DIRECTORY_SEPARATOR
              .$day
              . DIRECTORY_SEPARATOR 
              . $name
              . DIRECTORY_SEPARATOR;
        
        if( !file_exists( $path ) || !is_dir( $path ) || !file_exists( $path . 'index.html' ) )
        {
            return '';
        }
        
        if( $post === NULL )
        {
            $date = strtotime( $month . '/' . $day . '/' . $year );
        
            if( $date === 0 )
            {
                return '';
            }
        
            foreach( $this->_posts->post as $post )
            {
                if( $post->name != $name )
                {
                    continue;
                }
                
                if( $date != strtotime( $post->date ) )
                {
                    continue;
                }
                
                return $this->_getPostContent( $post );
            }
        
            return '';
        }
        
        $time = ( isset( $post->date ) ) ? strtotime( $post->date ) : 0;
        $date = strftime( '%m/%d/%Y', $time );
        
        XS_Menu::getInstance()->setPageTitle( XS_Menu::getInstance()->getPageTitle( '/blog/' ) );
        XS_Menu::getInstance()->addRootlineItem( ( $time > 0 ) ? $date . ' - ' . $post->title : $post->title, $this->_getPostUrl( $post ) );
        
        $container              = new XS_Xhtml_Tag( 'div' );
        $container[ 'class' ]   = 'row';
        $content                = $container->div;
        $details                = $container->div;
        $content[ 'class' ]     = 'col-sm-8';
        $details[ 'class' ]     = 'col-sm-4';
        
        $content->h2 = $post->title;
        
        $content->addTextData( file_get_contents( $path . 'index.html' ) );
        
        if( file_exists( $path . 'image.png' ) )
        {
            $imgDiv             = $details->p;
            $imgDiv[ 'class' ]  = 'text-center';
            $img                = $imgDiv->img;
            $img[ 'class' ]     = 'img-circle';
            $img[ 'width' ]     = 140;
            $img[ 'height' ]    = 140;
            $img[ 'alt' ]       = $post->title;
            $img[ 'src' ]       = '/blog/' . $post->date . '/' . $post->name . '/image.png';
        }
        
        $panel                  = $details->div;
        $panel[ 'class' ]       = 'panel panel-default';
        $panelBody              = $panel->div;
        $panelBody[ 'class' ]   = 'panel-body';
        
        $panelAuthorRow                 = $panelBody->div;
        $panelDateRow                   = $panelBody->div;
        $panelCategoryRow               = $panelBody->div;
        $panelCommentsRow               = $panelBody->div;
        $panelAuthorRow[ 'class' ]      = 'row';
        $panelDateRow[ 'class' ]        = 'row';
        $panelCategoryRow[ 'class' ]    = 'row';
        $panelCommentsRow[ 'class' ]    = 'row';
        
        $panelAuthorLabel               = $panelAuthorRow->div;
        $panelAuthorText                = $panelAuthorRow->div;
        $panelDateLabel                 = $panelDateRow->div;
        $panelDateText                  = $panelDateRow->div;
        $panelCategoryLabel             = $panelCategoryRow->div;
        $panelCategoryText              = $panelCategoryRow->div;
        $panelCommentsLabel             = $panelCommentsRow->div;
        $panelCommentsText              = $panelCommentsRow->div;
        $panelAuthorLabel[ 'class' ]    = 'col-xs-4';
        $panelAuthorText[ 'class' ]     = 'col-xs-8';
        $panelDateLabel[ 'class' ]      = 'col-xs-4';
        $panelDateText[ 'class' ]       = 'col-xs-8';
        $panelCategoryLabel[ 'class' ]  = 'col-xs-4';
        $panelCategoryText[ 'class' ]   = 'col-xs-8';
        $panelCommentsLabel[ 'class' ]  = 'col-xs-4';
        $panelCommentsText[ 'class' ]   = 'col-xs-8';
        
        $panelAuthorLabel->small->strong    = $this->_lang->author;
        $panelDateLabel->small->strong      = $this->_lang->date;
        $panelCategoryLabel->small->strong  = $this->_lang->category;
        $panelCommentsLabel->small->strong  = $this->_lang->comments;
        
        $panelAuthorText->small     = ( isset( $post->author ) ) ? $post->author : '-';
        $panelDateText->small       = ( $time > 0 ) ? $date : '-';
        $panelCategoryText->small   = '-';
        $panelCommentsText->small   = '0';
        
        $copyright = $details->div;
        
        if( ( isset( $post->author ) ) )
        {
            $copyright->small->strong = sprintf( $this->_lang->copyright, $post->author );
            
            $copyright->addTextData( '<br />' );
            
            $copyright->small = sprintf
            (
                $this->_lang->copyrightNote,
                XS_Menu::getInstance()->getPageLink( $this->_lang->copyrightLicenseLink, $this->_lang->copyrightLicense )
            );
        }
        
        return ( string )$container;
    }
}
