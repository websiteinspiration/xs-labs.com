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

class XS_Forum_Thread_View extends XS_Forum_Base
{
    CONST POSTS_LIMIT = 10;
    
    protected $_id         = 0;
    protected $_activePage = 0;
    protected $_postsCount = 0;
    protected $_section    = NULL;
    protected $_category   = NULL;
    protected $_thread     = NULL;
    
    public function __construct( $id )
    {
        parent::__construct();
        
        $reply               = new XS_Forum_Thread_Reply_View();
        $this->_id           = ( int )$id;
        $this->_thread       = new XS_Database_Object( self::TABLE_THREADS, $this->_id );
        $this->_category     = new XS_Database_Object( self::TABLE_CATEGORIES, $this->_thread->id_forum_category );
        $this->_section      = new XS_Database_Object( self::TABLE_SECTIONS, $this->_category->id_forum_section );
        
        if( !$this->_id )
        {
            return;
        }
        
        if( isset( $this->_params[ 'deletePost' ] ) && $this->_params[ 'deletePost' ] ) {
            
            $post = new XS_Database_Object( self::TABLE_POSTS, ( int )$this->_params[ 'deletePost' ] );
            
            if( is_object( $this->_user ) && $post->id_forum_thread == $this->_thread->getId() && $post->root != 1 ) {
                
                if( $this->_userOptions->delete_posts == 1 ) {
                    
                    $post->delete();
                }
            }
        }
        
        $this->_postsCount = $this->_getPostsCount();
        
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
        
        if( $reply->postWasSubmitted() === true ) {
            
            $this->_activePage       = ceil( ( $this->_postsCount / self::POSTS_LIMIT ) - 1 );
            $this->_params[ 'page' ] = $this->_activePage;
        }
        
        $title = $this->_content->div;
        $posts = $this->_content->div;
        $pages = $this->_content->div;
        
        $title[ 'class' ] = 'thread-header';
        $pages[ 'class' ] = 'forum-pagination';
        
        $title->h3 = $this->_section->name . ' - ' . $this->_category->name . ' - ' . $this->_thread->title;
        
        $this->_listPosts( $this->_id, $posts );
        $this->_paginate( $pages );
        
        if( $this->_thread->closed == 0 ) {
            
            $this->_content->addChildNode( $reply->getContent() );
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
                    'forum[thread]' => $this->_id,
                    'forum[page]'   => $this->_activePage - 1
                )
            );
            
            $link->addTextData( $this->_lang->previous );
        }
        
        if( $this->_activePage < ceil( ( $this->_postsCount / self::POSTS_LIMIT ) - 1 ) ) {
            
            $link           = $prevNext->span->a;
            $link[ 'href' ] = $this->_menu->getCurrentPageUrl(
                array(
                    'forum[thread]' => $this->_id,
                    'forum[page]'   => $this->_activePage + 1
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
                        'forum[thread]' => $this->_id,
                        'forum[page]'   => $i
                    )
                );
            }
            
            $link->addTextData( $i + 1 );
        }
    }
    
    protected function _listPosts( $thread, XS_Xhtml_Tag $container )
    {
        $posts = XS_Database_Object::getObjectsByFields( self::TABLE_POSTS, array( 'id_forum_thread' => $thread ), 'ctime ASC', self::POSTS_LIMIT, $this->_activePage * self::POSTS_LIMIT );
        $i     = 0;
        
        foreach( $posts as $key => $value ) {
            
            $div             = $container->div;
            $details         = $div->div;
            $description     = $div->div;
            $author          = $details->div;
            $date            = $details->div;
            
            $user    = new XS_Database_Object( self::TABLE_USERS, $value->id_user );
            $options = XS_Database_Object::getObjectsByFields( self::TABLE_USER_OPTIONS, array( 'id_user' => $user->getId() ) );
            $option  = array_shift( $options );
            
            if( $option->show_fullname == 1 ) {
                
                $username = $user->firstname . ' ' . $user->lastname. ' (' . $user->username . ')';
                
            } else {
                
                $username = $user->username;
            }
            
            $div[ 'class' ]         = ( $i % 2 ) ? 'post-odd' : 'post-even';
            $description[ 'class' ] = 'post-description';
            $details[ 'class' ]     = 'post-details';
            $author[ 'class' ]      = 'post-author';
            $date[ 'class' ]        = 'post-date';
            
            $date->addTextData( sprintf( $this->_lang->date, strftime( self::DATETIME_FORMAT, $value->ctime ) ) );
            $author->addTextData( sprintf( $this->_lang->author, $username ) );
            
            $description->div = $this->_emoticon->replaceSymbols( nl2br( $value->message ) );
            
            if( is_object( $this->_userOptions ) && $this->_userOptions->delete_posts == 1 && $value->root != 1 ) {
                
                $delete               = $div->div;
                $deleteLink           = $delete->a;
                $deleteLink[ 'href' ] = $this->_menu->getCurrentPageUrl( array( 'forum[thread]' => $this->_thread->getId(), 'forum[deletePost]' => $value->getId() ) );
                $delete[ 'class' ]    = 'forum-post-delete';
                
                $deleteLink->addTextData( $this->_lang->deletePost );
            }
            
            $i++;
        }
    }
    
    protected function _getPostsCount()
    {
        $query = $this->_db->query(
            'SELECT COUNT( * ) FROM '
          . self::TABLE_POSTS
          . ' WHERE deleted = 0 AND id_forum_thread = '
          . $this->_id
        );
        
        $count = $query->fetchColumn();
        
        return ( int )$count;
    }
}
