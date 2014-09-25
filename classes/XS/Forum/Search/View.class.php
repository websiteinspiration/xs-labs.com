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

class XS_Forum_Search_View extends XS_Forum_Base
{
    CONST POSTS_LIMIT = 10;
    
    protected $_searchQuery = array();
    protected $_searchWord  = '';
    protected $_activePage = 0;
    protected $_postsCount = 0;
    
    public function __construct()
    {
        parent::__construct();
        
        if( isset( $_POST[ 'forum' ][ 'searchWord' ] ) && $_POST[ 'forum' ][ 'searchWord' ] ) {
            
            $this->_searchWord = $_POST[ 'forum' ][ 'searchWord' ];
            
        } elseif( isset( $_GET[ 'forum' ][ 'searchWord' ] ) && $_GET[ 'forum' ][ 'searchWord' ] ) {
            
            $this->_searchWord = urldecode( $_GET[ 'forum' ][ 'searchWord' ] );
            
        } else {
            
            $this->_content->div = $this->_lang->noSearchWord;
            return;
        }
        
        $this->_searchWord = htmlspecialchars( $this->_searchWord );
        $this->_createSearchQuery();
        
        $this->_postsCount   = $this->_getPostsCount();
        
        if( isset( $this->_params[ 'page' ] ) ) {
            
            if( $this->_params[ 'page' ] === 'last' ) {
                
                $this->_activePage = ceil( $this->_postsCount / self::POSTS_LIMIT ) - 1;
                
            } else {
                
                $this->_activePage = ( int )$this->_params[ 'page' ];
                
                if( $this->_activePage >= ( $this->_postsCount / self::POSTS_LIMIT ) ) {
                    
                    $this->_activePage = 0;
                }
            }
        }
        
        if( $this->_postsCount == 0 ) {
            
            $this->_content->div = $this->_lang->noResult;
            
        } else {
            
            $this->_search();
            
            $pages            = $this->_content->div;
            $pages[ 'class' ] = 'forum-pagination';
            
            $this->_paginate( $pages );
        }
    }
    
    protected function _getPostsCount()
    {
        $query = $this->_db->prepare(
            'SELECT COUNT( * ) FROM '
          . self::TABLE_POSTS
          . ' WHERE deleted = 0 AND ( '
          . $this->_searchQuery[ 'query' ]
          . ' )'
        );
        
        $query->execute( $this->_searchQuery[ 'params' ] );
        
        $count = $query->fetchColumn();
        
        return ( int )$count;
    }
    
    protected function _search()
    {
        $sql    = 'SELECT * FROM '
                . self::TABLE_POSTS
                . ' WHERE deleted = 0 AND ( '
                . $this->_searchQuery[ 'query' ]
                . ' ) ORDER BY ctime DESC LIMIT '
                . self::POSTS_LIMIT
                . ' OFFSET '
                . ( self::POSTS_LIMIT * $this->_activePage );
        
        $query = $this->_db->prepare( $sql );
        
        $query->execute( $this->_searchQuery[ 'params' ] );
        
        $this->_content->h3 = $this->_lang->titleResults;
        $posts              = $this->_content->div;
        $posts[ 'class' ]   = 'forum-search-results';
        
        $this->_displayPosts( $query, $posts );
    }
    
    protected function _createSearchQuery()
    {
        $words  = explode( ' ', $this->_searchWord );
        $query  = array();
        $params = array();
        
        $i = 0;
        
        foreach( $words as $word ) {
            
            if( empty( $word ) ) {
                
                continue;
            }
            
            $query[]                   = 'message LIKE :message' . $i;
            $params[ ':message' . $i ] = '%' . $word . '%';
            
            $i++;
        }
        
        $this->_searchQuery = array(
            'query' => implode( ' OR ', $query ),
            'params' => $params
        );
    }
    
