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

class XS_Forum_Category_View extends XS_Forum_Base
{
    CONST THREADS_LIMIT = 20;
    
    protected $_id           = 0;
    protected $_activePage   = 0;
    protected $_threadsCount = 0;
    protected $_section      = NULL;
    protected $_category     = NULL;
    
    public function __construct( $id )
    {
        parent::__construct();
        
        $this->_id           = ( int )$id;
        $this->_category     = new XS_Database_Object( self::TABLE_CATEGORIES, $this->_id );
        $this->_section      = new XS_Database_Object( self::TABLE_SECTIONS, $this->_category->id_forum_section );
        
        if( isset( $this->_params[ 'deleteThread' ] ) && $this->_params[ 'deleteThread' ] ) {
            
            $thread = new XS_Database_Object( self::TABLE_THREADS, ( int )$this->_params[ 'deleteThread' ] );
            
            if( is_object( $this->_user ) && $thread->id_forum_category == $this->_category->getId() ) {
                
                if( $this->_userOptions->delete_threads == 1 ) {
                    
                    $posts = XS_Database_Object::getObjectsByFields( self::TABLE_POSTS, array( 'id_forum_thread' => $thread->getId() ) );
                    
                    foreach( $posts as $post ) {
                        
                        $post->delete();
                    }
                    
                    $thread->delete();
                }
            }
        }
        
        $this->_threadsCount = $this->_getThreadsCount();
        
        if( isset( $this->_params[ 'page' ] ) ) {
            
            if( $this->_params[ 'page' ] === 'last' ) {
                
                $this->_activePage = ceil( $this->_postsCount / self::THREADS_LIMIT ) - 1;
                
            } else {
                
                $this->_activePage = ( int )$this->_params[ 'page' ];
                
                if( $this->_activePage >= ( $this->_postsCount / self::THREADS_LIMIT ) ) {
                    
                    $this->_activePage = 0;
                }
            }
        }
        
        if( is_object( $this->_user ) ) {
            
            $new               = $this->_content->div;
            $new[ 'class' ]    = 'forum-thread-create-link';
            $newLink           = $new->a;
            $newLink[ 'href' ] = $this->_menu->getCurrentPageUrl(
                array(
                    'forum[newThread]' => $this->_params[ 'category' ]
                )
            );
            
            $newLink->addTextData( $this->_lang->createThread );
        }
        
        $title   = $this->_content->div;
        $threads = $this->_content->div;
        $pages   = $this->_content->div;
        
        $title[ 'class' ] = 'category-header';
        $pages[ 'class' ] = 'forum-pagination';
        
        $title->h3 = $this->_section->name . ' - ' . $this->_category->name;
        
        $this->_listThreads( $this->_id, $threads );
        $this->_paginate( $pages );
    }
    
    protected function _paginate( XS_Xhtml_Tag $container )
    {
        if( $this->_threadsCount <= self::THREADS_LIMIT ) {
            
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
                    'forum[category]' => $this->_id,
                    'forum[page]'     => $this->_activePage - 1
                )
            );
            
