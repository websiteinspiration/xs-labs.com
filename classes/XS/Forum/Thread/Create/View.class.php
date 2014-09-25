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

class XS_Forum_Thread_Create_View extends XS_Forum_Base
{
    protected $_category = NULL;
    
    public function __construct( $category )
    {
        parent::__construct();
        
        if( !is_object( $this->_user ) )
        {
            header( 'Location:' . $this->_menu->getCurrentPageUrl() );
            exit();
        }
        
        $this->_errors = new stdClass();
        $valid         = false;
        
        if( isset( $_POST[ 'forum' ][ 'newThread' ] ) )
        {
            $this->_data = $_POST[ 'forum' ][ 'newThread' ];
            $valid       = $this->_checkFormValues();
        }
        
        $this->_category = new XS_Database_Object( self::TABLE_CATEGORIES, ( int )$category );
        
        if( $valid === true )
        {
            $id = $this->_createThread();
            
            header( 'Location:' . $this->_menu->getCurrentPageUrl( array( 'forum[thread]' => $id ) ) );
            exit();
        }
        
        $this->_content->h3 = $this->_lang->titleNewThread;
        $form               = $this->_content->form;
        $form[ 'action' ]   = $this->_menu->getCurrentPageUrl( array( 'forum[newThread]' => $this->_category->getId() ) );
        $form[ 'name' ]     = 'forum-newThread';
        $form[ 'id' ]       = 'forum-newThread';
        $form[ 'method' ]   = 'post';
        $form[ 'class' ]    = 'forum-newThread';
        
        $this->_createFields( $form );
    }
    
    protected function _checkFormValues()
    {
        $this->_checkRequired( 'title' );
        $this->_checkRequired( 'message' );
        
        $valid = true;
        
        foreach( $this->_errors as $field => $errorText )
        {
            $error            = new XS_Xhtml_Tag( 'div' );
            $error[ 'class' ] = 'forum-field-error';
            
            $error->addTextData( $errorText );
            
            $this->_errors->$field = $error;
            $valid                 = false;
        }
        
        return $valid;
    }
    
    protected function _checkRequired( $name )
    {
        if( !isset( $this->_data[ $name ] ) || !$this->_data[ $name ] )
        {
            $this->_errors->$name = $this->_lang->errorRequired;
        }
    }
    
    protected function _createFields( XS_Xhtml_Tag $form )
    {
        $this->_createTextInput( 'title', $form );
        $this->_createTextArea( 'message', $form );
        
        $this->_createSubmit( $form );
    }
    
    protected function _createTextInput( $name, XS_Xhtml_Tag $container )
    {
        $div      = $container->div;
        $field    = $div->div;
        $labelDiv = $field->div;
        $inputDiv = $field->div;
        $label    = $labelDiv->label;
        $input    = $inputDiv->input;
        
        $div[ 'class' ]      = 'forum-field-' . $name;
        $field[ 'class' ]    = 'forum-field';
        $labelDiv[ 'class' ] = 'forum-field-label';
        $inputDiv[ 'class' ] = 'forum-field-formElement';
        
        $label->addTextData( $this->_lang->$name );
        
        $label[ 'for' ]   = 'forum-field-' . $name;
        $input[ 'id' ]    = 'forum-field-' . $name;
        $input[ 'type' ]  = 'text';
        $input[ 'size' ]  = '50';
        $input[ 'value' ] = ( isset( $this->_data[ $name ] ) ) ? $this->_data[ $name ] : '';
        $input[ 'name' ]  = 'forum[newThread][' . $name . ']';
        
        if( isset( $this->_errors->$name ) )
        {
            $div->addChildNode( $this->_errors->$name );
        }
    }
    
    protected function _createTextArea( $name, XS_Xhtml_Tag $container )
    {
        $div      = $container->div;
        $field    = $div->div;
        $labelDiv = $field->div;
        $textDiv  = $field->div;
        $label    = $labelDiv->label;
        $text     = $textDiv->textarea;
        
        $div[ 'class' ]      = 'forum-field-' . $name;
        $field[ 'class' ]    = 'forum-field';
        $labelDiv[ 'class' ] = 'forum-field-label';
        $textDiv[ 'class' ]  = 'forum-field-formElement';
        
        $label->addTextData( $this->_lang->$name );
        
        $label[ 'for' ]   = 'forum-field-' . $name;
        $text[ 'id' ]    = 'forum-field-' . $name;
        $text[ 'cols' ]  = '75';
        $text[ 'rows' ]  = '20';
        $text[ 'name' ]  = 'forum[newThread][' . $name . ']';
        
        if( isset( $this->_data[ $name ] ) )
        {
            $text->addTextData( $this->_data[ $name ] );
        }
        
        if( isset( $this->_errors->$name ) )
        {
            $div->addChildNode( $this->_errors->$name );
        }
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
        $input[ 'name' ]     = 'forum[newThread][' . $name . ']';
    }
    
    protected function _createThread()
    {
        $thread = new XS_Database_Object( self::TABLE_THREADS );
        $post   = new XS_Database_Object( self::TABLE_POSTS );
        
        $thread->title             = htmlspecialchars( $this->_data[ 'title' ] );
        $thread->id_forum_category = $this->_category->getId();
        $thread->id_user           = $this->_user->getId();
        
        $thread->commit();
        
        $post->message         = htmlspecialchars( $this->_data[ 'message' ] );
        $post->root            = 1;
        $post->id_forum_thread = $thread->getId();
        $post->id_user         = $this->_user->getId();
        
        $post->commit();
        
        $menu         = XS_Menu::getInstance();
        $url          = ( isset( $_SERVER[ 'HTTPS' ] ) ) ? 'https://' : 'http://' . $_SERVER[ 'HTTP_HOST' ] . $menu->getCurrentPageUrl();
        $threadUrl    = $url . '?' . urlencode( 'forum[thread]' ) . '=' . $thread->getId();
        $userFullName = $this->_user->username . ' (' . $this->_user->firstname . ' ' . $this->_user->lastname . ')';
        $section      = new XS_Database_Object( self::TABLE_SECTIONS, ( int )$this->_category->id_forum_section );
        $message      = 'A new forum thread was created:'
                      . chr( 10 )
                      . chr( 10 )
                      . 'Title:    ' . $thread->title         . chr( 10 )
                      . 'Category: ' . $this->_category->name . chr( 10 )
                      . 'Section:  ' . $section->name         . chr( 10 )
                      . 'User:     ' . $userFullName          . chr( 10 )
                      . chr( 10 )
                      . 'Click on the link below to display the thread:'
                      . chr( 10 )
                      . $threadUrl;
        
        $mail = new XS_Mail
        (
            'macmade@xs-labs.com',
            'XS-Labs: new forum thread',
            $message,
            'macmade@xs-labs.com'
        );
        
        $mail->send();
        
        return $thread->getId();
    }
}