    protected function _displayPosts( PDOStatement $query, XS_Xhtml_Tag $container )
    {
        $i = 0;
        
        while( $post = $query->fetchObject() ) {
            
            $thread = new XS_Database_Object( self::TABLE_THREADS, $post->id_forum_thread );
            
            $div             = $container->div;
            $title           = $div->div;
            $details         = $div->div;
            $description     = $div->div;
            $link            = $title->h4->a;
            $author          = $details->div;
            $date            = $details->div;
            $replies         = $details->div;
            $descriptionLink = $description->a;
            
            $user    = new XS_Database_Object( self::TABLE_USERS, $post->id_user );
            $options = XS_Database_Object::getObjectsByFields( self::TABLE_USER_OPTIONS, array( 'id_user' => $user->getId() ) );
            $option  = array_shift( $options );
            
            if( $option->show_fullname == 1 ) {
                
                $username = $user->firstname . ' ' . $user->lastname. ' (' . $user->username . ')';
                
            } else {
                
                $username = $user->username;
            }
            
            $div[ 'class' ]         = ( $i % 2 ) ? 'post-odd' : 'post-even';
            $title[ 'class' ]       = 'post-title';
            $description[ 'class' ] = 'post-description';
            $details[ 'class' ]     = 'post-details';
            $author[ 'class' ]      = 'post-author';
            $date[ 'class' ]        = 'post-date';
            $replies[ 'class' ]     = 'post-replies';
            
            $link[ 'href' ] = $this->_menu->getCurrentPageUrl(
                array(
                    'forum[thread]' => $thread->getId(),
                    'forum[page]'   => 'last'
                )
            );
            
            $descriptionLink[ 'href' ] = $link[ 'href' ];
            
            $link->addTextData( $thread->title );
            $date->addTextData( sprintf( $this->_lang->date, strftime( self::DATETIME_FORMAT, $post->ctime ) ) );
            $author->addTextData( sprintf( $this->_lang->author, $username ) );
            
            $descriptionLink->div = $this->_emoticon->replaceSymbols( $this->_str->crop( $post->message, 200 ) );
            
            $i++;
        }
    }
    
    protected function _paginate( XS_Xhtml_Tag $container )
    {
        if( $this->_postsCount <= self::POSTS_LIMIT ) {
            
            return;
        }
        
        $prevNext            = $container->div;
        $pages               = $container->div;
        $prevNext[ 'class' ] = 'forum-prevNext';
        $pages[ 'class' ]    = 'forum-pages';
        $list                = $pages->ul;
        
        if( $this->_activePage > 0 ) {
            
            $link           = $prevNext->span->a;
            $link[ 'href' ] = $this->_menu->getCurrentPageUrl(
                array(
                    'forum[search]' => 1,
                    'forum[page]'   => $this->_activePage - 1,
                    'forum[searchWord]' => $this->_searchWord
                )
            );
            
            $link->addTextData( $this->_lang->previous );
        }
        
        if( $this->_activePage < ceil( ( $this->_postsCount / self::POSTS_LIMIT ) - 1 ) ) {
            
            $link           = $prevNext->span->a;
            $link[ 'href' ] = $this->_menu->getCurrentPageUrl(
                array(
                    'forum[search]' => 1,
                    'forum[page]'   => $this->_activePage + 1,
                    'forum[searchWord]' => $this->_searchWord
                )
            );
            
            $link->addTextData( $this->_lang->next );
        }
        
        for( $i = 0; $i < ceil( $this->_postsCount / self::POSTS_LIMIT ); $i++ ) {
            
            if( $i == $this->_activePage ) {
                
                $link = $list->li;
                
            } else {
                
                $link           = $list->li->a;
                $link[ 'href' ] = $this->_menu->getCurrentPageUrl(
                    array(
                        'forum[search]' => 1,
                        'forum[page]'   => $i,
                        'forum[searchWord]' => $this->_searchWord
                    )
                );
            }
            
            $link->addTextData( $i + 1 );
        }
    }
}
