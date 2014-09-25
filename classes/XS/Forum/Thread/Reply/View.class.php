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

class XS_Forum_Thread_Reply_View extends XS_Forum_Base
{
    protected $_postWasSubmitted = false;
    
    public function __construct()
    {
        parent::__construct();
        
        if( !is_object( $this->_user ) )
        {
            return;
        }
        
        if( isset( $_POST[ 'forum' ][ 'reply' ] ) )
        {
            $this->_data        = $_POST[ 'forum' ][ 'reply' ];
            $confirm            = $this->_content->div;
            $confirm[ 'class' ] = 'forum-reply-confirm';
            
            if( $this->_postReply() )
            {
                $confirm->addTextData( $this->_lang->replyConfirm );
                
                $this->_postWasSubmitted = true;
            }
            else
            {
                $confirm->addTextData( $this->_lang->replyConfirmError );
                
                $this->_postWasSubmitted = false;
            }
        }
        
        $this->_content->h4 = $this->_lang->titleReply;
        $anchor             = $this->_content->a;
        $anchor[ 'name' ]   = 'forum-reply-anchor';
        $form               = $this->_content->form;
        $form[ 'action' ]   = $this->_menu->getCurrentPageUrl( array( 'forum[thread]' => $this->_params[ 'thread' ] ) ) . '#forum-reply-anchor';
        $form[ 'name' ]     = 'forum-reply';
        $form[ 'id' ]       = 'forum-reply';
        $form[ 'method' ]   = 'post';
        $form[ 'class' ]    = 'forum-reply';
        
        $this->_createFields( $form );
    }
    
    protected function _createFields( XS_Xhtml_Tag $form )
    {
        $this->_createTextArea( 'message', $form );
        $this->_createSubmit( $form );
    }
    
    protected function _createSubmit( XS_Xhtml_Tag $container, $name = 'submit' )
    {
        $div      = $container->div;
        $field    = $div->div;
        $inputDiv = $field->div;
        $input    = $inputDiv->input;
        
        $div[ 'class' ]      = 'forum-field-' . $name;
        $field[ 'class' ]    = 'forum-field';
        $inputDiv[ 'class' ] = 'forum-field-formElement';
        $input[ 'id' ]       = 'forum-field-' . $name;
        $input[ 'type' ]     = 'submit';
        $input[ 'value' ]    = $this->_lang->$name;
        $input[ 'name' ]     = 'forum[reply][' . $name . ']';
    }
    
    protected function _createTextArea( $name, XS_Xhtml_Tag $container )
    {
        $div     = $container->div;
        $field   = $div->div;
        $textDiv = $field->div;
        $text    = $textDiv->textarea;
        
        $div[ 'class' ]      = 'forum-field-' . $name;
        $field[ 'class' ]    = 'forum-field';
        $textDiv[ 'class' ] = 'forum-field-formElement';
        $text[ 'id' ]       = 'forum-field-' . $name;
        $text[ 'name' ]     = 'forum[reply][' . $name . ']';
        $text[ 'cols' ]     = '75';
        $text[ 'rows' ]     = '10';
    }
    
    protected function _postReply()
    {
        if( !isset( $this->_data[ 'message' ] ) || !$this->_data[ 'message' ] )
        {
            return false;
        }
        
        $post                  = new XS_Database_Object( self::TABLE_POSTS );
        $post->message         = htmlspecialchars( $this->_data[ 'message' ] );
        $post->id_forum_thread = $this->_params[ 'thread' ];
        $post->id_user         = $this->_user->getId();
        
        $post->commit();
        
        $thread       = new XS_Database_Object( self::TABLE_THREADS,    ( int )$post->id_forum_thread );
        $category     = new XS_Database_Object( self::TABLE_CATEGORIES, ( int )$thread->id_forum_category );
        $section      = new XS_Database_Object( self::TABLE_SECTIONS,   ( int )$category->id_forum_section );
        $menu         = XS_Menu::getInstance();
        $url          = ( isset( $_SERVER[ 'HTTPS' ] ) ) ? 'https://' : 'http://' . $_SERVER[ 'HTTP_HOST' ] . $menu->getCurrentPageUrl();
        $threadUrl    = $url . '?' . urlencode( 'forum[thread]' ) . '=' . $thread->getId();
        $userFullName = $this->_user->username . ' (' . $this->_user->firstname . ' ' . $this->_user->lastname . ')';
        $message      = 'A new forum post was created:'
                      . chr( 10 )
                      . chr( 10 )
                      . 'Thread:   ' . $thread->title  . chr( 10 )
                      . 'Category: ' . $category->name . chr( 10 )
                      . 'Section:  ' . $section->name  . chr( 10 )
                      . 'User:     ' . $userFullName   . chr( 10 )
                      . chr( 10 )
                      . 'Click on the link below to display the thread:'
                      . chr( 10 )
                      . $threadUrl;
        
        $mail = new XS_Mail
        (
            'macmade@xs-labs.com',
            'XS-Labs: new forum post',
            $message,
            'macmade@xs-labs.com'
        );
        
        $mail->send();
        
        return true;
    }
    
    public function postWasSubmitted()
    {
        return $this->_postWasSubmitted;
    }
}