            $link->addTextData( $this->_lang->previous );
        }
        
        if( $this->_activePage < ( $this->_threadsCount / self::THREADS_LIMIT ) - 1 ) {
            
            $link           = $prevNext->span->a;
            $link[ 'href' ] = $this->_menu->getCurrentPageUrl(
                array(
                    'forum[category]' => $this->_id,
                    'forum[page]'     => $this->_activePage + 1
                )
            );
            
            $link->addTextData( $this->_lang->next );
        }
        
        for( $i = 0; $i < (  $this->_threadsCount / self::THREADS_LIMIT  ); $i++ ) {
            
            if( $i === $this->_activePage ) {
                
                $link = $list->li;
                
            } else {
                
                $link           = $list->li->a;
                $link[ 'href' ] = $this->_menu->getCurrentPageUrl(
                    array(
                        'forum[category]' => $this->_id,
                        'forum[page]'     => $i
                    )
                );
            }
            
            $link->addTextData( $i + 1 );
        }
    }
    
    protected function _listThreads( $category, XS_Xhtml_Tag $container )
    {
        $threads = XS_Database_Object::getObjectsByFields( self::TABLE_THREADS, array( 'id_forum_category' => $category ), 'ctime DESC', self::THREADS_LIMIT, $this->_activePage * self::THREADS_LIMIT );
        $i       = 0;
        
        if( !count( $threads ) ) {
            
            $div            = $container->div;
            $div[ 'class' ] = 'forum-no-thread';
            
            $div->addTextData( $this->_lang->noThread );
            
            return;
        }
        
        foreach( $threads as $key => $value ) {
            
            $posts = XS_Database_Object::getObjectsByFields( self::TABLE_POSTS, array( 'id_forum_thread' => $value->getId(), 'root' => 1 ) );
            
            if( !count( $posts ) ) {
                
                continue;
            }
            
            $post            = array_shift( $posts );
            $div             = $container->div;
            $title           = $div->div;
            $details         = $div->div;
            $description     = $div->div;
            $link            = $title->h4->a;
            $author          = $details->div;
            $date            = $details->div;
            $replies         = $details->div;
            $descriptionLink = $description->a;
            
            $user    = new XS_Database_Object( self::TABLE_USERS, $value->id_user );
            $options = XS_Database_Object::getObjectsByFields( self::TABLE_USER_OPTIONS, array( 'id_user' => $user->getId() ) );
            $option  = array_shift( $options );
            
            if( $option->show_fullname == 1 ) {
                
                $username = $user->firstname . ' ' . $user->lastname. ' (' . $user->username . ')';
                
            } else {
                
                $username = $user->username;
            }
            
            $div[ 'class' ]         = ( $i % 2 ) ? 'thread-odd' : 'thread-even';
            $title[ 'class' ]       = 'thread-title';
            $description[ 'class' ] = 'thread-description';
            $details[ 'class' ]     = 'thread-details';
            $author[ 'class' ]      = 'thread-author';
            $date[ 'class' ]        = 'thread-date';
            $replies[ 'class' ]     = 'thread-replies';
            
            $link[ 'href' ]            = $this->_menu->getCurrentPageUrl( array( 'forum[thread]' => $value->getId() ) );
            $descriptionLink[ 'href' ] = $link[ 'href' ];
            
            $link->addTextData( $value->title );
            $date->addTextData( sprintf( $this->_lang->date, strftime( self::DATETIME_FORMAT, $value->ctime ) ) );
            $author->addTextData( sprintf( $this->_lang->author, $username ) );
            
            $descriptionLink->div = $this->_emoticon->replaceSymbols( $this->_str->crop( $post->message, 200 ) );
            
            if( is_object( $this->_userOptions ) && $this->_userOptions->delete_threads == 1 ) {
                
                $delete               = $div->div;
                $deleteLink           = $delete->a;
                $deleteLink[ 'href' ] = $this->_menu->getCurrentPageUrl( array( 'forum[category]' => $this->_category->getId(), 'forum[deleteThread]' => $value->getId() ) );
                $delete[ 'class' ]    = 'forum-thread-delete';
                
                $deleteLink->addTextData( $this->_lang->deleteThread );
            }
            
            $i++;
            
            $this->_getRepliesCount( $replies, $value->getId() );
        }
    }
    
    protected function _getThreadsCount()
    {
        $query = $this->_db->query(
            'SELECT COUNT( * ) FROM '
          . self::TABLE_THREADS
          . ' WHERE deleted = 0 AND id_forum_category = '
          . $this->_id
        );
        
        $count = $query->fetchColumn();
        
        return ( int )$count;
    }
    
    protected function _getRepliesCount( XS_Xhtml_Tag $container, $thread )
    {
        $query = $this->_db->query(
            'SELECT COUNT( * ) FROM '
          . self::TABLE_POSTS
          . ' WHERE deleted = 0 AND root = 0 AND id_forum_thread = '
          . $thread
        );
        
        $count = $query->fetchColumn();
        
        $container->addTextData( sprintf( $this->_lang->repliesCount, $count ) );
    }
}
