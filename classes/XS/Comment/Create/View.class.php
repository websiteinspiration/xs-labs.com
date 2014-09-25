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

class XS_Comment_Create_View extends XS_Comment_Base
{
    protected $_errors  = NULL;
    protected $_session = NULL;
    protected $_data    = array();
    
    public function __construct()
    {
        parent::__construct();
        
        $this->_session = XS_Session::getInstance();
        $this->_errors  = new stdClass();
        $valid          = false;
        
        if( isset( $_POST[ 'comment' ] ) )
        {
            $this->_data = $_POST[ 'comment' ];
            $valid       = $this->_checkFormValues();
        }
        
        $anchor             = $this->_content->a;
        $anchor[ 'name' ]   = 'comment-create-anchor';
        
        if( $valid === true )
        {
            $confirm            = $this->_content->div;
            $confirm[ 'class' ] = 'comment-confirm';
            
            $this->_createComment();
            $confirm->addTextData( $this->_lang->createConfirm );
            
        }
        else
        {
            $this->_content->h4 = $this->_lang->titleCreateComment;
            $form               = $this->_content->form;
            $form[ 'action' ]   = $this->_menu->getCurrentPageUrl() . '#comment-create-anchor';
            $form[ 'id' ]       = 'comment-create';
            $form[ 'method' ]   = 'post';
            $form[ 'class' ]    = 'comment-create';
            
            $this->_createFields( $form );
        }
    }
    
    protected function _createFields( XS_Xhtml_Tag $form )
    {
        $this->_createTextInput( 'author', $form );
        $this->_createTextInput( 'email', $form );
        $this->_createTextArea( 'comment', $form );
        $this->_createCaptcha( 'captcha', $form );
        $this->_createSubmit( $form );
    }
    
    protected function _createCaptcha( $name, XS_Xhtml_Tag $container )
    {
        $div      = $container->div;
        $field    = $div->div;
        $labelDiv = $field->div;
        $inputDiv = $field->div;
        $label    = $labelDiv->label;
        $input    = $inputDiv->input;
        $captcha  = $inputDiv->img;
        
        $div[ 'class' ]      = 'comment-field-' . $name;
        $field[ 'class' ]    = 'comment-field';
        $labelDiv[ 'class' ] = 'comment-field-label';
        $inputDiv[ 'class' ] = 'comment-field-formElement';
        
        $label->addTextData( $this->_lang->$name );
        
        $captcha[ 'src' ]   = '/scripts/captcha.php';
        $captcha[ 'alt' ]   = $this->_lang->$name;
        $captcha[ 'title' ] = $this->_lang->$name;
        
        $label[ 'for' ]   = 'comment-field-' . $name;
        $input[ 'id' ]    = 'comment-field-' . $name;
        $input[ 'type' ]  = 'text';
        $input[ 'size' ]  = '50';
        $input[ 'value' ] = ( isset( $this->_data[ $name ] ) ) ? $this->_data[ $name ] : '';
        $input[ 'name' ]  = 'comment[' . $name . ']';
        
        if( isset( $this->_errors->$name ) )
        {
            $div->addChildNode( $this->_errors->$name );
        }
    }
    
    protected function _createTextInput( $name, XS_Xhtml_Tag $container )
    {
        $div      = $container->div;
        $field    = $div->div;
        $labelDiv = $field->div;
        $inputDiv = $field->div;
        $label    = $labelDiv->label;
        $input    = $inputDiv->input;
        
        $div[ 'class' ]      = 'comment-field-' . $name;
        $field[ 'class' ]    = 'comment-field';
        $labelDiv[ 'class' ] = 'comment-field-label';
        $inputDiv[ 'class' ] = 'comment-field-formElement';
        
        $label->addTextData( $this->_lang->$name );
        
        $label[ 'for' ]   = 'comment-field-' . $name;
        $input[ 'id' ]    = 'comment-field-' . $name;
        $input[ 'type' ]  = 'text';
        $input[ 'size' ]  = '50';
        $input[ 'value' ] = ( isset( $this->_data[ $name ] ) ) ? $this->_data[ $name ] : '';
        $input[ 'name' ]  = 'comment[' . $name . ']';
        
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
        
        $div[ 'class' ]      = 'comment-field-' . $name;
        $field[ 'class' ]    = 'comment-field';
        $inputDiv[ 'class' ] = 'comment-field-formElement';
        $input[ 'id' ]       = 'comment-field-' . $name;
        $input[ 'type' ]     = 'submit';
        $input[ 'class' ]    = 'form-submit';
        $input[ 'value' ]    = $this->_lang->$name;
        $input[ 'name' ]     = 'comment[' . $name . ']';
    }
    
