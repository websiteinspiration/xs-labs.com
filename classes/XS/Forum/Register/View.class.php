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

class XS_Forum_Register_View extends XS_Forum_Base
{
    protected $_errors = NULL;
    protected $_data   = array();
    
    public function __construct()
    {
        parent::__construct();
        
        $this->_errors = new stdClass();
        $valid         = false;
        
        if( isset( $_POST[ 'forum' ][ 'register' ] ) )
        {
            $this->_data = $_POST[ 'forum' ][ 'register' ];
            $valid       = $this->_checkFormValues();
        }
        
        if( is_object( $this->_user ) )
        {
            header( 'Location: ' . $this->_menu->getCurrentPageUrl() );
            exit();
        }
        
        if( $valid === true )
        {
            $this->_createUser();
            $this->_content->h3  = $this->_lang->titleRegisterSuccess;
            $this->_content->div = $this->_lang->titleRegisterSuccessText;
        }
        else
        {
            $this->_content->h3 = $this->_lang->titleRegister;
            $form               = $this->_content->form;
            $form[ 'action' ]   = $this->_menu->getCurrentPageUrl( array( 'forum[register]' => 1 ) );
            $form[ 'name' ]     = 'forum-register';
            $form[ 'id' ]       = 'forum-register';
            $form[ 'method' ]   = 'post';
            $form[ 'class' ]    = 'forum-register';
            
            $this->_createFields( $form );
        }
    }
    
    protected function _createUser()
    {
        $user    = new XS_Database_Object( self::TABLE_USERS );
        $options = new XS_Database_Object( self::TABLE_USER_OPTIONS );
        
        $user->firstname = $this->_data[ 'firstname' ];
        $user->lastname  = $this->_data[ 'lastname' ];
        $user->username  = $this->_data[ 'username' ];
        $user->password  = sha1( $this->_data[ 'password' ] );
        $user->email     = $this->_data[ 'email' ];
        $user->company   = ( isset( $this->_data[ 'company' ] ) ) ? $this->_data[ 'company' ] : '';
        $user->www       = ( isset( $this->_data[ 'www' ] ) )     ? $this->_data[ 'www' ]     : '';
        
        $user->commit();
        
        $options->show_fullname = ( int )( isset( $this->_data[ 'show_full_name' ] ) && $this->_data[ 'show_full_name' ] );
        $options->id_user       = $user->getId();
        
        $options->commit();
        
        $message = 'A new forum user has registered:'
                 . chr( 10 )
                 . chr( 10 )
                 . 'Usernname:  ' . $user->username  . chr( 10 )
                 . 'E-mail:     ' . $user->email     . chr( 10 )
                 . 'First name: ' . $user->firstname . chr( 10 )
                 . 'Last name:  ' . $user->lastname  . chr( 10 )
                 . 'Company:    ' . $user->company   . chr( 10 )
                 . 'WWW:        ' . $user->www       . chr( 10 );
        
        $mail = new XS_Mail
        (
            'macmade@xs-labs.com',
            'XS-Labs: new forum user',
            $message,
            'macmade@xs-labs.com'
        );
        
        $mail->send();
    }
    
    protected function _checkFormValues()
    {
        $this->_checkRequired( 'firstname' );
        $this->_checkRequired( 'lastname' );
        $this->_checkRequired( 'username' );
        $this->_checkRequired( 'password' );
        $this->_checkRequired( 'email' );
        $this->_checkEmail(    'email' );
        
        if( $this->_checkUniqueInDatabase( 'username', self::TABLE_USERS, 'username' ) === false )
        {
            $this->_errors->username = $this->_lang->errorUsernameNotUnique;
        }
        
        if( $this->_checkUniqueInDatabase( 'email', self::TABLE_USERS, 'email' ) === false )
        {
            $this->_errors->email = $this->_lang->errorEmailNotUnique;
        }
        
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
    
    protected function _checkUniqueInDatabase( $name, $table, $column )
    {
        if( !isset( $this->_data[ $name ] ) )
        {
            return false;
        }
        
        $records = $this->_db->getRecordsByFields
        (
            $table,
            array
            (
                'deleted' => 0,
                $name     => $this->_data[ $name ]
            )
        );
        
        return ( count( $records ) === 0 );
    }
    
    protected function _createFields( XS_Xhtml_Tag $form )
    {
        $this->_createTextInput(    'firstname',    $form );
        $this->_createTextInput(    'lastname',     $form );
        $this->_createTextInput(    'username',     $form );
        $this->_createTextPassword( 'password',     $form );
        $this->_createTextInput(    'email',        $form );
        $this->_createTextInput(    'company',      $form );
        $this->_createTextInput(    'www',          $form );
        $this->_createCheckbox(     'show_full_name', $form );
        
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
        $input[ 'name' ]  = 'forum[register][' . $name . ']';
        
        if( isset( $this->_errors->$name ) )
        {
            $div->addChildNode( $this->_errors->$name );
        }
    }
    
    protected function _createTextPassword( $name, XS_Xhtml_Tag $container )
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
        $input[ 'type' ]  = 'password';
        $input[ 'size' ]  = '50';
        $input[ 'value' ] = '';
        $input[ 'name' ]  = 'forum[register][' . $name . ']';
        
        if( isset( $this->_errors->$name ) )
        {
            $div->addChildNode( $this->_errors->$name );
        }
    }
    
    protected function _createCheckbox( $name, XS_Xhtml_Tag $container )
    {
        $div      = $container->div;
        $field    = $div->div;
        $inputDiv = $field->div;
        $input    = $inputDiv->input;
        $label    = $inputDiv->label;
        
        $div[ 'class' ]      = 'forum-field-' . $name;
        $field[ 'class' ]    = 'forum-field';
        $inputDiv[ 'class' ] = 'forum-field-formElement';
        
        $label->addTextData( $this->_lang->$name );
        
        $label[ 'for' ]   = 'forum-field-' . $name;
        $input[ 'id' ]    = 'forum-field-' . $name;
        $input[ 'type' ]  = 'checkbox';
        $input[ 'value' ] = '1';
        $input[ 'name' ]  = 'forum[register][' . $name . ']';
        
        if( isset( $this->_data[ $name ] ) && $this->_data[ $name ] )
        {
            $input[ 'checked' ] = 'checked';
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
        $input[ 'name' ]     = 'forum[register][' . $name . ']';
    }
}
