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
    protected $_errors  = array();
    
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
        $path        = __ROOTDIR__ . DIRECTORY_SEPARATOR . 'blog' . DIRECTORY_SEPARATOR . 'posts.xml';
        
        if( file_exists( $path ) )
        {
            $this->_posts = simplexml_load_file( $path );
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
        $i              = 0;
        $this->_errors  = array();
        
        $posts              = new XS_Xhtml_Tag( 'div' );
        $posts[ 'class' ]   = 'marketing';
        
        foreach( $this->_posts->post as $post )
        {
            if( !isset( $post->title ) )
            {
                $this->_addPostError( $post, $this->_lang->missingPostTitle );
                continue;
            }
            
            if( !isset( $post->name ) )
            {
                $this->_addPostError( $post, $this->_lang->missingPostName );
                continue;
            }
            
            if( !isset( $post->date ) )
            {
                $this->_addPostError( $post, $this->_lang->missingPostDate );
                continue;
            }
            
            if( !isset( $post->time ) )
            {
                $this->_addPostError( $post, $this->_lang->missingPostTime );
                continue;
            }
            
            $path = __ROOTDIR__ . DIRECTORY_SEPARATOR . 'blog' . DIRECTORY_SEPARATOR . str_replace( '/', DIRECTORY_SEPARATOR, $post->date ) . DIRECTORY_SEPARATOR . $post->name . DIRECTORY_SEPARATOR;
            
            if( !file_exists( $path ) || !is_dir( $path ) || !file_exists( $path . 'index.html' ) )
            {
                $this->_addPostError( $post, $this->_lang->missingPostFile );
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
            $text = substr( $text, 0, 400 ) . '...';
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
        
        $time     = ( isset( $post->date ) ) ? strtotime( $post->date . ' ' .$post->time ) : 0;
        $date     = strftime( '%m/%d/%Y', $time );
        $dateTime = strftime( '%m/%d/%Y %H:%M', $time );
        
        XS_Menu::getInstance()->setPageTitle( XS_Menu::getInstance()->getPageTitle( '/blog/' ) );
        XS_Menu::getInstance()->addRootlineItem( ( $time > 0 ) ? $date . ' - ' . $post->title : $post->title, $this->_getPostUrl( $post ) );
        
        $container              = new XS_Xhtml_Tag( 'div' );
        $container[ 'class' ]   = 'row';
        $content                = $container->div;
        $details                = $container->div;
        $content[ 'class' ]     = 'col-sm-8';
        $details[ 'class' ]     = 'col-sm-4';
        
        $content->h2 = $post->title;
        
        $html = file_get_contents( $path . 'index.html' );
        $html = str_replace
        (
            '{POST_URL}',
            'http://' . $_SERVER[ 'HTTP_HOST' ] . '/blog/' . $post->date . '/' . $post->name . '/',
            $html
        );
        
        $content->addTextData( $html );
        
        $comments = $this->_getPostComments( $post );
        
        if( $comments !== NULL )
        {
            $content->addChildNode( $comments );
        }
        
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
        $panelDateText->small       = ( $time > 0 ) ? $dateTime : '-';
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
    
    protected function _getPostComments( SimpleXMLElement $post )
    {
        $comments   = array();
        $path       = __ROOTDIR__ . DIRECTORY_SEPARATOR . 'blog' . DIRECTORY_SEPARATOR . 'comments.xml';
        
        if( !file_exists( $path ) )
        {
            return NULL;
        }
        
        if( !isset( $post->date ) )
        {
            return NULL;
        }
        
        if( !isset( $post->name ) )
        {
            return NULL;
        }
        
        $postID = $post->date . '/' . $post->name;
        $xml    = simplexml_load_file( $path );
        
        foreach( $xml->comment as $comment )
        {
            if( $postID == $comment->post )
            {
                $comments[] = $comment;
            }
        }
        
        if( count( $comments ) === 0 )
        {
            return NULL;
        }
        
        $html       = new XS_Xhtml_Tag( div );
        $html->h3   = $this->_lang->comments;
        
        foreach( $comments as $comment )
        {
            $panel = $html->div;
            
            if( XS_Crypto::getInstance()->decrypt( $comment->email ) == XS_Crypto::getInstance()->decrypt( 'wNZPcaaaOVpOu7p4ec1uTtv3F5Tlr49n3mYRLwc4WH4yCzaOzL//x0l+6NzaJGRX' ) )
            {
                $panel[ 'class' ]   = 'panel panel-warning';
            }
            else
            {
                $panel[ 'class' ]   = 'panel panel-default';
            }
            
            $heading            = $panel->div;
            $heading[ 'class' ] = 'panel-heading';
            $body               = $panel->div;
            $body[ 'class' ]    = 'panel-body';
            
            $row                    = $heading->div;
            $row[ 'class' ]         = 'row';
            $authorLabel            = $row->div;
            $authorLabel[ 'class' ] = 'col-xs-2';
            $author                 = $row->div;
            $author[ 'class' ]      = 'col-xs-10';
            $row                    = $heading->div;
            $row[ 'class' ]         = 'row';
            $dateLabel              = $row->div;
            $dateLabel[ 'class' ]   = 'col-xs-2';
            $date                   = $row->div;
            $date[ 'class' ]        = 'col-xs-10';
            
            $authorLabel->small->strong->addTextData( $this->_lang->author );
            $author->small->addTextData( $comment->author );
            $dateLabel->small->strong->addTextData( $this->_lang->date );
            $date->small->addTextData( strftime( '%m/%d/%Y %H:%M', strtotime( $comment->date ) ) );
            
            $body->addTextData( nl2br( trim( $comment->content ) ) );
        }
        
        return $html;
    }
    
    public function getErrors()
    {
        if( count( $this->_errors ) === 0 )
        {
            $this->getPosts();
        }
        
        if( count( $this->_errors ) === 0 )
        {
            return '';
        }
        
        $errors = new XS_Xhtml_Tag( 'div' );
        
        foreach( $this->_errors as $error )
        {
            $errors->addChildNode( $error );
        }
        
        return ( string )$errors;
    }
    
    protected function _addPostError( SimpleXMLElement $post, $message )
    {
        if( empty( $message ) || $post === NULL )
        {
            return;
        }
        
        $error          = new XS_Xhtml_Tag( 'div' );
        $msg            = $error->div;
        $pre            = $error->pre;
        $msg[ 'class' ] = 'alert alert-warning';
        
        $msg->addTextData( $message );
        $pre->addTextData( print_r( $post, true ) );
        
        $this->_errors[] = $error;
    }
    
    public function getAtomFeed()
    {
        $feed                = new XS_Xhtml_Tag( 'feed' );
        $feed[ 'xmlns' ]    = 'http://www.w3.org/2005/Atom';
        $feed->title        = 'XS-Labs';
        $feed->subtitle     = 'XS-Labs Blog';
        $feed->id           = 'urn:uuid:' . ( string )( new XS_UUID( 'XS-Labs Blog' ) );
        $link1              = $feed->link;
        $link1[ 'href' ]    = 'http://' . $_SERVER[ 'HTTP_HOST' ] . '/feed/atom.php';
        $link1[ 'rel' ]     = self;
        $link2              = $feed->link;
        $link2[ 'href' ]    = 'http://' . $_SERVER[ 'HTTP_HOST' ] . XS_Menu::getInstance()->getPageURL( 'blog' );
        
        if( count( $this->_posts ) > 0 && isset( $this->_posts->post[ 0 ]->date ) && isset( $this->_posts->post[ 0 ]->time ) )
        {
            $updated = $feed->updated;
            
            $updated->addTextData( ( new DateTime( $this->_posts->post[ 0 ]->date . ' ' .$this->_posts->post[ 0 ]->time ) )->format( DateTime::ATOM ) );
        }
        
        foreach( $this->_posts->post as $post )
        {
            if( !isset( $post->title ) )
            {
                continue;
            }
            
            if( !isset( $post->name ) )
            {
                continue;
            }
            
            if( !isset( $post->date ) )
            {
                continue;
            }
            
            if( !isset( $post->time ) )
            {
                continue;
            }
            
            $path = __ROOTDIR__ . DIRECTORY_SEPARATOR . 'blog' . DIRECTORY_SEPARATOR . str_replace( '/', DIRECTORY_SEPARATOR, $post->date ) . DIRECTORY_SEPARATOR . $post->name . DIRECTORY_SEPARATOR;
            
            if( !file_exists( $path ) || !is_dir( $path ) || !file_exists( $path . 'index.html' ) )
            {
                continue;
            }
            
            $entry = $feed->entry;
            
            $entry->title           = $post->title;
            $link1                  = $entry->link;
            $link2                  = $entry->link;
            $link1[ 'href' ]        = 'http://' . $_SERVER[ 'HTTP_HOST' ] . $this->_getPostUrl( $post );
            $link2[ 'href' ]        = 'http://' . $_SERVER[ 'HTTP_HOST' ] . $this->_getPostUrl( $post );
            $link2[ 'rel' ]         = 'alternate';
            $link2[ 'type' ]        = 'text/html';
            $entry->id              = 'urn:uuid:' . ( string )( new XS_UUID( $post->date . '-' . $post->name ) );
            $entry->updated         = ( new DateTime( $post->date . ' ' . $post->time ) )->format( DateTime::ATOM );
            $summary                = $entry->summary;
            $summary[ 'type' ]      = 'html';
            $content                = $entry->content;
            $content[ 'type' ]      = 'html';
            $author                 = $entry->author;
            $author->name           = $post->author;
            
            $summary->addTextData( trim( $this->_getPostAbstract( $post ) ) );
            
            $html = file_get_contents( $path . 'index.html' );
            $html = str_replace
            (
                '{POST_URL}',
                'http://' . $_SERVER[ 'HTTP_HOST' ] . '/blog/' . $post->date . '/' . $post->name . '/',
                $html
            );
        
            $content->addTextData( $html );
        }
        
        return '<?xml version="1.0" encoding="utf-8"?>' . chr( 10 ) . ( string )$feed->asXml();
    }
    
    public function getRssFeed()
    {
        $rss                    = new XS_Xhtml_Tag( 'rss' );
        $rss[ 'version' ]       = '2.0';
        $rss[ 'xmlns:atom' ]    = 'http://www.w3.org/2005/Atom';
        $channel                = $rss->channel;
        $channel->title         = 'XS-Labs';
        $channel->description   = 'XS-Labs Blog';
        $channel->ttl           = '1800';
        $channel->link          = 'http://' . $_SERVER[ 'HTTP_HOST' ] . XS_Menu::getInstance()->getPageURL( 'blog' );
        
        $atomLink           = new XS_Xhtml_Tag( 'atom:link' );
        $atomLink[ 'href' ] = 'http://' . $_SERVER[ 'HTTP_HOST' ] . '/feed/rss.php';
        $atomLink[ 'rel' ]  = 'self';
        $atomLink[ 'type' ] = 'application/rss+xml';
        
        $channel->addChildNode( $atomLink );
        
        if( count( $this->_posts ) > 0 && isset( $this->_posts->post[ 0 ]->date ) && isset( $this->_posts->post[ 0 ]->time ) )
        {
            $updated = $channel->pubDate;
            
            $updated->addTextData( ( new DateTime( $this->_posts->post[ 0 ]->date . ' ' .$this->_posts->post[ 0 ]->time ) )->format( DateTime::RSS ) );
        }
        
        foreach( $this->_posts->post as $post )
        {
            if( !isset( $post->title ) )
            {
                continue;
            }
            
            if( !isset( $post->name ) )
            {
                continue;
            }
            
            if( !isset( $post->date ) )
            {
                continue;
            }
            
            if( !isset( $post->time ) )
            {
                continue;
            }
            
            $path = __ROOTDIR__ . DIRECTORY_SEPARATOR . 'blog' . DIRECTORY_SEPARATOR . str_replace( '/', DIRECTORY_SEPARATOR, $post->date ) . DIRECTORY_SEPARATOR . $post->name . DIRECTORY_SEPARATOR;
            
            if( !file_exists( $path ) || !is_dir( $path ) || !file_exists( $path . 'index.html' ) )
            {
                continue;
            }
            
            $item = $channel->item;
            
            $item->title            = $post->title;
            $item->description      = trim( $this->_getPostAbstract( $post ) );
            $link                   = 'http://' . $_SERVER[ 'HTTP_HOST' ] . $this->_getPostUrl( $post );
            $guid                   = $item->guid;
            $guid[ 'isPermaLink' ]  = "false";
            $item->pubDate          = ( new DateTime( $post->date . ' ' . $post->time ) )->format( DateTime::RSS );
            $item->link             = 'http://' . $_SERVER[ 'HTTP_HOST' ] . $this->_getPostUrl( $post );
            
            $guid->addTextData( ( string )( new XS_UUID( $post->date . '-' . $post->name ) ) );
        }
        
        return '<?xml version="1.0" encoding="utf-8"?>' . chr( 10 ) . ( string )$rss->asXml();
    }
}