    protected function _createTextArea( $name, XS_Xhtml_Tag $container )
    {
        $div      = $container->div;
        $field    = $div->div;
        $labelDiv = $field->div;
        $textDiv  = $field->div;
        $text     = $textDiv->textarea;
        $label    = $labelDiv->label;
        
        $div[ 'class' ]      = 'comment-field-' . $name;
        $field[ 'class' ]    = 'comment-field';
        $textDiv[ 'class' ]  = 'comment-field-formElement';
        $labelDiv[ 'class' ] = 'comment-field-label';
        
        $label->addTextData( $this->_lang->$name );
        
        $text[ 'id' ]        = 'comment-field-' . $name;
        $text[ 'name' ]      = 'comment[' . $name . ']';
        $text[ 'cols' ]      = '75';
        $text[ 'rows' ]      = '10';
        
        if( isset( $this->_data[ $name ] ) )
        {
            $text->addTextData( $this->_data[ $name ] );
        }
        
        if( isset( $this->_errors->$name ) )
        {
            $div->addChildNode( $this->_errors->$name );
        }
    }
    
    protected function _checkFormValues()
    {
        $this->_checkRequired( 'author' );
        $this->_checkRequired( 'email' );
        $this->_checkRequired( 'comment' );
        $this->_checkRequired( 'captcha' );
        $this->_checkEmail( 'email' );
        $this->_checkCaptcha( 'captcha' );
        
        $valid = true;
        
        foreach( $this->_errors as $field => $errorText )
        {
            $error            = new XS_Xhtml_Tag( 'div' );
            $error[ 'class' ] = 'comment-field-error';
            
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
    
    protected function _checkCaptcha( $name )
    {
        if( !isset( $this->_data[ $name ] ) )
        {
            return;
        }
        
        if( strtolower( $this->_data[ $name ] ) != $this->_session->XS_Captcha )
        {
            $this->_errors->$name = $this->_lang->errorInvalidCaptcha;
        }
    }
    
    protected function _checkEmail( $name )
    {
        if( !isset( $this->_data[ $name ] ) )
        {
            return;
        }
        
        if( !filter_var( $this->_data[ $name ], FILTER_VALIDATE_EMAIL ) )
        {
            $this->_errors->$name = $this->_lang->errorInvalidEmail;
        }
    }
    
    public function _createComment()
    {
        $comment = new XS_Database_Object( self::TABLE_COMMENTS );
        
        $comment->author  = $this->_data[ 'author' ];
        $comment->email   = $this->_data[ 'email' ];
        $comment->comment = $this->_data[ 'comment' ];
        $comment->path    = $this->_path;
        
        $comment->commit();
        
        $menu    = XS_Menu::getInstance();
        $path    = substr( $comment->path, strpos( $comment->path, '/', 2 ) );
        $url     = ( isset( $_SERVER[ 'HTTPS' ] ) ) ? 'https://' : 'http://'
                 . $_SERVER[ 'HTTP_HOST' ]
                 . $menu->getCurrentPageUrl();
        $message = 'A new comment has been posted to the page named \''
                 . $menu->getPageTitle( $path )
                 . '\'.'
                 . chr( 10 )
                 . chr( 10 )
                 . 'Author: ' . $comment->author . ' (' . $comment->email . ')'
                 . chr( 10 )
                 . chr( 10 )
                 . 'Click on the link below to view the page:'
                 . chr( 10 )
                 . $url;
        
        $mail = new XS_Mail
        (
            'macmade@xs-labs.com',
            'XS-Labs: new comment',
            $message,
            'macmade@xs-labs.com'
        );
        
        $mail->send();
        
        $comments = XS_Database_Object::getObjectsByFields( self::TABLE_COMMENTS, array( 'path' => $this->_path ) );
        $emails   = array( $comment->email => true );
        
        foreach( $comments as $previousComment )
        {
            if( isset( $emails[ $previousComment->email ] ) )
            {
                continue;
            }
            
            $mail = new XS_Mail
            (
                $previousComment->email,
                'XS-Labs: new comment',
                $message,
                'macmade@xs-labs.com'
            );
            
            $mail->send();
            
            $emails[ $previousComment->email ] = true;
        }
    }
}
